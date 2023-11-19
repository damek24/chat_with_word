<?php

namespace App\Actions\OpenAI\WordParser;

use Illuminate\Support\Arr;
use PhpOffice\PhpWord\Element\AbstractElement;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;

class GetFirstText
{
    public static function execute(TextRun $textRun): string
    {
        $elements = array_filter($textRun->getElements(), fn(AbstractElement $element) => $element instanceof Text);
        return Arr::first($elements)?->getText() ?? '';
    }
}
