<?php

namespace App\Actions\OpenAI;

use App\Actions\OpenAI\Chat\Responder;
use App\Dto\OpenAi\QdrantClient;
use App\Enums\GptMode;
use App\Models\DocumentSection;
use App\Models\RuleSection;

class RuleResponder
{
    public static function execute(string $message)
    {
        $client = new QdrantClient('document_rules');
        $result = $client->search($message, 80);
        $ids = [];
        foreach ($result as $row) {
            //dump($row);
            if ($row['payload']['type'] === 'rule') {
                $ids[] = $row['id'];
            } else {
                $ids [] = $row['payload']['rule_id'];
            }
        }

        $ids = array_unique($ids);
        $max_length = 5000;
        $current_length = 0;
        $context = [];
        foreach ($ids as $id) {
            $rule = DocumentSection::query()->where('id', $id)->first();
            $current_length = $current_length + strlen($rule->content);
            if ($current_length > $max_length) {
                break;
            }
            $context [] = $rule->content;
        }
        return (new Responder($message, $context))->sendMessage(GptMode::gpt4_nov_2023)->content;
    }
}
