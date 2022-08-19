<?php

namespace App\Service\Checkout\Action;

use App\Entity\PaymentInstrument;
use App\Entity\User;

class SourcePayoutAction extends PayoutAction
{
    public function __construct(int $amount, string $currency, private PaymentInstrument $paymentInstrument)
    {
        parent::__construct($amount, $currency);
    }

    public function preparePayload(): ?array
    {
        return $this->payload = [
            'destination' => [
                'type' => self::TYPE_SOURCE,
                'id' => $this->paymentInstrument->getSource(),
                'first_name' => $this->paymentInstrument->getFirstName(),
                'last_name' => $this->paymentInstrument->getLastName()
            ],
            'amount' => $this->amount,
            'currency' => $this->currency
        ];
    }
}
