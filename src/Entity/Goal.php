<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Enum\GoalStatus;
use App\Repository\GoalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Timestampable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Gedmo\Mapping\Annotation\Blameable;

#[ORM\Entity(repositoryClass: GoalRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new Put(),
        new Delete(),
        new GetCollection(),
        new Post(),
    ],
    normalizationContext: ['groups' => ['goal:read']],
    denormalizationContext: ['groups' => ['goal:write']],
)]
class Goal
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups('goal:read')]
    private ?Uuid $id;

    #[ORM\Column(length: 255)]
    #[Groups(['goal:read', 'goal:write'])]
    private string $name;

    #[ORM\Column]
    #[Groups(['goal:read', 'goal:write'])]
    private float $amount;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['goal:read', 'goal:write'])]
    private ?\DateTimeInterface $expectedEnding = null;

    #[ORM\ManyToMany(targetEntity: Invest::class, inversedBy: 'goals')]
    #[Groups(['goal:read', 'goal:write'])]
    private Collection $investments;

    #[ORM\Column(length: 255, nullable: true, enumType: GoalStatus::class)]
    #[Groups(['goal:read', 'goal:write'])]
    private ?GoalStatus $status = null;

    /**
     * @Timestampable(on="create")
     */
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups('goal:read')]
    private \DateTime $createdAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Blameable(on: 'create')]
    #[Groups('goal:read')]
    private ?User $createdBy;

    public function __construct()
    {
        $this->id = $id ?? Uuid::v6();
        $this->investments = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id = $id ?? Uuid::v6();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getExpectedEnding(): ?\DateTimeInterface
    {
        return $this->expectedEnding;
    }

    public function setExpectedEnding(\DateTimeInterface $expectedEnding): self
    {
        $this->expectedEnding = $expectedEnding;

        return $this;
    }

    #[Groups('goal:read')]
    public function getTimeRemaining(): ?string
    {
        return date_diff($this->expectedEnding, new \DateTime())->format("m:d / H:i");
    }

    /**
     * @return Collection<int, Invest>
     */
    public function getInvestments(): Collection
    {
        return $this->investments;
    }

    public function addInvestment(Invest $investment): self
    {
        if (!$this->investments->contains($investment)) {
            $this->investments->add($investment);
        }

        return $this;
    }

    public function removeInvestment(Invest $investment): self
    {
        $this->investments->removeElement($investment);

        return $this;
    }

    public function getStatus(): ?GoalStatus
    {
        return $this->status;
    }

    public function setStatus(?GoalStatus $status): void
    {
        $this->status = $status;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }
}
