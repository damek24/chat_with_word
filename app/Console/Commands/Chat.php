<?php

namespace App\Console\Commands;

use App\Actions\OpenAI\Chat\Responder;
use App\Actions\OpenAI\RuleResponder;
use App\Dto\OpenAi\QdrantClient;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

use function Laravel\Prompts\text;

class Chat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:chat';

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
        \Laravel\Prompts\info('I am MFL expert ask me anything!. type "goodbye" to finish this conversationphp ar');

        while (true) {
            $message = text('your question?');
            if ($message === 'goodbye') {
                \Laravel\Prompts\info('goodbye!');
                break;
            }
            \Laravel\Prompts\info(RuleResponder::execute($message));
        }
    }
}
