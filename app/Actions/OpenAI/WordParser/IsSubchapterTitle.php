<?php

namespace App\Actions\OpenAI\WordParser;

class IsSubchapterTitle
{
    public static function execute(string $text): bool
    {
        return preg_match('/^\d+\.\d\s*/', $text);
    }
}
