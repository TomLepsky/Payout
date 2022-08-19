<?php

namespace App\Model;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class SourcePaymentInput extends PaymentInput
{
    #[Groups(['payment:write'])]
    #[Assert\NotBlank]
    #[Assert\Type(type: 'string')]
    #[Assert\Regex(pattern: '/^(src)_(\w{26})$/', message: "Неверный идентификатор")]
    public mixed $source;
}
