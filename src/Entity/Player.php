<?php

namespace App\Entity;

use App\Entity\Impl\BaseEntity;
use App\Repository\PlayerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class Player extends BaseEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $firsteName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\ManyToOne(inversedBy: 'players')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Club $clubId = null;

    /**
     * @var Collection<int, WellnessQuestions>
     */
    #[ORM\OneToMany(targetEntity: WellnessQuestions::class, mappedBy: 'playerId', orphanRemoval: true)]
    private Collection $wellnessQuestions;

    /**
     * @var Collection<int, Workload>
     */
    #[ORM\OneToMany(targetEntity: Workload::class, mappedBy: 'playerId', orphanRemoval: true)]
    private Collection $workloads;

    public function __construct()
    {
        $this->wellnessQuestions = new ArrayCollection();
        $this->workloads = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirsteName(): ?string
    {
        return $this->firsteName;
    }

    public function setFirsteName(string $firsteName): static
    {
        $this->firsteName = $firsteName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getClubId(): ?Club
    {
        return $this->clubId;
    }

    public function setClubId(?Club $clubId): static
    {
        $this->clubId = $clubId;

        return $this;
    }

    /**
     * @return Collection<int, WellnessQuestions>
     */
    public function getWellnessQuestions(): Collection
    {
        return $this->wellnessQuestions;
    }

    public function addWellnessQuestion(WellnessQuestions $wellnessQuestion): static
    {
        if (!$this->wellnessQuestions->contains($wellnessQuestion)) {
            $this->wellnessQuestions->add($wellnessQuestion);
            $wellnessQuestion->setPlayerId($this);
        }

        return $this;
    }

    public function removeWellnessQuestion(WellnessQuestions $wellnessQuestion): static
    {
        if ($this->wellnessQuestions->removeElement($wellnessQuestion)) {
            // set the owning side to null (unless already changed)
            if ($wellnessQuestion->getPlayerId() === $this) {
                $wellnessQuestion->setPlayerId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Workload>
     */
    public function getWorkloads(): Collection
    {
        return $this->workloads;
    }

    public function addWorkload(Workload $workload): static
    {
        if (!$this->workloads->contains($workload)) {
            $this->workloads->add($workload);
            $workload->setPlayerId($this);
        }

        return $this;
    }

    public function removeWorkload(Workload $workload): static
    {
        if ($this->workloads->removeElement($workload)) {
            // set the owning side to null (unless already changed)
            if ($workload->getPlayerId() === $this) {
                $workload->setPlayerId(null);
            }
        }

        return $this;
    }
}
