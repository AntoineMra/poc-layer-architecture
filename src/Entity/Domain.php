<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\DomainRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Uuid;
use Gedmo\Mapping\Annotation\Blameable;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DomainRepository::class)]
#[ApiResource(
    operations: [
        new Get(),
        new Put(),
        new Delete(),
        new GetCollection(),
        new Post(),
    ],
    normalizationContext: ['groups' => ['domain:read']],
    denormalizationContext: ['groups' => ['domain:write']]
)]
class Domain
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['transaction:read', 'domain:read'])]
    private ?Uuid $id;

    #[ORM\Column(length: 255)]
    #[Groups(['transaction:read', 'domain:read', 'domain:write'])]
    private ?string $label = null;

    #[ORM\OneToMany(mappedBy: 'domain', targetEntity: Category::class, orphanRemoval: true)]
    #[Groups(['domain:read', 'domain:write'])]
    private Collection $categories;

    #[ORM\Column(length: 255, nullable: false)]
    #[Groups(['transaction:read', 'category:read', 'domain:read', 'domain:write'])]
    #[Assert\Regex(pattern: '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', message: 'The color must be a valid hex color')]
    private string $color;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Blameable(on: 'create')]
    #[Groups('domain:read')]
    private ?User $createdBy;

    public function __construct()
    {
        $this->id = $id ?? Uuid::v6();
        $this->categories = new ArrayCollection();
        $this->color = '#FFFFFF';
    }

    public function getId(): Uuid
    {
        return $this->id;
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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): void
    {
        $this->color = $color;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->setDomain($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        if ($this->categories->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getDomain() === $this) {
                $category->setDomain(null);
            }
        }

        return $this;
    }

    #[Groups('domain:read')]
    public function getTransactionsTotal(): int
    {
        $total = 0;
        /** @var Category $category */
        foreach ($this->categories as $category) {
            $total += $category->getTransactionsTotal();
        }

        return $total;
    }


    #[Groups('domain:read')]
    public function getTransactionsMedium(): int
    {
        $medium = 0;
        /** @var Category $category */
        foreach ($this->categories as $category) {
            $medium += $category->getTransactionsMedium() / $this->categories->count();
        }

        return $medium;
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
