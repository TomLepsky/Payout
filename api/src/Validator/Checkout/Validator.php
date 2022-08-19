<?php

namespace App\Validator\Checkout;

use stdClass;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class Validator
{
    public const TOKEN_PAYOUT_MODE = 1;
    public const CARD_PAYOUT_MODE = 2;
    public const TOKEN_MODE = 3;

    private array $requiredCardPayoutFields = [
        'number',
        'expiryMonth',
        'expiryYear',
        'firstName',
        'lastName',
        'amount',
        'currency'
    ];

    private array $requiredTokenPayoutFields = [
        'firstName',
        'lastName',
        'amount',
        'currency',
        'token'
    ];

    private array $requiredTokenFields = [
        'type',
        'number',
        'expiryMonth',
        'expiryYear'
    ];

    public function validate(StdClass $object, int $mode) : bool
    {
        $requiredFields = match ($mode) {
            self::TOKEN_PAYOUT_MODE => $this->requiredTokenPayoutFields,
            self::CARD_PAYOUT_MODE => $this->requiredCardPayoutFields,
            self::TOKEN_MODE => $this->requiredTokenFields,
            default => []
        };

        $fields = array_keys(get_object_vars($object));
        $missingParameters = [];
        foreach ($requiredFields as $field) {
            if (!in_array($field, $fields)) {
                $missingParameters[] = $field;
            }
        }

        if (!empty($missingParameters)) {
            throw new BadRequestException("Missing parameter(s): " . implode(', ', $missingParameters));
        }

        return true;
    }
}
