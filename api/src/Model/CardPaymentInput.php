<?php

namespace App\Model;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class CardPaymentInput extends PaymentInput
{
    #[Groups(['payment:write'])]
    #[Assert\NotBlank(message: "Это значение не может быть пустым")]
    #[Assert\Type(type: 'integer')]
    #[Assert\Regex(pattern: '/^[\d]{13}$|^[\d]{16}$|^[\d]{18,19}$/', message: "Номер карты должен содержать 13, 16, 18 или 19 цифр")]
    public mixed $number;

    #[Groups(['payment:write'])]
    #[Assert\NotBlank(message: "Это значение не может быть пустым")]
    #[Assert\Type(type: 'integer')]
    #[Assert\Range(notInRangeMessage: "Номер месяца должен быть от 1 до 12", min: 1, max: 12)]
    public mixed $expiryMonth;

    #[Groups(['payment:write'])]
    #[Assert\NotBlank(message: "Это значение не может быть пустым")]
    #[Assert\Type(type: 'integer')]
    #[Assert\Regex(pattern: '/^[\d]{2,4}$/', message: "Введите две последние цифры года")]
    public mixed $expiryYear;
}
