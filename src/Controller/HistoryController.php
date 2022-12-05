<?php

namespace App\Controller;

use App\Entity\History;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HistoryController extends AbstractController
{
    #[Route('/history/{id}', name: 'app_history', methods: 'GET')]
    public function view(History $history): Response
    {
        return $this->render('history/index.html.twig', [
            'controller_name' => 'HistoryController',
            'history' => $history,
        ]);
    }
}
