<?php

namespace App\Model;

use App\Config;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

abstract class PaymentInput extends Model implements Solvent
{
    #[Groups(['payment:write'])]
    #[Assert\NotBlank(message: "Это значение не может быть пустым")]
    #[Assert\Type(type: 'string')]
    #[Assert\Regex(pattern: '/^[a-zA-Z]{1,50}$/', message: "Имя должно содержать от 1 до 50 символов")]
    public mixed $firstName;

    #[Groups(['payment:write'])]
    #[Assert\NotBlank(message: "Это значение не может быть пустым")]
    #[Assert\Type(type: 'string')]
    #[Assert\Regex(pattern: '/^[a-zA-Z]{1,50}$/', message: "Фамилия должна содержать от 1 до 50 символов")]
    public mixed $lastName;

    #[Groups(['payment:write'])]
    #[Assert\NotBlank(message: "Это значение не может быть пустым")]
    #[Assert\Type(type: 'float')]
    #[Assert\Positive(message: "Сумма должна быть положительной")]
    public mixed $amount;

    #[Groups(['payment:write'])]
    #[Assert\NotBlank(message: "Это значение не может быть пустым")]
    #[Assert\Type(type: 'string')]
    #[Assert\Choice(choices: Config::AVAILABLE_CURRENCIES, message: "Доступные валюты: {{ choices }}")]
    public mixed $currency;

    #[Groups(['payment:write'])]
    #[Assert\NotBlank(allowNull: true)]
    #[Assert\Type(type: 'string')]
    public mixed $note = null;

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }
}
