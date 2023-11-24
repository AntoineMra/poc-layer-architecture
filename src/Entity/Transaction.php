<?php

namespace App\Entity;

use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Uid\Uuid;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiFilter;
use App\Entity\Enum\TransactionType;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use App\Entity\Enum\TransactionStatus;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\TransactionRepository;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Elasticsearch\Filter\MatchFilter;
use Gedmo\Mapping\Annotation\Blameable;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[ApiResource(
    operations: [
        new Put(),
        new Delete(),
        new Post(),
    ],
    normalizationContext: ['groups' => ['transaction:return']],
    denormalizationContext: ['groups' => ['transaction:write']]
)]
#[ApiResource(
    uriTemplate: '/budgets/{budgetId}/transactions',
    operations: [ new GetCollection() ],
    uriVariables: [
        'budgetId' => new Link(toProperty: 'budget', fromClass: Budget::class),
    ],
    normalizationContext: ['groups' => ['transaction:read']]
)]
#[ApiFilter(SearchFilter::class, properties: ['label' => 'partial', 'status' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['amount'])]
#[ApiFilter(MatchFilter::class, properties: ['type'])]
class Transaction
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['budget:read', 'transaction:read', 'transaction:return'])]
    private ?Uuid $id;

    #[ORM\Column(length: 255)]
    #[Groups(['budget:read', 'transaction:read', 'transaction:return', 'category:read', 'domain:read', 'transaction:write'])]
    private ?string $label = null;

    #[ORM\Column]
    #[Groups(['budget:read', 'transaction:read', 'transaction:return', 'category:read', 'domain:read', 'transaction:write'])]
    private float $amount;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['budget:read', 'transaction:read', 'transaction:return', 'category:read', 'domain:read', 'transaction:write'])]
    private ?string $date = null;

    #[ORM\Column(length: 255,  enumType: TransactionType::class)]
    #[Groups(['budget:read', 'transaction:read', 'transaction:return', 'category:read', 'domain:read', 'transaction:write'])]
    private TransactionType $type;

    #[ORM\Column(type: Types::STRING, length: 255, enumType: TransactionStatus::class)]
    #[ApiProperty(example: 'Draft')]
    #[Groups(['budget:read', 'transaction:read', 'transaction:return', 'category:read', 'domain:read', 'transaction:write'])]
    private TransactionStatus $status;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[ApiProperty(example: 'Transaction en attente de remboursement')]
    #[Groups(['budget:read', 'transaction:read', 'transaction:return', 'category:read', 'domain:read', 'transaction:write'])]
    private ?string $comment = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['transaction:write', 'transaction:read', 'transaction:return'])]
    private ?Budget $budget = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[Groups(['budget:read', 'transaction:read', 'transaction:return', 'transaction:write'])]
    private ?Category $category = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Blameable(on: 'create')]
    #[Groups('budget:read')]
    private ?User $createdBy;

    public function __construct()
    {
        $this->id = $id ?? Uuid::v6();
        $this->status = TransactionStatus::Draft;
    }

    public function getId(): ?Uuid
    {
        return $this->id = $id ?? Uuid::v6();
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

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

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(?string $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getStatus(): TransactionStatus
    {
        return $this->status;
    }

    public function setStatus(TransactionStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getType(): TransactionType
    {
        return $this->type;
    }

    public function setType(TransactionType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getBudget(): ?Budget
    {
        return $this->budget;
    }

    public function setBudget(?Budget $budget): self
    {
        $this->budget = $budget;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

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
