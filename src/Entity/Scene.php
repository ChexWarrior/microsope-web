<?php

namespace App\Entity;

use App\Repository\SceneRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SceneRepository::class)]
class Scene
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $place = null;

    #[ORM\Column(length: 255)]
    private ?string $tone = null;

    #[ORM\Column(length: 1000)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'scenes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Event $event = null;

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

    public function getTone(): ?string
    {
        return $this->tone;
    }

    public function setTone(string $tone): self
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

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }
}
