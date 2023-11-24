<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\InvestRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Gedmo\Mapping\Annotation\Blameable;

#[ORM\Entity(repositoryClass: InvestRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new Put(
            denormalizationContext: ['groups' => ['invest:put']],
        ),
        new Delete(),
        new GetCollection(),
        new Post(
            denormalizationContext: ['groups' => ['invest:post']],
        ),
    ],
    normalizationContext: ['groups' => ['invest:read']],
)]
class Invest
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;


    #[ORM\Column(length: 255)]
    #[Groups(['invest:read', 'invest:post'])]
    private string $support;

    #[ORM\Column(nullable: true)]
    #[Groups(['invest:read', 'invest:post', 'invest:put'])]
    private ?float $expectedReturn = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['invest:read', 'invest:post', 'invest:put'])]
    private ?int $monthlyInvestment = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['invest:read', 'invest:post', 'invest:put'])]
    private ?string $specificCondition = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['invest:read', 'invest:post', 'invest:put'])]
    private ?float $currentAmount = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['invest:read', 'invest:post'])]
    private ?int $initialAmount = null;

    #[ORM\ManyToMany(targetEntity: Goal::class, mappedBy: 'investments')]
    #[Groups(['invest:read', 'invest:post', 'invest:put'])]
    private Collection $goals;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Blameable(on: 'create')]
    #[Groups('invest:read')]
    private ?User $createdBy;

    public function __construct(?Uuid $id = null)
    {
        $this->id = $id ?? Uuid::v6();
        $this->goals = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getSupport(): string
    {
        return $this->support;
    }

    public function setSupport(string $support): self
    {
        $this->support = $support;

        return $this;
    }

    public function getExpectedReturn(): ?float
    {
        return $this->expectedReturn;
    }

    public function setExpectedReturn(float $expectedReturn): self
    {
        $this->expectedReturn = $expectedReturn;

        return $this;
    }

    public function getMonthlyInvestment(): ?int
    {
        return $this->monthlyInvestment;
    }

    public function setMonthlyInvestment(int $monthlyInvestment): self
    {
        $this->monthlyInvestment = $monthlyInvestment;

        return $this;
    }

    public function getSpecificCondition(): ?string
    {
        return $this->specificCondition;
    }

    public function setSpecificCondition(string $specificCondition): self
    {
        $this->specificCondition = $specificCondition;

        return $this;
    }

    public function getCurrentAmount(): ?float
    {
        return $this->currentAmount;
    }

    public function setCurrentAmount(float $currentAmount): self
    {
        $this->currentAmount = $currentAmount;

        return $this;
    }

    public function getInitialAmount(): ?int
    {
        return $this->initialAmount;
    }

    public function setInitialAmount(int $initialAmount): self
    {
        $this->initialAmount = $initialAmount;

        return $this;
    }

    /**
     * @return Collection<int, Goal>
     */
    public function getGoals(): Collection
    {
        return $this->goals;
    }

    public function addGoal(Goal $goal): self
    {
        if (!$this->goals->contains($goal)) {
            $this->goals->add($goal);
            $goal->addInvestment($this);
        }

        return $this;
    }

    public function removeGoal(Goal $goal): self
    {
        if ($this->goals->removeElement($goal)) {
            $goal->removeInvestment($this);
        }

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }
}
