<?php

namespace App\Service\Checkout\Action;

use App\Model\Card;
use App\Model\Token;

class CreateCardTokenAction extends Action
{
    public const TOKEN_PATH = '/tokens';

    public function __construct(private Card $card) {}

    public function preparePayload(): array
    {
        return $this->payload = [
            'type' => self::TYPE_CARD,
            'number' => $this->card->number,
            'expiry_month' => $this->card->expiryMonth,
            'expiry_year' => $this->card->expiryYear
        ];
    }

    public function getMethod(): string
    {
        return self::METHOD_POST;
    }

    public function getUri(): string
    {
        return self::TOKEN_PATH;
    }

    public function getMode(): int
    {
        return self::PUBLIC_KEY_MODE;
    }

    public function getModelClass(): string
    {
        return Token::class;
    }
}
