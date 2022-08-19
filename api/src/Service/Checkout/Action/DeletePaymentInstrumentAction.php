<?php

namespace App\Service\Checkout\Action;

class DeletePaymentInstrumentAction extends PaymentInstrumentAction
{
    public function __construct(private string $instrumentId) {}

    public function preparePayload(): ?array
    {
        return null;
    }

    public function getUri(): string
    {
        return self::INSTRUMENT_PATH . "/$this->instrumentId";
    }

    public function getMethod(): string
    {
        return self::METHOD_DELETE;
    }
}
