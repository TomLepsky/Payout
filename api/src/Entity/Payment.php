<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Controller\Checkout\CreatePaymentViaCard;
use App\Controller\Checkout\CreatePaymentViaInstrument;
use App\Config;
use App\Filter\PaymentSearchFilter;
use App\Filter\SubsidiaryAmountFilter;
use App\Model\Solvent;
use App\OpenApi\Context\PaymentOpenApiContext;
use App\Repository\PaymentRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[ORM\Table(name: 'payment')]
#[ORM\Index(fields: ['paymentId'], name: 'payment_id_index')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    collectionOperations: [
        'get_collection' => [
            'path' => '/payments',
            'method' => 'get',
            'security' => "is_granted('" . Config::ACCOUNTANT . "')",
            'normalization_context' => [
                'groups' => ['paymentCollection:read'],
                'skip_null_values' => true,
                'enable_max_depth' => true
            ],
            'order' => ['createdAt' => 'DESC'],
        ],
        'post_payment_via_card' => [
            'path' => '/card-payout',
            'method' => 'post',
            'security_post_denormalize' => "is_granted('" . Config::CHIEF_ACCOUNTANT . "')",
            'controller' => CreatePaymentViaCard::class,
            'normalization_context' => [
                'groups' => ['paymentItem:read'],
                'skip_null_values' => true,
                'enable_max_depth' => true
            ],
            'denormalization_context' => [
                'groups' => ['payment:write']
            ],
            'input' => false,
            'openapi_context' => PaymentOpenApiContext::POST_PAYMENT_VIA_CARD
        ],
        'post_payment_via_source' => [
            'path' => '/source-payout',
            'method' => 'post',
            'security_post_denormalize' => "is_granted('" . Config::CHIEF_ACCOUNTANT . "')",
            'controller' => CreatePaymentViaInstrument::class,
            'normalization_context' => [
                'groups' => ['paymentItem:read'],
                'skip_null_values' => true,
                'enable_max_depth' => true
            ],
            'denormalization_context' => [
                'groups' => ['payment:write']
            ],
            'input' => false,
            'openapi_context' => PaymentOpenApiContext::POST_PAYMENT_VIA_SOURCE
        ]
    ],
    itemOperations: [
        'get_item' => [
            'path' => '/payments/{id}',
            'method' => 'get',
            'security' => "is_granted('" . Config::ACCOUNTANT . "')",
            'normalization_context' => [
                'groups' => ['paymentItem:read'],
                'skip_null_values' => true,
                'enable_max_depth' => true
            ],
        ]
    ],
    attributes: [
        'pagination_client_items_per_page' => true,
    ],
    denormalizationContext: [
        'groups' => ['payment:write']
    ],
    normalizationContext: [
        'enable_max_depth' => true
    ]
)]
#[ApiFilter(SearchFilter::class, properties: ['status'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt'])]
#[ApiFilter(SubsidiaryAmountFilter::class, properties: ['amount'])]
#[ApiFilter(OrderFilter::class, properties: ['amount', 'currency', 'status', 'createdAt', 'instrument.lastName'])]
#[ApiFilter(PaymentSearchFilter::class, properties: ['instrument.firstName', 'instrument.lastName', 'instrument.last4'])]
class Payment implements Solvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['paymentItem:read', 'paymentCollection:read', 'instrumentItem:read'])]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['paymentItem:read', 'paymentCollection:read', 'instrumentItem:read'])]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^(pay)_(\w{26})$/')]
    private string $paymentId;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(['paymentItem:read', 'paymentCollection:read', 'instrumentItem:read'])]
    #[Assert\NotBlank]
    private ?float $amount;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['paymentItem:read', 'paymentCollection:read', 'instrumentItem:read'])]
    #[Assert\NotBlank(allowNull: true)]
    private ?string $currency;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['paymentItem:read', 'paymentCollection:read', 'instrumentItem:read'])]
    #[Assert\NotBlank(allowNull: true)]
    #[Assert\Choice(choices: ['Authorized', 'Pending', 'Card Verified', 'Captured', 'Declined', 'Paid'])]
    private ?string $status;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['paymentItem:read', 'paymentCollection:read', 'instrumentItem:read'])]
    private ?DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['paymentItem:read', 'paymentCollection:read'])]
    private ?DateTimeInterface $updatedAt;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['paymentItem:read', 'paymentCollection:read'])]
    private ?string $code;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['paymentItem:read', 'paymentCollection:read'])]
    private ?string $summary;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'payments')]
    #[Groups(['paymentItem:read', 'paymentCollection:read'])]
    #[MaxDepth(maxDepth: 1)]
    private ?UserInterface $owner = null;

    #[ORM\OneToMany(mappedBy: 'payment', targetEntity: Note::class, cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['paymentItem:read', 'paymentCollection:read'])]
    #[MaxDepth(maxDepth: 1)]
    private ?Collection $notes;

    #[ORM\ManyToOne(targetEntity: PaymentInstrument::class, fetch: 'EAGER', inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['paymentItem:read', 'paymentCollection:read'])]
    #[MaxDepth(maxDepth: 1)]
    private ?PaymentInstrument $instrument = null;

    public function __construct()
    {
        $this->notes = new ArrayCollection();
    }

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

    public function getPaymentId(): ?string
    {
        return $this->paymentId;
    }

    public function setPaymentId(string $paymentId): self
    {
        $this->paymentId = $paymentId;

        return $this;
    }

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

    public function setCurrency(?string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): self
    {
        $this->summary = $summary;

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

    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Note $note): self
    {
        if (!$this->notes->contains($note)) {
            $this->notes[] = $note;
            $note->setPayment($this);
        }

        return $this;
    }

    public function removeNote(Note $note): self
    {
        if ($this->notes->removeElement($note)) {
            if ($note->getPayment() === $this) {
                $note->setPayment(null);
            }
        }

        return $this;
    }

    public function getInstrument(): ?PaymentInstrument
    {
        return $this->instrument;
    }

    public function setInstrument(?PaymentInstrument $instrument): self
    {
        $this->instrument = $instrument;

        return $this;
    }
}
