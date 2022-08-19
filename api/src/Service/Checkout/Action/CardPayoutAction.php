<?php

namespace App\Service\Checkout\Action;

use App\Model\Card;
use App\Entity\User;

class CardPayoutAction extends PayoutAction
{
    public function __construct(int $amount, string $currency, private Card $card, private User $cardOwner)
    {
        parent::__construct($amount, $currency);
    }

    public function preparePayload() : ?array
    {
        return $this->payload = [
            'destination' => [
                'type' => self::TYPE_CARD,
                'number' => $this->card->number,
                'expiry_month' => $this->card->expiryMonth,
                'expiry_year' => $this->card->expiryYear,
                'first_name' => $this->cardOwner->getFirstName(),
                'last_name' => $this->cardOwner->getLastName()
            ],
            'amount' => $this->amount,
            'currency' => $this->currency
        ];
    }
}
