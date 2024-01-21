<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Period;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    #[Route('/event/{id}', name: 'event', methods: 'GET')]
    public function getEvent(
        Event $event,
        #[MapQueryParameter(filter: \FILTER_VALIDATE_BOOL)] bool $showScenes
    ): Response
    {
        return $this->render('event/event.html.twig', [
            'event' => $event,
            'numScenes' => $event->getScenes()->count(),
            'scenes' => $showScenes ? $event->getScenes() : [],
        ]);
    }
}
