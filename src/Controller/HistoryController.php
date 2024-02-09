<?php

namespace App\Controller;

use App\Entity\History;
use App\Repository\SceneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HistoryController extends AbstractController
{
    public function __construct(
        private SceneRepository $sceneRepository,
    ){}

    #[Route('/history/{id}', name: 'app_history', methods: 'GET')]
    public function view(History $history): Response
    {
        $numScenesByEvent = $this->sceneRepository->getNumScenesForEventsInHistory($history);
        return $this->render('history/index.html.twig', [
            'history' => $history,
            'numScenesByEvent' => $numScenesByEvent,
        ]);
    }

    #[Route('/history/{id}/board', name: 'history_board', methods: 'GET')]
    public function getBoard(History $history)
    {
        $numScenesByEvent = $this->sceneRepository->getNumScenesForEventsInHistory($history);
        return $this->render('history/board.html.twig', [
            'hideTermForm' => true,
            'periods' => $history->getPeriods(),
            'numScenesByEvent' => $numScenesByEvent,
        ]);
    }

    #[Route('/history/form/hide', name: 'hide_form', methods: 'GET')]
    public function hideForm(): Response
    {
        return new Response(
            '<div id="term-dialog" class="backdrop hidden"></div>'
        );
    }
}
