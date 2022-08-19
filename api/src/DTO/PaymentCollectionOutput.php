<?php

namespace App\DTO;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

class PaymentCollectionOutput
{
    #[Groups(['paymentItem:read', 'paymentCollection:read'])]
    public ?int $id;

    #[Groups(['paymentItem:read', 'paymentCollection:read'])]
    public ?string $paymentId;

    #[Groups(['paymentItem:read', 'paymentCollection:read'])]
    public ?float $amount;

    #[Groups(['paymentItem:read', 'paymentCollection:read'])]
    public ?string $currency;

    #[Groups(['paymentItem:read', 'paymentCollection:read'])]
    public ?string $status = null;

    #[Groups(['paymentItem:read', 'paymentCollection:read'])]
    public ?DateTimeInterface $createdAt = null;

    #[Groups(['paymentItem:read', 'paymentCollection:read'])]
    public ?DateTimeInterface $updatedAt = null;

    #[Groups(['paymentItem:read', 'paymentCollection:read'])]
    public ?string $code = null;

    #[Groups(['paymentItem:read', 'paymentCollection:read'])]
    public ?string $summary = null;

    #[Groups(['paymentItem:read', 'paymentCollection:read'])]
    public ?string $firstName = null;

    #[Groups(['paymentItem:read', 'paymentCollection:read'])]
    public ?string $lastName = null;

    #[Groups(['paymentItem:read', 'paymentCollection:read'])]
    public ?string $bin = null;

    #[Groups(['paymentItem:read', 'paymentCollection:read'])]
    public ?string $last4 = null;

    #[Groups(['paymentItem:read', 'paymentCollection:read'])]
    public ?Collection $notes = null;

    #[Groups(['paymentItem:read', 'paymentCollection:read'])]
    public ?UserInterface $owner = null;
}
