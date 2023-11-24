<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Put;
use App\Entity\Enum\TransactionStatus;
use App\Repository\BankTranslationRepository;
use Doctrine\DBAL\Types\Types;
use Gedmo\Mapping\Annotation\Blameable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BankTranslationRepository::class)]
#[ApiResource(
    operations: [
        new Put(
            denormalizationContext: ['groups' => ['bankTranslation:put']],
        ),
        new Delete(),
        new GetCollection(
            normalizationContext: ['groups' => ['bankTranslation:read']],
        ),
    ],
)]
class BankTranslation
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['bankTranslation:read'])]
    private ?Uuid $id;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups(['bankTranslation:read'])]
    private ?string $bankLabel = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups(['bankTranslation:read', 'bankTranslation:put'])]
    private ?string $customLabel = null;

    #[ORM\ManyToOne(targetEntity: Category::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['bankTranslation:read', 'bankTranslation:put'])]
    private ?Category $category = null;

    #[ORM\Column(type: Types::STRING, length: 255, enumType: TransactionStatus::class)]
    #[ApiProperty(example: 'Draft')]
    #[Groups(['bankTranslation:read', 'bankTranslation:put'])]
    #[Assert\NotBlank(groups: ['bankTranslation:put'])]
    private TransactionStatus $status;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Blameable(on: 'create')]
    private ?User $createdBy;

    public function __construct()
    {
        $this->id = $id ?? Uuid::v6();
    }

    public function getId(): ?Uuid
    {
        return $this->id = $id ?? Uuid::v6();
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    public function getCustomLabel()
    {
        return $this->customLabel;
    }

    public function setCustomLabel($customLabel)
    {
        $this->customLabel = $customLabel;

        return $this;
    }

    public function getBankLabel()
    {
        return $this->bankLabel;
    }

    public function setBankLabel($bankLabel)
    {
        $this->bankLabel = $bankLabel;

        return $this;
    }

    public function getStatus(): TransactionStatus
    {
        return $this->status;
    }

    public function setStatus(TransactionStatus $status): void
    {
        $this->status = $status;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }
}
