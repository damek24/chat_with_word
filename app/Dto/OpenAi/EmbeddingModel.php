<?php

namespace App\Dto\OpenAi;

use App\Actions\OpenAI\EmbeddingHelper;
use Illuminate\Support\Str;
use Qdrant\Models\VectorStruct;
use Spatie\LaravelData\Data;

class EmbeddingModel extends Data
{
    public readonly string $id;

    public function __construct(public readonly array $vector, ?string $id = null)
    {
        $this->id = $id ?? Str::uuid()->toString();
    }

    public static function fromInput(string $input, ?string $uuid = null): EmbeddingModel
    {
        $vector = EmbeddingHelper::execute($input);
        return new EmbeddingModel($vector, $uuid);
    }

    public function toVectorStruct(): VectorStruct
    {
        return new VectorStruct($this->vector);
    }
}
