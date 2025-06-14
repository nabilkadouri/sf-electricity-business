<?php

namespace App\Entity;

use ApiPlatform\Metadata\Patch;
use App\Enum\BookingStatus;
use App\Enum\PaymentMethod;
use App\Repository\BookingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
#[ApiResource(
    operations: [
        new Patch(
            denormalizationContext: ['groups' => ['booking:write']],
            security: "is_granted('ROLE_USER') and object.getChargingStation().getUser() == user",
            securityMessage: "Vous n'êtes pas autorisé à modifier le statut de cette réservation."
        )
        ],
    normalizationContext:['groups' => ['booking:read']],
    denormalizationContext: ['groups' => ['booking:write']]
)]
class Booking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['booking:read', 'charging_station:read', 'user:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['booking:read', 'booking:write','user:read','charging_station:read'])]
    private ?\DateTimeInterface $createAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['booking:read', 'booking:write', 'charging_station:read', 'user:read'])]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['booking:read', 'booking:write', 'charging_station:read', 'user:read'])]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 2)]
    #[Groups(['booking:read', 'booking:write', 'charging_station:read', 'user:read'])]
    private ?string $totalAmount = null;

    #[ORM\Column(enumType: BookingStatus::class)]
    #[Groups(['booking:read', 'booking:write', 'charging_station:read', 'user:read'])]
    private ?BookingStatus $status = null;

    #[ORM\Column(enumType: PaymentMethod::class, nullable: false)]
    #[Groups(['booking:read', 'booking:write', 'charging_station:read', 'user:read'])]
    private ?PaymentMethod $paymentType = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['booking:write', 'booking:read', 'charging_station:read'])]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['booking:read','booking:write', "user:read"])]    
    private ?ChargingStation $chargingStation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreateAt(): ?\DateTimeInterface
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeInterface $createAt): static
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }


    public function getTotalAmount(): ?string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(string $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    public function getStatus(): ?BookingStatus
    {
        return $this->status;
    }

    public function setStatus(BookingStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getPaymentType(): ?PaymentMethod
    {
        return $this->paymentType;
    }

    public function setPaymentType( PaymentMethod $paymentType): static
    {
        $this->paymentType = $paymentType;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getChargingStation(): ?ChargingStation
    {
        return $this->chargingStation;
    }

    public function setChargingStation(?ChargingStation $chargingStation): static
    {
        $this->chargingStation = $chargingStation;

        return $this;
    }
}
