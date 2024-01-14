<?php

namespace App\Controller;

use App\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class SceneController extends AbstractController
{
    public function scenes(Event $event): Response
    {
        return $this->render('partials/scene.html.twig', [
            'event' => $event
        ]);
    }

}