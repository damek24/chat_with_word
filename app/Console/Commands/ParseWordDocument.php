<?php

namespace App\Console\Commands;

use App\Actions\OpenAI\WordParser\ConvertTableToMarkdown;
use App\Actions\OpenAI\WordParser\GetFirstText;
use App\Actions\OpenAI\WordParser\IsChapterTitle;
use App\Actions\OpenAI\WordParser\IsSubchapterTitle;
use App\Models\DocumentSection;
use Illuminate\Console\Command;
use PhpOffice\PhpWord\Element\AbstractElement;
use PhpOffice\PhpWord\Element\ListItemRun;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextBreak;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\IOFactory;

class ParseWordDocument extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:parse-word-document';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private array $chapters = [];
    private array $current_subchapter = [];

    private ?string $chapter_title = null;

    private ?string $subchapter_title = null;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $source = storage_path('files/rule_book.docx');
        $php_word = IOFactory::load($source);

        foreach ($php_word->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $this->processElement($element);
            }
        }
        foreach ($this->chapters as &$chapter) {
            foreach ($chapter['subchapters'] as &$subchapter) {
                $subchapter['items'] = array_filter($subchapter['items'], fn(string $elem) => trim($elem));
            }
            $chapter['subchapters'] = array_filter($chapter['subchapters'], fn(array $subchapters) => !empty($subchapters['items']));

            foreach ($chapter['subchapters'] as &$subchapter) {
                $subchapter['items'] = implode(' ', $subchapter['items']);
            }
        }

        //RuleSection::query()->delete();
        foreach ($this->chapters as $title => $chapterContent) {
            foreach ($chapterContent['subchapters'] as $subchapter_key => $subchapter) {
                DocumentSection::query()->firstOrCreate([
                    'chapter' => $title,
                    'subchapter' => $subchapter_key,
                    'content' => $subchapter['items']
                ]);
            }
        }
    }

    private function processElement($element)
    {
        if ($element instanceof TextRun) {
            $firstText = GetFirstText::execute($element);
            if (IsChapterTitle::execute($firstText)) {
                $this->addChapter($firstText);
                $this->subchapter_title = null;
            } elseif (IsSubchapterTitle::execute($firstText)) {
                $this->addSubchapter($firstText, $element);
            } else {
                $this->subchapter_title ??= "1";
                $this->chapters[$this->chapter_title]['subchapters'][$this->subchapter_title]['items'] ??= [];
                $items = $this->chapters[$this->chapter_title]['subchapters'][$this->subchapter_title]['items'];
                foreach ($element->getElements() as $textElement) {
                    if ($textElement instanceof Text) {
                        $items[] = $textElement->getText();
                    }
                }
                $this->chapters[$this->chapter_title]['subchapters'][$this->subchapter_title]['items'] = $items;
            }
        } elseif ($element instanceof ListItemRun) {
            $items = $this->chapters[$this->chapter_title]['subchapters'][$this->subchapter_title]['items'];
            foreach ($element->getElements() as $listElement) {
                if ($listElement instanceof Text) {
                    $items[] = $listElement->getText();
                }
            }
            $this->chapters[$this->chapter_title]['subchapters'][$this->subchapter_title]['items'] = $items;
        } elseif ($element instanceof Table) {
            $this->chapters[$this->chapter_title]['subchapters'][$this->subchapter_title]['items'] = [
                ...$this->chapters[$this->chapter_title]['subchapters'][$this->subchapter_title]['items'],
                ConvertTableToMarkdown::execute($element)
            ];
        }
    }

    private function addChapter($title)
    {
        if (!empty($this->current_subchapter)) {
            $this->chapters[] = ['title' => end($this->chapters)['title'], 'subchapters' => $this->current_subchapter];
            $this->current_subchapter = [];
        }
        $this->chapter_title = $title;
        $this->chapters[$title] = ['title' => $title, 'subchapters' => []];
    }

    private function addSubchapter($title, $element)
    {
        $this->subchapter_title = $title;
        $this->chapters[$this->chapter_title]['subchapters'][$title] = ['title' => $title, 'items' => []];
        $items = $this->chapters[$this->chapter_title]['subchapters'][$title]['items'];
        $elements = $element->getElements();
        $elements = array_filter($elements, fn(AbstractElement $e) => !$e instanceof  TextBreak);
        $elements = array_filter($elements, fn(Text $e) => $e->getText() !== $title);
        foreach ($elements as $element) {
            $items [] = $element->getText();
        }
        $this->chapters[$this->chapter_title]['subchapters'][$title]['items'] = $items;
        // dump(count($element->getElements());
    }
}
