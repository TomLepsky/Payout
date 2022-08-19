<?php

namespace App\Service\Checkout\Action;

abstract class Action
{
    public const METHOD_POST = 'POST';
    public const METHOD_GET = 'GET';
    public const METHOD_DELETE = 'DELETE';

    public const PUBLIC_KEY_MODE = 1;
    public const PRIVATE_KEY_MODE = 2;

    public const TYPE_CARD = 'card';
    public const TYPE_TOKEN = 'token';
    public const TYPE_SOURCE = 'id';

    public array $payload;

    abstract public function preparePayload() : ?array;
    abstract public function getMethod() : string;
    abstract public function getUri() : string;
    abstract public function getMode() : int;
    abstract public function getModelClass() : string;
}
