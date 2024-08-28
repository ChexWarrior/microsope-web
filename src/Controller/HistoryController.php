<?php

namespace App\Controller;

use App\Entity\History;
use App\Repository\HistoryRepository;
use App\Repository\SceneRepository;
use App\Service\HtmlFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class HistoryController extends AbstractController
{
    public function __construct(
        private SceneRepository $sceneRepository,
        private HistoryRepository $historyRepository,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
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

    #[Route('/history/info-form/hide', name: 'hide_info_form', methods: 'GET')]
    public function hideInfoForm(): Response
    {
        return new Response(
            '<div id="history-dialog" class="backdrop hidden"></div>'
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

    #[Route('/history/{id}/edit', name: 'edit_history', methods: 'POST')]
    public function editHistory(History $history, Request $request): Response {
        $description = $request->getPayload()->get('description');
        $focus = $request->getPayload()->get('focus');
        $includedPalette = $request->getPayload()->get('included');
        $excludedPalette = $request->getPayload()->get('excluded');

        $includedPalette = explode("\n", $includedPalette);
        $excludedPalette = explode("\n", $excludedPalette);

        $history->setDescription($description);
        $history->setFocus($focus);
        $history->setIncluded($includedPalette);
        $history->setExcluded($excludedPalette);
        $errors = [];
        foreach ($this->validator->validate($history) as $error) {
            $errors[] = "{$error->getPropertyPath()} - {$error->getMessage()}";
        }

        if (count($errors) > 0) {
            return $this->render('common/errors.html.twig', [
                'errors' => $errors,
            ], new Response('', Response::HTTP_BAD_REQUEST, [
                'HX-Retarget' => '.form-errors',
                'HX-Reswap' => 'outerHTML',
            ]));
        }

        $this->entityManager->flush();

        return $this->render('history/info.html.twig', [
            'hideForm' => true,
            'history' => $history,
        ]);
    }
}
