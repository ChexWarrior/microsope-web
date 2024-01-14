<?php

namespace App\Controller;

use App\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SceneController extends AbstractController
{
    #[Route('/scenes/{id}', name: 'scenes', methods: 'GET')]
    public function scenes(Event $event): Response
    {
        return $this->render('partials/scene.html.twig', [
            'event' => $event
        ]);
    }

}