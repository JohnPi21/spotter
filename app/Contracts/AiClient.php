<?php

namespace App\Contracts;

use App\Data\Ai\AiCallContextData;
use Prism\Prism\Contracts\Schema;

interface AiClient
{
    public function text(AiCallContextData $context): string;

    public function structured(AiCallContextData $context): array;

    public function chat();
}
