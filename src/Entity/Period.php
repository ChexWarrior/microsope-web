<?php

namespace App\Entity;

use App\Repository\PeriodRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PeriodRepository::class)]
class Period extends Term
{
    #[ORM\ManyToOne(inversedBy: 'periods')]
    #[ORM\JoinColumn(nullable: false)]
    private ?History $history = null;

    #[ORM\OneToMany(mappedBy: 'period', targetEntity: Event::class, orphanRemoval: true)]
    #[ORM\OrderBy(["place" => "ASC"])]
    private Collection $events;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    public function setHistory(?History $history): self
    {
        $this->history = $history;

        return $this;
    }

    public function getHistory(): ?History {
        return $this->history;
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setPeriod($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getPeriod() === $this) {
                $event->setPeriod(null);
            }
        }

        return $this;
    }
}
