<?php

namespace App\Service\Checkout\Action;

use App\Entity\Payment;

abstract class PayoutAction extends Action
{
    protected const PAYOUT_PATH = '/payments';

    public function __construct(protected int $amount, protected string $currency) {}

    public function getMethod() : string
    {
        return self::METHOD_POST;
    }

    public function getUri() : string
    {
        return self::PAYOUT_PATH;
    }

    public function getMode() : int
    {
        return self::PRIVATE_KEY_MODE;
    }

    public function getModelClass(): string
    {
        return Payment::class;
    }
}
