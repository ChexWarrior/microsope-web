<?php

namespace App\Entity;

use App\Repository\PlayerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class Player
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'players')]
    #[ORM\JoinColumn(nullable: false)]
    private ?History $history = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $legacy = null;

    #[ORM\Column]
    private ?bool $lens = null;

    public function __construct(string $name, ?History $history, bool $active, ?string $legacy, bool $isLens)
    {
        $this->name = $name;
        $this->history = $history;
        $this->active = $active;
        $this->legacy = $legacy;
        $this->lens = $isLens;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getHistory(): ?History
    {
        return $this->history;
    }

    public function setHistory(?History $history): self
    {
        $this->history = $history;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getLegacy(): ?string
    {
        return $this->legacy;
    }

    public function setLegacy(?string $legacy): self
    {
        $this->legacy = $legacy;

        return $this;
    }

    public function isLens(): ?bool
    {
        return $this->lens;
    }

    public function setLens(bool $lens): self
    {
        $this->lens = $lens;

        return $this;
    }
}
