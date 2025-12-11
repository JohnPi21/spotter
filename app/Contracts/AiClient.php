<?php

namespace App\Contracts;

use Prism\Prism\Contracts\Schema;

interface AiClient
{
    public function text(string $prompt, string $systemPrompt): string;

    public function structured(string $prompt, string $systemPrompt, Schema $schema): array;

    public function chat();
}
