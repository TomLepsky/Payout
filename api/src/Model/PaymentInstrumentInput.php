<?php

namespace App\Model;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class PaymentInstrumentInput
{
    #[Groups(['instrument:write'])]
    #[Assert\NotBlank]
    #[Assert\Type(type: 'string')]
    #[Assert\Choice(choices: ['card'])]
    public mixed $type = 'card';

    #[Groups(['instrument:write'])]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank(allowNull: true)]
    public mixed $title = null;

    #[Groups(['instrument:write'])]
    #[Assert\NotBlank]
    #[Assert\Type(type: 'string')]
    #[Assert\Regex(pattern: '/^[a-zA-Z]{1,50}$/', message: "Имя должно содержать от 1 до 50 символов")]
    public mixed $firstName;

    #[Groups(['instrument:write'])]
    #[Assert\NotBlank]
    #[Assert\Type(type: 'string')]
    #[Assert\Regex(pattern: '/^[a-zA-Z]{2,21}$/', message: "Фамилия должна содержать от 1 до 50 символов")]
    public mixed $lastName;

    #[Groups(['instrument:write'])]
    #[Assert\NotBlank]
    #[Assert\Type(type: 'integer')]
    #[Assert\Regex(pattern: '/^[\d]{13}$|^[\d]{16}$|^[\d]{18,19}$/', message: "Номер карты должен содержать 13, 16, 18 или 19 цифр")]
    public mixed $number;

    #[Groups(['instrument:write'])]
    #[Assert\NotBlank]
    #[Assert\Type(type: 'integer')]
    #[Assert\Range(notInRangeMessage: "Номер месяца должен быть от 1 до 12", min: 1, max: 12)]
    public mixed $expiryMonth;

    #[Groups(['instrument:write'])]
    #[Assert\NotBlank]
    #[Assert\Type(type: 'integer')]
    #[Assert\Regex(pattern: '/^[\d]{2,4}$/', message: "Введите две последние цифры года")]
    public mixed $expiryYear;

    #[Groups(['instrument:write'])]
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank(allowNull: true)]
    public mixed $note = null;
}
