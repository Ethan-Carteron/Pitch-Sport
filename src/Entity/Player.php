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
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'players')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Club $club = null;

    /**
     * @var Collection<int, WellnessQuestions>
     */
    #[ORM\OneToMany(targetEntity: WellnessQuestions::class, mappedBy: 'player', orphanRemoval: true)]
    private Collection $wellnessQuestions;

    /**
     * @var Collection<int, Workload>
     */
    #[ORM\OneToMany(targetEntity: Workload::class, mappedBy: 'player', orphanRemoval: true)]
    private Collection $workloads;

    #[ORM\Column(nullable: true)]
    private ?int $score = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $uid = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telegramChatId = null;

    public function __construct()
    {
        $this->wellnessQuestions = new ArrayCollection();
        $this->workloads = new ArrayCollection();
        $this->uid = bin2hex(random_bytes(16));
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

    public function getClub(): ?Club
    {
        return $this->club;
    }

    public function setClub(?Club $club): static
    {
        $this->club = $club;

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
            $wellnessQuestion->setPlayer($this);
        }

        return $this;
    }

    public function removeWellnessQuestion(WellnessQuestions $wellnessQuestion): static
    {
        if ($this->wellnessQuestions->removeElement($wellnessQuestion)) {
            // set the owning side to null (unless already changed)
            if ($wellnessQuestion->getPlayer() === $this) {
                $wellnessQuestion->setPlayer(null);
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
            $workload->setPlayer($this);
        }

        return $this;
    }

    public function removeWorkload(Workload $workload): static
    {
        if ($this->workloads->removeElement($workload)) {
            // set the owning side to null (unless already changed)
            if ($workload->getPlayer() === $this) {
                $workload->setPlayer(null);
            }
        }

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getUid(): ?string
    {
        return $this->uid;
    }

    public function setUid(?string $uid): static
    {
        $this->uid = $uid;

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

    public function getTelegramChatId(): ?string
    {
        return $this->telegramChatId;
    }

    public function setTelegramChatId(?string $telegramChatId): static
    {
        $this->telegramChatId = $telegramChatId;

        return $this;
    }
}
