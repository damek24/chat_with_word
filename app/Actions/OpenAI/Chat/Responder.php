<?php

namespace App\Actions\OpenAI\Chat;

class Responder extends MessageStructure
{
    public function __construct(string $user, array $context)
    {
        $context = join("\n", $context);
        $system = <<<MSG
Respond base only from your knowledge below. If question is not connected respond "I don't know"

knowledge:
$context
MSG;

        parent::__construct($user, $system, false);
    }
}
