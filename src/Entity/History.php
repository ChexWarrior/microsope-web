<?php

namespace App\Entity;

use App\Repository\HistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoryRepository::class)]
class History
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $excluded = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $included = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $focus = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getExcluded(): array
    {
        return $this->excluded;
    }

    public function setExcluded(?array $excluded): self
    {
        $this->excluded = $excluded;

        return $this;
    }

    public function getIncluded(): array
    {
        return $this->included;
    }

    public function setIncluded(?array $included): self
    {
        $this->included = $included;

        return $this;
    }

    public function getFocus(): ?string
    {
        return $this->focus;
    }

    public function setFocus(?string $focus): self
    {
        $this->focus = $focus;

        return $this;
    }
}
