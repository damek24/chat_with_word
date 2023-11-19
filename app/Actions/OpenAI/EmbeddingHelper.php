<?php

namespace App\Actions\OpenAI;

use OpenAI\Laravel\Facades\OpenAI;

class EmbeddingHelper
{
    public static function execute(string $input): array
    {
        $result = OpenAI::embeddings()->create([
            'model' => 'text-embedding-ada-002',
            'input' => $input,
        ]);
        return $result->embeddings[0]->embedding;
    }
}
