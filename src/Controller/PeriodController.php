<?php

namespace App\Controller;

use App\Entity\Period;
use App\Enum\Tone;
use App\Repository\HistoryRepository;
use App\Repository\PeriodRepository;
use App\Repository\PlayerRepository;
use App\Service\HtmlFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;

class PeriodController extends AbstractController
{
    public function __construct(
        private PlayerRepository $playerRepository,
        private PeriodRepository $periodRepository,
        private HistoryRepository $historyRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * Creates the form for the first period of a history.
     *
     * @param int $historyId
     */
    #[Route('/period/form', name: 'form_first_period', methods: 'GET')]
    public function firstForm(#[MapQueryParameter] int $parentId): Response {
        $history = $this->historyRepository->find($parentId);

        // Get all active players.
        $players = $this->playerRepository->findAllByActiveAndHistory($history);
        $htmxAttrs = [
            'hx-post' => '/period/add',
            'hx-target' => '#board',
        ];

        return $this->render('history/term-form.html.twig', [
            'title' => 'Add First Period',
            'term' => [
                'description' => '',
                'place' => 0,
                'tone' => ['value' => ''],
                'createdBy' => ['id' => -1],
            ],
            'lastPlace' => 0,
            'players' => $players,
            'parentId' => $history->getId(),
            'htmx_attrs' => HtmlFormatter::formatAsAttributes($htmxAttrs),
        ]);
    }

    #[Route('/period/{id}/form', name: 'form_period', methods: 'GET')]
    public function form(Period $period, #[MapQueryParameter] string $mode): Response
    {
        // Get the value of the last place for a valid period in history.
        $lastPlace = $this->periodRepository->findLastPlaceByHistory($period->getHistory());

        // Get all active players.
        $players = $this->playerRepository->findAllByActiveAndHistory($period->getHistory());

        if ($mode == "edit") {
            $title = "Edit Period: " . ($period->getPlace() + 1);
            $term = $period;
            $htmxAttrs = [
                'hx-post' => "/period/{$period->getId()}/edit",
                'hx-target' => '#board',
            ];
        } else {
            $title = "Add New Period";
            $term = [
                'description' => '',
                'place' => $period->getPlace(),
                'tone' => ['value' => ''],
                'createdBy' => ['id' => -1],
            ];
            $htmxAttrs = [
                'hx-post' => '/period/add',
                'hx-target' => '#board',
            ];

            // Give new card option to be last in order.
            $lastPlace += 1;
        }

        return $this->render('history/term-form.html.twig', [
            'title' => $title,
            'term' => $term,
            'lastPlace' => $lastPlace,
            'players' => $players,
            'parentId' => $period->getHistory()->getId(),
            'htmx_attrs' => HtmlFormatter::formatAsAttributes($htmxAttrs),
        ]);
    }

    #[Route('/period/{id}/edit', name: 'edit_period', methods: 'POST')]
    public function editPeriod(Period $period, Request $request): Response
    {
        // Get params.
        // TODO: Validate request params.
        $description = $request->getPayload()->get('description');
        $tone = $request->getPayload()->get('tone');
        $place = $request->getPayload()->get('order');
        $playerId = $request->getPayload()->get('player');
        $createdBy = $this->playerRepository->find($playerId);

        // Update period.
        $period->setDescription($description);

        if ($period->getPlace() !== $place) {
            $periodToUpdate = $this->periodRepository->findByPlace($place, $period->getHistory());
            $periodToUpdate->setPlace($period->getPlace());
        }

        $period->setPlace($place);
        $period->setTone(Tone::from($tone));
        $period->setCreatedBy($createdBy);
        $this->entityManager->flush();

        // Regenerate the entire board.
        return $this->redirectToRoute('history_board', [
                'id' => $period->getHistory()->getId()
            ]
        );
    }

    #[Route('/period/add', name: 'add_period', methods: 'POST')]
    public function addPeriod(Request $request): Response {
        // Get params.
        // TODO: Validate request params.
        $description = $request->getPayload()->get('description');
        $tone = $request->getPayload()->get('tone');
        $place = $request->getPayload()->get('order');
        $playerId = $request->getPayload()->get('player');
        $historyId = $request->getPayload()->get('parent');

        $createdBy = $this->playerRepository->find($playerId);
        $history = $this->historyRepository->find($historyId);

        try {
            $lastPlace = $this->periodRepository->findLastPlaceByHistory($history);
        } catch (NoResultException $e) {
            $lastPlace = -1;
        }

        // When adding a new card we need to move all after it up one in order.
        if ($place <= $lastPlace) {
            $periodsToUpdate = $this->periodRepository->findAllWithPlaceGreaterThanOrEqual($place, $history);

            /** @var Period $p */
            foreach ($periodsToUpdate as $p) {
                $p->setPlace($p->getPlace() + 1);
            }
        }

        $newPeriod = new Period();
        $newPeriod->setDescription($description);
        $newPeriod->setPlace($place);
        $newPeriod->setTone(Tone::from($tone));
        $newPeriod->setCreatedBy($createdBy);
        $newPeriod->setHistory($history);
        $this->entityManager->persist($newPeriod);
        $this->entityManager->flush();

        // Regenerate the entire board.
        return $this->redirectToRoute('history_board', [
                'id' => $historyId
            ]
        );
    }
}