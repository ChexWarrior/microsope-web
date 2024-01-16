<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Period;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    #[Route('/event/show-scenes/{id}', name: 'event_show_scenes', methods: 'GET')]
    public function getEventWithScenes(Event $event): Response
    {
        return $this->render('partials/event-show-scenes.html.twig', [
            'event' => $event,
            'numScenes' => $event->getScenes()->count()
        ]);
    }

    #[Route('/event/hide-scenes/{id}', name: 'event_hide_scenes', methods: 'GET')]
    public function getEventNoScenes(Event $event): Response
    {
        return $this->render('partials/event-hide-scenes.html.twig', [
            'event' => $event,
            'numScenes' => $event->getScenes()->count()
        ]);
    }

    #[Route('/period/{id}/events')]
    public function getEventsFromPeriod(Period $period): Response
    {
        return $this->render('partials/event-nav-dropdown.html.twig', [
            'events' => $period->getEvents(),
        ]);
    }
}
