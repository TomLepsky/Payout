<?php

namespace App\Service\Checkout\Action;

use App\Entity\PaymentInstrument;

abstract class PaymentInstrumentAction extends Action
{
    protected const INSTRUMENT_PATH = '/instruments';

    public function getUri(): string
    {
        return self::INSTRUMENT_PATH;
    }

    public function getMode(): int
    {
        return self::PRIVATE_KEY_MODE;
    }

    public function getModelClass(): string
    {
        return PaymentInstrument::class;
    }
}
