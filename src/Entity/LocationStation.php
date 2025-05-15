<?php

namespace App\Entity;

use App\Repository\LocationStationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: LocationStationRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['location_station:read']],
    denormalizationContext: ['groups' => ['location_station:write']]
)]
class LocationStation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['location_station:read', 'charging_station:read','booking:read','user:read' ])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['location_station:read', 'location_station:write', 'charging_station:write', 'charging_station:read','booking:read','user:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['location_station:read', 'location_station:write', 'charging_station:write', 'charging_station:read','booking:read','user:read'])]
    private ?string $address = null;

    #[ORM\Column(length: 10)]
    #[Groups(['location_station:read', 'location_station:write', 'charging_station:write', 'charging_station:read','booking:read','user:read'])]
    private ?string $postaleCode = null;

    #[ORM\Column(length: 255)]
    #[Groups(['location_station:read', 'location_station:write', 'charging_station:write', 'charging_station:read','booking:read','user:read'])]
    private ?string $city = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['location_station:read', 'location_station:write', 'charging_station:write', 'charging_station:read','booking:read','user:read'])]
    private ?float $latitude = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['location_station:read', 'location_station:write', 'charging_station:write', 'charging_station:read','booking:read','user:read'])]
    private ?float $longitude = null;

    /**
     * @var Collection<int, ChargingStation>
     */
    #[ORM\OneToMany(targetEntity: ChargingStation::class, mappedBy: 'locationStation', orphanRemoval: true)]
    #[Groups(['location_station:read'])]
    private Collection $chargingStations;

    public function __construct()
    {
        $this->chargingStations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @return Collection<int, ChargingStation>
     */
    public function getChargingStations(): Collection
    {
        return $this->chargingStations;
    }

    public function addChargingStation(ChargingStation $chargingStation): static
    {
        if (!$this->chargingStations->contains($chargingStation)) {
            $this->chargingStations->add($chargingStation);
            $chargingStation->setLocationStation($this);
        }

        return $this;
    }

    public function removeChargingStation(ChargingStation $chargingStation): static
    {
        if ($this->chargingStations->removeElement($chargingStation)) {
            // set the owning side to null (unless already changed)
            if ($chargingStation->getLocationStation() === $this) {
                $chargingStation->setLocationStation(null);
            }
        }

        return $this;
    }
}
