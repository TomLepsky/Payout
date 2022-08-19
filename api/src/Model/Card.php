<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class Card extends Model
{
    #[Assert\NotBlank(message: "Это значение не может быть пустым")]
    public string $number;

    public int $expiryMonth;

    public int $expiryYear;
}
