<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
#[ApiResource]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, ChargingStation>
     */
    #[ORM\OneToMany(targetEntity: ChargingStation::class, mappedBy: 'location', orphanRemoval: true)]
    private Collection $chargingStations;

    public function __construct()
    {
        $this->chargingStations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
            $chargingStation->setLocation($this);
        }

        return $this;
    }

    public function removeChargingStation(ChargingStation $chargingStation): static
    {
        if ($this->chargingStations->removeElement($chargingStation)) {
            // set the owning side to null (unless already changed)
            if ($chargingStation->getLocation() === $this) {
                $chargingStation->setLocation(null);
            }
        }

        return $this;
    }
}
