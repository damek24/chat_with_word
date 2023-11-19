<?php

namespace App\Actions\OpenAI\WordParser;

use PhpOffice\PhpWord\Element\Cell;
use PhpOffice\PhpWord\Element\Row;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;

class ConvertTableToMarkdown
{
    public static function execute(Table $table): string
    {
        $markdownTable = "";
        foreach ($table->getRows() as $rowIndex => $row) {
            $rowMarkdown = self::processRow($row);
            $divider = "|" . str_repeat("---|", count($row->getCells()));

            if ($rowIndex === 0) {
                // After the first row, add the Markdown divider for the header
                $markdownTable .= $rowMarkdown . "\n" . $divider . "\n";
            } else {
                $markdownTable .= $rowMarkdown . "\n";
            }
        }
        return $markdownTable;
    }

    private static function processRow(Row $row): string
    {
        $rowMarkdown = "|";
        foreach ($row->getCells() as $cell) {
            $rowMarkdown .= self::processCell($cell) . "|";
        }
        return $rowMarkdown;
    }

    private static function processCell(Cell $cell): string
    {
        $cellText = "";
        foreach ($cell->getElements() as $cellElement) {
            if ($cellElement instanceof Text) {
                $cellText .= $cellElement->getText();
            }
            if ($cellElement instanceof TextRun) {
                $items = [];
                foreach ($cellElement->getElements() as $textElement) {
                    if ($textElement instanceof Text) {
                        $items[] = $textElement->getText();
                    }
                }
                $cellText .= implode(', ', $items);
            }
        }
        return " $cellText ";
    }
}
