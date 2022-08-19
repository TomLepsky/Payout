<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class Token extends Model
{
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['card', 'applepay', 'googlepay'])]
    public string $type;

    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^(tok)_(\w{26})$/')]
    public string $token;

    public string $expiresOn;

    #[Assert\Regex(pattern: '/^[\d]{1,2}$/')]
    public ?int $expiryMonth;

    #[Assert\Regex(pattern: '/^[\d]{4}$/')]
    public ?int $expiryYear;

    #[Assert\Regex(pattern: '/^[\d]{4}$/')]
    public ?string $last4;

    #[Assert\Regex(pattern: '/^[\d]{1,6}$/')]
    public ?string $bin;

    public ?string $scheme;
}
