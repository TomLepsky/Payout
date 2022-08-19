<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Config;
use App\Repository\NoteRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NoteRepository::class)]
#[ORM\Table(name: 'note')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    collectionOperations: [
        'get_collection' => [
            'path' => '/notes',
            'method' => 'get',
            'security' => "is_granted('" . Config::ACCOUNTANT . "')",
            'normalization_context' => [
                'groups' => ['noteCollection:read'],
                'skip_null_values' => true
            ]
        ],
        'post' => [
            'path' => '/notes',
            'method' => 'post',
            'security_post_denormalize' => "is_granted('" . Config::CHIEF_ACCOUNTANT . "')",
            'denormalization_context' => [
                'groups' => ['note:write']
            ]
        ]
    ],
    itemOperations: [
        'get_item' => [
            'path' => '/notes/{id}',
            'method' => 'get',
            'security' => "is_granted('" . Config::ACCOUNTANT . "')",
            'normalization_context' => [
                'groups' => ['noteItem:read'],
                'skip_null_values' => true
            ],
        ],
        'put_item' => [
            'path' => '/notes/{id}',
            'method' => 'put',
            'security_post_denormalize' => "is_granted('" . Config::CHIEF_ACCOUNTANT . "')",
            'denormalization_context' => [
                'groups' => ['note:edit']
            ]
        ]
    ],
    attributes: [
        'pagination_client_items_per_page' => true,
    ],
    normalizationContext: [
        'enable_max_depth' => true
    ]
)]
#[ApiFilter(SearchFilter::class, properties: ['payment.id'])]
class Note
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['noteItem:read', 'noteCollection:read', 'paymentItem:read', 'paymentCollection:read'])]
    private int $id;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(allowNull: false)]
    #[Groups(['noteItem:read', 'noteCollection:read', 'note:write', 'note:edit', 'paymentItem:read', 'paymentCollection:read'])]
    private string $content;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['noteItem:read', 'noteCollection:read', 'paymentItem:read', 'paymentCollection:read'])]
    private UserInterface $owner;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['noteItem:read', 'noteCollection:read', 'paymentItem:read', 'paymentCollection:read'])]
    private UserInterface $lastEditor;

    #[ORM\ManyToOne(targetEntity: Payment::class, inversedBy: 'notes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(allowNull: false)]
    #[Groups(['noteItem:read', 'noteCollection:read', 'note:write'])]
    #[MaxDepth(maxDepth: 1)]
    private Payment $payment;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['noteItem:read', 'noteCollection:read', 'paymentItem:read', 'paymentCollection:read'])]
    private ?DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['noteItem:read', 'noteCollection:read', 'paymentItem:read', 'paymentCollection:read'])]
    private ?DateTimeInterface $updatedAt;

    #[ORM\PrePersist]
    public function prePersist() : void
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    #[ORM\PreUpdate]
    public function preUpdate() : void
    {
        $this->updatedAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getOwner(): ?UserInterface
    {
        return $this->owner;
    }

    public function setOwner(?UserInterface $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getLastEditor(): ?UserInterface
    {
        return $this->lastEditor;
    }

    public function setLastEditor(?UserInterface $lastEditor): self
    {
        $this->lastEditor = $lastEditor;

        return $this;
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function setPayment(?Payment $payment): self
    {
        $this->payment = $payment;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
