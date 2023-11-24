<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Uid\Uuid;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Enum\BudgetStatus;
use ApiPlatform\Metadata\ApiFilter;
use App\Entity\Enum\TransactionType;
use App\Repository\BudgetRepository;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation\Timestampable;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use App\Validator\Constraints\UniqueMonthBudget;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Gedmo\Mapping\Annotation\Blameable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BudgetRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new Put(
            denormalizationContext: ['groups' => ['budget:put']]
        ),
        new Delete(),
        new GetCollection(),
        new Post(),
    ],
    normalizationContext: ['groups' => ['budget:read']],
    denormalizationContext: ['groups' => ['budget:write']]
)]
#[ApiFilter(SearchFilter::class, properties: ['id' => 'exact', 'status' => 'exact', 'date' => 'partial'])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt' => 'DESC'])]
class Budget
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups('budget:read')]
    private ?Uuid $id;

    #[ORM\Column(length: 255)]
    #[ApiProperty(example: 'Budget Janvier 2023')]
    #[Groups(['budget:read', 'budget:write', 'budget:put'])]
    private string $title;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank]
    #[UniqueMonthBudget]
    #[Groups(['budget:read', 'budget:write', 'budget:put'])]
    private \DateTime $date;

    #[ORM\Column(type: Types::STRING, length: 255, enumType: BudgetStatus::class)]
    #[ApiProperty(example: 'Draft')]
    #[Groups(['budget:read', 'budget:put'])]
    private BudgetStatus $status;

    #[ORM\OneToMany(mappedBy: 'budget', targetEntity: Transaction::class, orphanRemoval: true)]
    #[Groups(['budget:read', 'budget:put'])]
    private Collection $transactions;

    #[ORM\OneToOne(targetEntity: BankExtraction::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ApiProperty(types: ['https://schema.org/image'])]
    private ?BankExtraction $extraction = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Timestampable(on: 'create')]
    #[Groups('budget:read')]
    private \DateTime $createdAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Blameable(on: 'create')]
    #[Groups('budget:read')]
    private ?User $createdBy;

    public function __construct()
    {
        $this->id = $id ?? Uuid::v6();
        $this->transactions = new ArrayCollection();
        $this->status = BudgetStatus::Draft;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    #[Groups('budget:read')]
    public function getFormatedDate(): string
    {
        return $this->date->format('m/y');
    }

    public function setDate(?\DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getStatus(): BudgetStatus
    {
        return $this->status;
    }

    public function setStatus(?BudgetStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setBudget($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->removeElement($transaction)) {
            if ($transaction->getBudget() === $this) {
                $transaction->setBudget(null);
            }
        }

        return $this;
    }

    #[Groups('budget:read')]
    public function getTransactionsTotalIncome(): int
    {
        $totalIncome = 0;
        /** @var Transaction $transaction */
        foreach ($this->transactions as $transaction) {
            if ($transaction->getType() === TransactionType::Income) {
                $totalIncome += $transaction->getAmount();
            }
        }

        return $totalIncome;
    }

    #[Groups('budget:read')]
    public function getTransactionsTotalExpense(): int
    {
        $totalExpense = 0;
        /** @var Transaction $transaction */
        foreach ($this->transactions as $transaction) {
            if ($transaction->getType() === TransactionType::Expense) {
                $totalExpense += $transaction->getAmount();
            }
        }

        return $totalExpense;
    }

    #[Groups('budget:read')]
    public function getTransactionsDifferential(): int
    {
        return $this->getTransactionsTotalIncome() - $this->getTransactionsTotalExpense();
    }

    #[Groups('budget:read')]
    public function getTransactionsPercent(): int
    {
        {
            $totalIncome = $this->getTransactionsTotalIncome();
            $totalDiff = $this->getTransactionsDifferential();
        
            if ($totalIncome === 0) {
                return 0;
            }
        
            return round((($totalDiff / $totalIncome) * 100));
        }
    }


    #[Groups('budget:read')]
    public function getTransactionsMedium(): int
    {
        $medium = 0;
        /** @var Transaction $transaction */
        foreach ($this->transactions as $transaction) {
            $medium += $transaction->getAmount() / $this->transactions->count();
        }

        return $medium;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getExtraction()
    {
        return $this->extraction;
    }

    public function setExtraction($extraction)
    {
        $this->extraction = $extraction;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }
}
