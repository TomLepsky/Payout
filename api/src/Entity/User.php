<?php

namespace App\Entity;

use ApiPlatform\Core\Action\NotFoundAction;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Config;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ApiResource(
    collectionOperations: [
        'get_collection' => [
            'path' => '/users',
            'method' => 'GET',
            'security' => "is_granted('" . Config::ADMIN . "')",
            'controller' => NotFoundAction::class,
            'read' => false,
            'write' => false
        ],
        'post_collection' => [
            'path' => '/users',
            'method' => 'POST',
            'security_post_denormalize' => "is_granted('" . Config::ADMIN . "')",
            'controller' => NotFoundAction::class
        ]
    ],
    itemOperations: [
        'get_item' => [
            'path' => '/users/{id}',
            'method' => 'GET',
            'security' => "is_granted('" . Config::ADMIN . "')",
            'controller' => NotFoundAction::class,
            'read' => false,
            'write' => false
        ]
    ],
    attributes: [
        'pagination_client_items_per_page' => true,
    ],
    denormalizationContext: [
        'groups' => ['user:write']
    ],
    normalizationContext: [
        'groups' => ['userItem:read', 'userCollection:read'],
        'enable_max_depth' => true
    ]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['userItem:read', 'userCollection:read', 'user:write', 'paymentItem:read', 'paymentCollection:read', 'noteItem:read', 'noteCollection:read', 'instrumentItem:read', 'instrumentCollection:read'])]
    private int $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Groups(['userItem:read', 'userCollection:read', 'user:write'])]
    #[Assert\Email(mode: 'strict')]
    private string $email;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['userItem:read', 'userCollection:read', 'user:write', 'paymentItem:read', 'paymentCollection:read', 'noteItem:read', 'noteCollection:read', 'instrumentItem:read', 'instrumentCollection:read'])]
    #[Assert\NotBlank]
    private string $firstName;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['userItem:read', 'userCollection:read', 'user:write', 'paymentItem:read', 'paymentCollection:read', 'noteItem:read', 'noteCollection:read', 'instrumentItem:read', 'instrumentCollection:read'])]
    #[Assert\NotBlank]
    private string $lastName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $checkoutId;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups(['userItem:read', 'userCollection:read', 'user:write'])]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    #[Assert\NotBlank]
    private string $password;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Payment::class)]
    #[Groups(['userItem:read'])]
    #[MaxDepth(maxDepth: 1)]
    #[ApiProperty(readableLink: true, writableLink: true)]
    private ?Collection $payments;

    public function __construct()
    {
        $this->payments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getCheckoutId(): ?string
    {
        return $this->checkoutId;
    }

    public function setCheckoutId(?string $checkoutId): void
    {
        $this->checkoutId = $checkoutId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPayments(): ?array
    {
        return $this->payments->getValues();
    }

    public function addPayments(Payment $payment): self
    {
        if (!$this->payments->contains($payment)) {
            $this->payments[] = $payment;
            $payment->setOwner($this);
        }
        return $this;
    }

    public function removePayments(Payment $payment): self
    {
        if ($this->payments->removeElement($payment)) {
            if ($payment->getOwner() === $this) {
                $payment->setOwner(null);
            }
        }
        return $this;
    }
}
