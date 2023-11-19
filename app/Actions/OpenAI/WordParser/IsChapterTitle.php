<?php

namespace App\Actions\OpenAI\WordParser;

class IsChapterTitle
{
    public static function execute(string $text): bool
    {
        return preg_match('/^\d+ /', $text) && !str_contains($text, '-');
    }
}
