<?php

namespace App\Entity;

use App\Entity\MediaObject;
use ApiPlatform\Metadata\Post;
use App\Repository\BankExtractionRepository;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Gedmo\Mapping\Annotation\Blameable;
use App\Controller\MediaObject\CreateExtractionAction;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BankExtractionRepository::class)]
#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/budgets/extraction',
            controller: CreateExtractionAction::class,
            validationContext: ['groups' => ['Default', 'extraction:create']],
        ),
    ],
    denormalizationContext: ['groups' => ['extraction:create']],
    normalizationContext: ['groups' => ['extraction:read']]
)]
class BankExtraction
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ApiProperty(identifier: true)]
    private Uuid $id;

    #[ORM\OneToOne(targetEntity: MediaObject::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(groups: ['extraction:create'])]
    #[Groups(['extraction:create'])]
    private MediaObject $mediaObject;

    #[ORM\OneToOne(targetEntity: Budget::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(groups: ['extraction:create'])]
    #[Groups(['extraction:create'])]
    private Budget $budget;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Blameable(on: 'create')]
    private ?User $createdBy;

    public function __construct($id = null)
    {
        $this->id = $id ?? Uuid::v6();
    }

    public function getId(): ?Uuid
    {
        return $this->id = $id ?? Uuid::v6();
    }

    public function getMediaObject()
    {
        return $this->mediaObject;
    }

    public function setMediaObject($mediaObject)
    {
        $this->mediaObject = $mediaObject;

        return $this;
    }

    public function getBudget()
    {
        return $this->budget;
    }

    public function setBudget($budget)
    {
        $this->budget = $budget;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }
}
