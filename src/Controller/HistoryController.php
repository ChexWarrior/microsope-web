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

    #[Route('/history/new', name: 'create_history', methods: 'GET')]
    public function new(): Response {
        return $this->render('history/new.html.twig');
    }

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

    #[Route('/history/add', name: 'add_history', methods: 'POST')]
    public function addHistory(Request $request): Response {
        $data = $this->parseHistoryParameters($request);
        $history = History::build(
            $data['description'],
            $data['excluded'],
            $data['included'],
            $data['focus']
        );

        $errors = $this->checkErrors($history);
        if (count($errors) > 0) {
            return $this->returnErrorResponse($errors);
        }

        $this->entityManager->persist($history);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_history', [
            'id' => $history->getId(),
        ]);
    }

    #[Route('/history/{id}/edit', name: 'edit_history', methods: 'POST')]
    public function editHistory(History $history, Request $request): Response {
        $data = $this->parseHistoryParameters($request);
        $history->setDescription($data['description']);
        $history->setFocus($data['focus']);
        $history->setIncluded($data['included']);
        $history->setExcluded($data['excluded']);

        $errors = $this->checkErrors($history);
        if (count($errors) > 0) {
            return $this->returnErrorResponse($errors);
        }

        $this->entityManager->flush();

        return $this->render('history/info.html.twig', [
            'hideForm' => true,
            'history' => $history,
        ]);
    }

    public function checkErrors(History $history): array {
        $errors = [];
        foreach ($this->validator->validate($history) as $error) {
            $errors[] = "{$error->getPropertyPath()} - {$error->getMessage()}";
        }

        return $errors;
    }

    public function returnErrorResponse(array $errors): Response {
        return $this->render('common/errors.html.twig', [
            'errors' => $errors,
        ], new Response('', Response::HTTP_BAD_REQUEST, [
            'HX-Retarget' => '.form-errors',
            'HX-Reswap' => 'outerHTML',
        ]));
    }

    public function parseHistoryParameters(Request $request): array {
        $data = [];
        $data['description'] = $request->getPayload()->get('description');
        $data['focus'] = $request->getPayload()->get('focus');

        $includedPalette = trim($request->getPayload()->get('included', ''));
        $excludedPalette = trim($request->getPayload()->get('excluded', ''));

        $data['included'] = array_filter(explode("\n", $includedPalette));
        $data['excluded'] = array_filter(explode("\n", $excludedPalette));

        return $data;
    }
}
