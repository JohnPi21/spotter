<?php

namespace App\Contracts;

interface AiClient
{
    // make all methods take params
    public function text();

    public function structured();

    public function chat();
}
