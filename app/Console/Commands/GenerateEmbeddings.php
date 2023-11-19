<?php

namespace App\Console\Commands;

use App\Dto\OpenAi\QdrantClient;
use App\Models\DocumentSection;
use App\Models\RuleSection;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class GenerateEmbeddings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-embeddings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $qdrantClient = new QdrantClient('document_rules');
        /** @var Collection<int, DocumentSection> $sections */
        $sections = DocumentSection::query()->get();
        $progress = $this->output->createProgressBar($sections->count());
        foreach ($sections as $section) {
            $qdrantClient->upsert($section->content, $section->id, ['type' => 'rule']);
            $tags = implode(',', $section->tags['tags']);
            $qdrantClient->upsert($tags, payload: ['type' => 'tags', 'rule_id' => $section->id]);
            $progress->advance();
        }
    }
}
