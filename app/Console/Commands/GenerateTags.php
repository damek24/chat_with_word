<?php

namespace App\Console\Commands;

use App\Models\DocumentSection;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use OpenAI\Laravel\Facades\OpenAI;

class GenerateTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-tags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private array $functions = [
        [
            'name' => 'generate_tags',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'tags' => [
                        'type' => 'array',
                        'description' => 'list of tags',
                        'items' => [
                            'type' => 'string',
                            'description' => 'SEO friendly, semantic tag',
                        ],
                    ],
                ],
            ],

        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $system = <<<MSG
Generate semantic tags for a given document to be used for search and retrieval, always written in Document language. respond in json
MSG;
        /** @var Collection<int, DocumentSection> $sections */
        $sections = DocumentSection::query()->whereNull('tags')->get();

        foreach ($sections as $section) {
            $user = $section->content;
            $systemMessage = $system;
            $systemMessage .= "additional info \nchapter title: " . $section->chapter;
            if (!is_numeric(trim($section->subchapter))) {
                $systemMessage .= "\n subchapter: " . $section->subchapter;
            }
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4-1106-preview',
                'messages' => [
                    ['role' => 'system', 'content' => $systemMessage],
                    ['role' => 'user', 'content' => $user],
                ],
                'functions' => $this->functions,
                'function_call' => 'auto',
                'response_format' => ['type' => 'json_object'],
            ]);
            $tags = $response->choices[0]->message->functionCall->arguments ?? $response->choices[0]->message->content ?? null;
            if (!$tags) {
                continue;
            }
            $tags = json_decode($tags, associative: true);
            $section->tags = $tags;
            $section->save();
            dump($tags);
        }
    }
}
