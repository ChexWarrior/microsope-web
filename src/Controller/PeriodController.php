<?php

namespace App\Controller;

use App\Entity\History;
use App\Entity\Period;
use App\Repository\HistoryRepository;
use App\Repository\PeriodRepository;
use App\Repository\PlayerRepository;
use App\Repository\SceneRepository;
use App\Service\HtmlFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PeriodController extends TermController
{
    public function __construct(
        private PeriodRepository $periodRepository,
        private HistoryRepository $historyRepository,
        private EntityManagerInterface $entityManager,
        private SceneRepository $sceneRepository,
        PlayerRepository $playerRepository,
        ValidatorInterface $validator
    ) {
        parent::__construct($playerRepository, $validator);
    }

    #[Route('/period/{id}', name: 'period', methods: 'GET')]
    public function getPeriod(Period $period): Response {
        return $this->render('period/container.html.twig', [
            'hideTermForm' => true,
            'period' => $period,
            'numScenesByEvent' => $this->sceneRepository->getNumScenesForEventsInPeriod($period),
        ]);
    }

    /**
     * Returns the form that will edit an existing period.
     *
     * @param Period $period - The period being edited.
     * @return Response
     */
    #[Route('/period/{id}/edit-form', name: 'edit_form_period', methods: 'GET')]
    public function editForm(Period $period): Response {
        $lastPlace = $this->periodRepository->findLastPlace($period->getHistory());
        $players = $this->playerRepository->findAllByActiveAndHistory($period->getHistory());

        $title = "Edit Period: " . ($period->getPlace() + 1);
        $htmxAttrs = [
            'hx-post' => "/period/{$period->getId()}/edit",
            'hx-target' => '#board',
        ];

        return $this->render('history/term-form.html.twig', [
            'title' => $title,
            'term' => $period,
            'lastPlace' => $lastPlace,
            'players' => $players,
            'parentId' => $period->getHistory()->getId(),
            'htmx_attrs' => HtmlFormatter::formatAsAttributes($htmxAttrs),
        ]);
    }

    /**
     * Returns the form that will add a new period.
     *
     * @param History $history - The history the new period belongs to.
     * @param int $defaultPlace - The default place for the new period.
     */
    #[Route('/period/{id}/add-form', name: 'add_form_period', methods: 'GET')]
    public function addForm(
        History $history,
        #[MapQueryParameter(name:"place")] int $defaultPlace = 0
    ): Response {
        try {
            $lastPlace = $this->periodRepository->findLastPlace($history);
        } catch (NoResultException $e) {
            $lastPlace = -1;
        }

        $players = $this->playerRepository->findAllByActiveAndHistory($history);
        $title = "Add New Period";
        $term = [
            'description' => '',
            'place' => $defaultPlace,
            'tone' => ['value' => ''],
            'createdBy' => ['id' => -1],
        ];
        $htmxAttrs = [
            'hx-post' => '/period/add',
            'hx-target' => '#board',
        ];

        return $this->render('history/term-form.html.twig', [
            'title' => $title,
            'term' => $term,
            'lastPlace' => $lastPlace + 1,
            'players' => $players,
            'parentId' => $history->getId(),
            'htmx_attrs' => HtmlFormatter::formatAsAttributes($htmxAttrs),
        ]);
    }

    #[Route('/period/{id}/edit', name: 'edit_period', methods: 'POST')]
    public function editPeriod(Period $period, Request $request): Response {
        $termParams = $this->parseTermParameters($request);
        $parentHistory = $period->getHistory();

        try {
            $editedPeriod = $this->editTerm($period, $termParams, $this->periodRepository, $parentHistory);
            $errors = $this->validateTerm($editedPeriod);
        } catch (NoResultException) {
            $errors = ['place - Must be greater than or equal to 0.'];
        }

        if (count($errors) > 0) {
            return $this->errorResponse($errors, '#term-errors');
        }

        $this->entityManager->flush();

        // Regenerate the entire board.
        return $this->redirectToRoute('history_board', [
                'id' => $parentHistory->getId(),
            ]
        );
    }

    #[Route('/period/add', name: 'add_period', methods: 'POST')]
    public function addPeriod(Request $request): Response {
        $termParams = $this->parseTermParameters($request);
        $parentHistory = $this->historyRepository->find($termParams['parentId']);

        try {
            $newPeriod = $this->addTerm(new Period(), $termParams, $this->periodRepository, $parentHistory);
            $errors = $this->validateTerm($newPeriod);
        } catch (NoResultException) {
            $errors = ['place - Must be greater than or equal to 0.'];
        }

        if (count($errors) > 0) {
            return $this->errorResponse($errors, '#term-errors');
        }

        $this->entityManager->persist($newPeriod);
        $this->entityManager->flush();

        // Regenerate the entire board.
        return $this->redirectToRoute('history_board', [
                'id' => $parentHistory->getId(),
            ]
        );
    }
}