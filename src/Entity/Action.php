<?php

namespace App\Entity;

use App\Enum\ActionEntity;
use App\Enum\ActionOperation;
use App\Repository\ActionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActionRepository::class)]
#[ORM\Table(name: '`action`')]
class Action
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?History $history = null;

    #[ORM\Column(length: 255, enumType: ActionOperation::class)]
    private ?ActionOperation $operation = null;

    #[ORM\Column(length: 255, enumType: ActionEntity::class)]
    private ?ActionEntity $entityType = null;

    #[ORM\Column]
    private ?int $entityId = null;

    #[ORM\Column(length: 1000)]
    private ?string $description = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHistory(): ?History
    {
        return $this->history;
    }

    public function setHistory(?History $history): self
    {
        $this->history = $history;

        return $this;
    }

    public function getOperation(): ActionOperation
    {
        return $this->operation;
    }

    public function setOperation(ActionOperation $operation): self
    {
        $this->operation = $operation;

        return $this;
    }

    public function getEntityType(): ActionEntity
    {
        return $this->entityType;
    }

    public function setEntityType(ActionEntity $entityType): self
    {
        $this->entityType = $entityType;

        return $this;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(int $entityId): self
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
