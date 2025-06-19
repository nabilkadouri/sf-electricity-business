<?php

namespace App\Entity;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Controller\GetChargingStationByUserController;
use App\Enum\ChargingStationStatus;
use App\Repository\ChargingStationRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ChargingStationRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            name: 'charging_stations',
            uriTemplate: '/charging_stations',
            controller: GetChargingStationByUserController::class,
            security: "is_granted('IS_AUTHENTICATED_FULLY')",
            read: false,
            output: ChargingStation::class,
            normalizationContext: ['groups' => ['charging_station:read']]
        ),
        new Post(
            security: "is_granted('EDIT_CHARGING_STATION', object)",
            normalizationContext: ['groups' => ['charging_station:read']],
            denormalizationContext: ['groups' => ['charging_station:write']]
        ),
        new Delete(security: "is_granted('DELETE_CHARGING_STATION', object)"),
    ],
    normalizationContext: ['groups' => ['charging_station:read']],
    denormalizationContext: ['groups' => ['charging_station:write']]
)]
class ChargingStation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['charging_station:read', 'booking:read','user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['charging_station:read', 'charging_station:write', 'booking:read', 'user:read'])]
    private ?string $nameStation = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['charging_station:read', 'charging_station:write'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    #[Groups(['charging_station:read', 'charging_station:write', 'booking:read','user:read'])]
    private ?string $power = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['charging_station:read', 'charging_station:write'])] 
    private ?string $plugType = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 6, scale: 2)]
    #[Groups(['charging_station:read', 'charging_station:write','booking:read',"user:read"])]
    private ?string $pricePerHour = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['charging_station:read', 'charging_station:write','user:read'], )]
    private ?string $picture = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['charging_station:read','user:read'])]
    private ?\DateTimeInterface $createAt = null;

    #[ORM\Column(enumType: ChargingStationStatus::class)]
    #[Groups(['charging_station:read','charging_station:write','booking:read','user:read'])]
    private ?ChargingStationStatus $status = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['charging_station:read', 'charging_station:write','user:read'])]
    private bool $isAvailable = false;

    #[ORM\ManyToOne(targetEntity: LocationStation::class, inversedBy: 'chargingStations', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['charging_station:read', 'charging_station:write','booking:read','user:read'])]
    private ?LocationStation $locationStation = null;

    /**
     * @var Collection<int, Timeslot>
     */
    #[ORM\OneToMany(targetEntity: Timeslot::class, mappedBy: 'chargingStation', orphanRemoval: true, cascade: ['persist'])]
    #[Groups(['charging_station:read','charging_station:write','user:read'])]
    private Collection $timeslots;

    #[ORM\ManyToOne(inversedBy: 'chargingStations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['charging_station:read', 'charging_station:write'])]
    private ?User $user = null;

    /**
     * @var Collection<int, Booking>
     */
    #[ORM\OneToMany(targetEntity: Booking::class, mappedBy: 'chargingStation', orphanRemoval: true)]
    #[Groups(['charging_station:read', "user:read"])]
    private Collection $bookings;

    public function __construct()
    {
        $this->createAt = new DateTimeImmutable();
        $this->bookings = new ArrayCollection();
        $this->timeslots = new ArrayCollection();
        $this->plugType = 'Type 2';
        $this->picture = 'images/default_picture_station.png';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameStation(): ?string
    {
        return $this->nameStation;
    }

    public function setNameStation(string $nameStation): static
    {
        $this->nameStation = $nameStation;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPower(): ?string
    {
        return $this->power;
    }

    public function setPower(string $power): static
    {
        $this->power = $power;

        return $this;
    }

    public function getPlugType(): ?string
    {
        return $this->plugType;
    }

    public function setPlugType(?string $plugType): static
    {
        $this->plugType = $plugType;

        return $this;
    }

    public function getPricePerHour(): ?string
    {
        return $this->pricePerHour;
    }

    public function setPricePerHour(string $pricePerHour): static
    {
        $this->pricePerHour = $pricePerHour;

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

    public function getCreateAt(): ?\DateTimeInterface
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeInterface $createAt): static
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getStatus(): ?ChargingStationStatus
    {
        return $this->status;
    }

    public function setStatus(ChargingStationStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function isAvailable(): ?bool
    {
        return $this->isAvailable;
    }

    public function setIsAvailable(bool $isAvailable): static
    {
        $this->isAvailable = $isAvailable;

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
            $booking->setChargingStation($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): static
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getChargingStation() === $this) {
                $booking->setChargingStation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Timeslot>
     */
    public function getTimeslots(): Collection
    {
        return $this->timeslots;
    }

    public function addTimeslot(Timeslot $timeslot): static
    {
        if (!$this->timeslots->contains($timeslot)) {
            $this->timeslots->add($timeslot);
            $timeslot->setChargingStation($this);
        }

        return $this;
    }

    public function removeTimeslot(Timeslot $timeslot): static
    {
        if ($this->timeslots->removeElement($timeslot)) {
            // set the owning side to null (unless already changed)
            if ($timeslot->getChargingStation() === $this) {
                $timeslot->setChargingStation(null);
            }
        }

        return $this;
    }

    public function getLocationStation(): ?LocationStation
    {
        return $this->locationStation;
    }

    public function setLocationStation(?LocationStation $locationStation): static
    {
        $this->locationStation = $locationStation;

        return $this;
    }
}
