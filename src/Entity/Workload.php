<?php

namespace App\Entity;

use App\Entity\Impl\BaseEntity;
use App\Repository\WorkloadRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkloadRepository::class)]
class Workload extends BaseEntity
{
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    protected DateTime $createdDate;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(nullable: true)]
    private ?int $duration = null;
    #[ORM\Column(nullable: true)]
    private ?int $totalDistance = null;
    #[ORM\Column(nullable: true)]
    private ?float $maxSpeed = null;
    #[ORM\Column(nullable: true)]
    private ?int $acceleration = null;
    #[ORM\ManyToOne(inversedBy: 'workloads')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Player $player = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getTotalDistance(): ?int
    {
        return $this->totalDistance;
    }

    public function setTotalDistance(?int $totalDistance): static
    {
        $this->totalDistance = $totalDistance;

        return $this;
    }

    public function getMaxSpeed(): ?float
    {
        return $this->maxSpeed;
    }

    public function setMaxSpeed(?float $maxSpeed): static
    {
        $this->maxSpeed = $maxSpeed;

        return $this;
    }

    public function getAcceleration(): ?int
    {
        return $this->acceleration;
    }

    public function setAcceleration(?int $acceleration): static
    {
        $this->acceleration = $acceleration;

        return $this;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function setPlayer(?Player $player): static
    {
        $this->player = $player;

        return $this;
    }

    public function getCreatedDate(): DateTime
    {
        return $this->createdDate;
    }

    public function setCreatedDate(DateTime $createdDate): static
    {
        $this->createdDate = $createdDate;

        return $this;
    }
}
