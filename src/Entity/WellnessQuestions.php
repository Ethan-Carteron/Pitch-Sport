<?php

namespace App\Entity;

use App\Entity\Impl\BaseEntity;
use App\Repository\WellnessQuestionsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WellnessQuestionsRepository::class)]
class WellnessQuestions extends BaseEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $fatigue = null;

    #[ORM\Column(nullable: true)]
    private ?int $sleep = null;

    #[ORM\Column(nullable: true)]
    private ?int $stress = null;

    #[ORM\ManyToOne(inversedBy: 'wellnessQuestions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Player $playerId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFatigue(): ?int
    {
        return $this->fatigue;
    }

    public function setFatigue(?int $fatigue): static
    {
        $this->fatigue = $fatigue;

        return $this;
    }

    public function getSleep(): ?int
    {
        return $this->sleep;
    }

    public function setSleep(?int $sleep): static
    {
        $this->sleep = $sleep;

        return $this;
    }

    public function getStress(): ?int
    {
        return $this->stress;
    }

    public function setStress(?int $stress): static
    {
        $this->stress = $stress;

        return $this;
    }

    public function getPlayerId(): ?Player
    {
        return $this->playerId;
    }

    public function setPlayerId(?Player $playerId): static
    {
        $this->playerId = $playerId;

        return $this;
    }
}
