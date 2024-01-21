<?php

namespace App\Entity;

use App\Enum\Tone;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;

/**
 * Holds the common properties among Period, Event and Scene classes.
 * @package App\Entity
 */
#[MappedSuperclass()]
abstract class Term
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Player $createdBy = null;

    #[ORM\Column]
    private ?int $place = null;

    #[ORM\Column(length: 255, enumType: Tone::class)]
    private ?Tone $tone = null;

    #[ORM\Column(length: 1000)]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlace(): ?int
    {
        return $this->place;
    }

    public function setPlace(int $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getTone(): Tone
    {
        return $this->tone;
    }

    public function setTone(Tone $tone): self
    {
        $this->tone = $tone;

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

    public function getCreatedBy(): ?Player
    {
        return $this->createdBy;
    }

    public function setCreatedBy(Player $player): self
    {
        $this->createdBy = $player;

        return $this;
    }
}