<?php

namespace App\Controller;

use App\Entity\History;
use App\Repository\SceneRepository;
use App\Service\HtmlFormatter;
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

    #[Route('/history/{id}/edit-form', name: 'edit_form_history', methods: 'GET')]
    public function editForm(History $history): Response {
        $htmxAttrs = [
            'hx-post' => "/history/{$history->getId()}/edit",
            'hx-target' => "#history-info-{$history->getId()}",
        ];

        return $this->render('history/info-form.html.twig', [
            'htmx_attrs' => HtmlFormatter::formatAsAttributes($htmxAttrs),
            'history' => $history,
        ]);
    }
}
