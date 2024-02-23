<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event extends Term
{
    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Period $period = null;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Scene::class, orphanRemoval: true, cascade: ['persist'], fetch: 'EXTRA_LAZY')]
    #[ORM\OrderBy(["place" => "ASC"])]
    private Collection $scenes;

    public function __construct()
    {
        $this->scenes = new ArrayCollection();
    }

    public function getPeriod(): ?Period
    {
        return $this->period;
    }

    public function setPeriod(?Period $period): self
    {
        $this->period = $period;

        return $this;
    }

    /**
     * @return Collection<int, Scene>
     */
    public function getScenes(): Collection
    {
        return $this->scenes;
    }

    public function addScene(Scene $scene): self
    {
        if (!$this->scenes->contains($scene)) {
            $this->scenes->add($scene);
            $scene->setEvent($this);
        }

        return $this;
    }

    public function removeScene(Scene $scene): self
    {
        if ($this->scenes->removeElement($scene)) {
            // set the owning side to null (unless already changed)
            if ($scene->getEvent() === $this) {
                $scene->setEvent(null);
            }
        }

        return $this;
    }
}
