<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\MeController;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ApiResource(
    operations: [
        new Get(
            name: 'me',
            uriTemplate: '/me',
            controller: MeController::class,
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            read: false,
            output: User::class,
        ),
        new Post(
            normalizationContext: ['groups' => ['user:read']], 
            denormalizationContext: ['groups' => ['user:write']]
        ),
        new Patch(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['user:read']], 
    denormalizationContext: ['groups' => ['user:write']] 
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read', 'user:write'])]

    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['user:read', 'user:write'])]

    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups(['user:read', 'user:write'])]

    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Groups(['user:write'])]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $address = null;

    #[ORM\Column(length: 10)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $postaleCode = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $city = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $picture = null;

    #[ORM\Column]
    #[Groups(['user:read', 'user:write'])]
    private ?bool $isValid = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?int $codeCheck = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?\DateTimeInterface $verificationCodeExpiresAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?bool $ownsStation = null;

    /**
     * @var Collection<int, Booking>
     */
    #[ORM\OneToMany(targetEntity: Booking::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $bookings;

    /**
     * @var Collection<int, ChargingStation>
     */
    #[ORM\OneToMany(targetEntity: ChargingStation::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $chargingSations;

    public function __construct()
    {
        $this->bookings = new ArrayCollection();
        $this->chargingSations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getPostaleCode(): ?string
    {
        return $this->postaleCode;
    }

    public function setPostaleCode(string $postaleCode): static
    {
        $this->postaleCode = $postaleCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): static
    {
        $this->picture = $picture;

        return $this;
    }


    public function isValid(): ?bool
    {
        return $this->isValid;
    }

    public function setIsValid(bool $isValid): static
    {
        $this->isValid = $isValid;

        return $this;
    }

    public function getCodeCheck(): ?int
    {
        return $this->codeCheck;
    }

    public function setCodeCheck(?int $codeCheck): static
    {
        $this->codeCheck = $codeCheck;

        return $this;
    }

    public function getVerificationCodeExpiresAt(): ?\DateTimeInterface
    {
        return $this->verificationCodeExpiresAt;
    }

    public function setVerificationCodeExpiresAt(?\DateTimeInterface $verificationCodeExpiresAt): static
    {
        $this->verificationCodeExpiresAt = $verificationCodeExpiresAt;

        return $this;
    }

    public function isOwnsStation(): ?bool
    {
        return $this->ownsStation;
    }

    public function setOwnsStation(?bool $ownsStation): static
    {
        $this->ownsStation = $ownsStation;

        return $this;
    }

    /**
     * @return Collection<int, Booking>
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): static
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings->add($booking);
            $booking->setUser($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): static
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getUser() === $this) {
                $booking->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ChargingStation>
     */
    public function getChargingSations(): Collection
    {
        return $this->chargingSations;
    }

    public function addChargingSation(ChargingStation $chargingSation): static
    {
        if (!$this->chargingSations->contains($chargingSation)) {
            $this->chargingSations->add($chargingSation);
            $chargingSation->setUser($this);
        }

        return $this;
    }

    public function removeChargingSation(ChargingStation $chargingSation): static
    {
        if ($this->chargingSations->removeElement($chargingSation)) {
            // set the owning side to null (unless already changed)
            if ($chargingSation->getUser() === $this) {
                $chargingSation->setUser(null);
            }
        }

        return $this;
    }
}
