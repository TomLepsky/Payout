<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\RangeFilter;
use App\Config;
use App\Controller\Checkout\CreatePaymentInstrument;
use App\Filter\FulltextFilter;
use App\OpenApi\Context\PaymentInstrumentOpenApiContext;
use App\Repository\PaymentInstrumentRepository;
use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PaymentInstrumentRepository::class)]
#[ORM\Table(name: 'payment_instrument')]
#[ORM\Index(fields: ['fingerprint'], name: 'fingerprint_index')]
#[ORM\Index(fields: ['source'], name: 'source_index')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    collectionOperations: [
        'get_collection' => [
            'path' => '/instruments',
            'method' => 'get',
            'security' => "is_granted('" . Config::CHIEF_ACCOUNTANT . "')",
            'normalization_context' => [
                'groups' => ['instrumentCollection:read'],
                'skip_null_values' => true,
                'enable_max_depth' => true
            ],
            'order' => ['createdAt' => 'DESC']
        ],
        'post' => [
            'path' => '/instruments',
            'method' => 'post',
            'security_post_denormalize' => "is_granted('" . Config::CHIEF_ACCOUNTANT . "')",
            'controller' => CreatePaymentInstrument::class,
            'normalization_context' => [
                'groups' => ['instrumentItem:read'],
                'skip_null_values' => true,
                'enable_max_depth' => true
            ],
            'denormalization_context' => [
                'groups' => ['instrument:write']
            ],
            'input' => false,
            'openapi_context' => PaymentInstrumentOpenApiContext::POST_INSTRUMENT,
        ]
    ],
    itemOperations: [
        'get_item' => [
            'path' => '/instruments/{id}',
            'method' => 'get',
            'security' => "is_granted('" . Config::CHIEF_ACCOUNTANT . "')",
            'normalization_context' => [
                'groups' => ['instrumentItem:read'],
                'skip_null_values' => true,
                'enable_max_depth' => true
            ]
        ],
        'put_item' => [
            'path' => '/instruments/{id}',
            'method' => 'put',
            'security_post_denormalize' => "is_granted('" . Config::CHIEF_ACCOUNTANT . "')",
            'normalization_context' => [
                'groups' => ['instrument:edit'],
                'skip_null_values' => true,
                'enable_max_depth' => true
            ],
            'denormalization_context' => [
                'groups' => ['instrument:edit']
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
#[ApiFilter(RangeFilter::class, properties: ['expiryMonth', 'expiryYear'])]
#[ApiFilter(BooleanFilter::class, properties: ['hide'])]
#[ApiFilter(FulltextFilter::class, properties: ['firstName', 'lastName', 'last4'])]
class PaymentInstrument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['instrumentItem:read', 'instrumentCollection:read', 'paymentItem:read', 'paymentCollection:read', 'instrument:edit'])]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\Regex(pattern: '/^(src)_(\w{26})$/')]
    #[Groups(['instrumentItem:read', 'instrumentCollection:read', 'paymentItem:read', 'paymentCollection:read'])]
    private string $source;

    #[ORM\Column(type: 'string', length: 255)]
    private string $fingerprint;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['card'])]
    #[Groups(['instrumentItem:read', 'instrumentCollection:read', 'instrument:write'])]
    private string $type;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['instrumentItem:read', 'instrumentCollection:read', 'instrument:write', 'instrument:edit'])]
    #[Assert\NotBlank(allowNull: true)]
    private ?string $title;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['instrumentItem:read', 'instrumentCollection:read', 'instrument:write', 'paymentItem:read', 'paymentCollection:read'])]
    #[Assert\NotBlank]
    private ?string $firstName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['instrumentItem:read', 'instrumentCollection:read', 'instrument:write', 'paymentItem:read', 'paymentCollection:read'])]
    #[Assert\NotBlank]
    private ?string $lastName;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['instrumentItem:read', 'instrumentCollection:read', 'instrument:write', 'paymentItem:read', 'paymentCollection:read'])]
    #[Assert\NotBlank]
    private string $bin;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['instrumentItem:read', 'instrumentCollection:read', 'instrument:write', 'paymentItem:read', 'paymentCollection:read'])]
    #[Assert\NotBlank]
    private string $last4;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^[\d]{1,2}$/')]
    private ?int $expiryMonth;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^[\d]{2,4}$/')]
    private ?int $expiryYear;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['instrumentItem:read', 'instrumentCollection:read', 'instrument:write', 'instrument:edit'])]
    #[Assert\NotBlank(allowNull: true)]
    private ?string $note = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Groups(['instrumentItem:read'])]
    #[MaxDepth(maxDepth: 1)]
    private ?UserInterface $owner = null;

    #[ORM\OneToMany(mappedBy: 'instrument', targetEntity: Payment::class, cascade: ['persist'], orphanRemoval: true)]
    #[MaxDepth(maxDepth: 1)]
    private Collection $payments;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['instrumentItem:read', 'instrumentCollection:read', 'instrument:write', 'instrument:edit'])]
    private bool $hide = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['instrumentItem:read', 'instrumentCollection:read'])]
    private ?DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->payments = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function prePersist() : void
    {
        $this->createdAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(string $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getFingerprint(): ?string
    {
        return $this->fingerprint;
    }

    public function setFingerprint(string $fingerprint): self
    {
        $this->fingerprint = $fingerprint;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getBin(): ?string
    {
        return $this->bin;
    }

    public function setBin(string $bin): self
    {
        $this->bin = $bin;

        return $this;
    }

    public function getLast4(): ?string
    {
        return $this->last4;
    }

    public function setLast4(string $last4): self
    {
        $this->last4 = $last4;

        return $this;
    }

    public function getExpiryMonth(): ?int
    {
        return $this->expiryMonth;
    }

    public function setExpiryMonth(?int $expiryMonth): self
    {
        $this->expiryMonth = $expiryMonth;

        return $this;
    }

    public function getExpiryYear(): ?int
    {
        return $this->expiryYear;
    }

    public function setExpiryYear(?int $expiryYear): self
    {
        $this->expiryYear = $expiryYear;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

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

    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): self
    {
        if (!$this->payments->contains($payment)) {
            $this->payments[] = $payment;
            $payment->setInstrument($this);
        }

        return $this;
    }

    public function removePayment(Payment $payment): self
    {
        if ($this->payments->removeElement($payment)) {
            if ($payment->getInstrument() === $this) {
                $payment->setInstrument(null);
            }
        }

        return $this;
    }

    public function getHide(): ?bool
    {
        return $this->hide;
    }

    public function setHide(bool $hide): self
    {
        $this->hide = $hide;

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
}
