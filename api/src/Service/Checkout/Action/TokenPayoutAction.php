<?php

namespace App\Service\Checkout\Action;

use App\Entity\User;
use App\Model\Token;

class TokenPayoutAction extends PayoutAction
{
    public function __construct(int $amount, string $currency, private User $cardOwner, private Token $token)
    {
        parent::__construct($amount, $currency);
    }

    public function preparePayload() : ?array
    {
        return $this->payload = [
            'destination' => [
                'type' => self::TYPE_TOKEN,
                'token' => $this->token->token,
                'first_name' => $this->cardOwner->getFirstName(),
                'last_name' => $this->cardOwner->getLastName()
            ],
            'amount' => $this->amount,
            'currency' => $this->currency
        ];
    }
}
