<?php

namespace App\Dto\OpenAi;

use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Qdrant\Config;
use Qdrant\Http\GuzzleClient;
use Qdrant\Models\PointsStruct;
use Qdrant\Models\PointStruct;
use Qdrant\Models\Request\CreateCollection;
use Qdrant\Models\Request\SearchRequest;
use Qdrant\Models\Request\VectorParams;
use Qdrant\Models\VectorStruct;
use Qdrant\Qdrant;

class QdrantClient
{
    private readonly Qdrant $client;

    public function __construct(private readonly string $collection_name, private readonly string $vector_model = 'openai', bool $create = false)
    {
        $config = new Config('localhost');
        $this->client = new Qdrant(new GuzzleClient($config));

        if(!$this->collectionExist($this->collection_name)) {
            $createCollection  = new CreateCollection();
            $createCollection->addVector(new VectorParams(1536, VectorParams::DISTANCE_COSINE), $this->vector_model);
            $this->client->collections($this->collection_name)->create($createCollection);
        }
    }

    public function upsert(EmbeddingModel|string $point, ?string $uuid = null, ?array $payload = null): static
    {
        if (is_string($point)) {
            $point = EmbeddingModel::fromInput($point, $uuid ?? Str::uuid()->toString());
        }
        $points = new PointsStruct();
        $struct = new PointStruct($point->id, new VectorStruct($point->vector, $this->vector_model), $payload);
        $points->addPoint($struct);
        $this->client->collections($this->collection_name)->points()->upsert($points);
        return $this;
    }

    public function search(string $input, int $limit = 10)
    {
        $model = EmbeddingModel::fromInput($input);
        $vector = new VectorStruct($model->vector, $this->vector_model);
        $request = new SearchRequest($vector);
        $request->setLimit($limit)->setWithPayload(true);
        $request->setParams([
            'hnsw_ef' => 128,
            'exact' => false,
        ]);
        $response = $this->client->collections($this->collection_name)->points()->search($request);
        return $response['result'];
    }

    public function cleanup()
    {
        $this->client->collections($this->collection_name)->delete();
    }

    private function collectionExist(string $name)
    {
        $collections = $this->client->collections()->list()['result']['collections'];
        $collections = collect($collections)->map(fn(array $item) => $item['name']);
        return in_array($name, $collections->all());
    }
}
