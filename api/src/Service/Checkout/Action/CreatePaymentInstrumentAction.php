<?php

namespace App\Service\Checkout\Action;

use App\Model\Token;

class CreatePaymentInstrumentAction extends PaymentInstrumentAction
{
    public function __construct(private Token $token) {}

    public function preparePayload(): ?array
    {
        return $this->payload = [
            'type' => 'token',
            'token' => $this->token->token
        ];
    }

    public function getMethod(): string
    {
        return self::METHOD_POST;
    }
}
