<?php

namespace App\Entity;

use App\Enum\DayOfWeek;
use App\Repository\TimeslotRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TimeslotRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['timeslot:read']],
    denormalizationContext: ['groups' => ['timeslot:write']]
)]
class Timeslot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['timeslot:read', 'charging_station:read','booking:read'])]
    private ?int $id = null;

    #[ORM\Column(enumType: DayOfWeek::class)]
    #[Groups(['timeslot:read', 'timeslot:write', 'charging_station:read','charging_station:write','booking:read'])]
    private ?DayOfWeek $dayOfWeek = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Groups(['timeslot:read', 'timeslot:write', 'charging_station:read','charging_station:write','booking:read'])]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Groups(['timeslot:read', 'timeslot:write', 'charging_station:read','charging_station:write','booking:read'])]
    private ?\DateTimeInterface $endTime = null;

    #[ORM\ManyToOne(inversedBy: 'timeslots')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['timeslot:read', 'timeslot:write'])]
    private ?ChargingStation $chargingStation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDayOfWeek(): ?DayOfWeek
    {
        return $this->dayOfWeek;
    }

    public function setDayOfWeek(DayOfWeek $dayOfWeek): static
    {
        $this->dayOfWeek = $dayOfWeek;

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeInterface $endTime): static
    {
        $this->endTime = $endTime;

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
