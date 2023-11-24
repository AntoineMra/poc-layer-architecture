<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Enum\MemoStatus;
use App\Repository\MemoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Timestampable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Gedmo\Mapping\Annotation\Blameable;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MemoRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new Put(
            denormalizationContext: ['groups' => ['memo:put']],
        ),
        new Delete(),
        new GetCollection(),
        new Post(
            denormalizationContext: ['groups' => ['memo:post']],
        ),
    ],
    normalizationContext: ['groups' => ['memo:read']],
)]
class Memo
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups('memo:read')]
    private ?Uuid $id;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Groups(['memo:read', 'memo:post', 'memo:put'])]
    private string $content;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank]
    #[Groups(['memo:read', 'memo:post', 'memo:put'])]
    private \DateTime $date;

    #[ORM\Column(length: 255, nullable: true, enumType: MemoStatus::class)]
    #[Groups(['memo:read', 'memo:put'])]
    private ?MemoStatus $status = MemoStatus::Ongoing;

    /**
     * @Timestampable(on="create")
     */
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups('memo:read')]
    private \DateTime $createdAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Blameable(on: 'create')]
    #[Groups('memo:read')]
    private ?User $createdBy;

    public function __construct()
    {
        $this->id = $id ?? Uuid::v6();
    }

    public function getId(): ?Uuid
    {
        return $this->id = $id ?? Uuid::v6();
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    #[Groups('memo:read')]
    public function getFormatedDate(): string
    {
        return $this->date->format('m/y');
    }

    public function setDate(?\DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getStatus(): ?MemoStatus
    {
        return $this->status;
    }

    public function setStatus(?MemoStatus $status): void
    {
        $this->status = $status;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }
}
