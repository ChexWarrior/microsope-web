<?php

namespace App\Controller;

use App\Entity\History;
use App\Entity\Player;
use App\Repository\HistoryRepository;
use App\Repository\PlayerRepository;
use App\Service\HtmlFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PlayerController extends AbstractController
{
    public function __construct(
        private PlayerRepository $playerRepository,
        private HistoryRepository $historyRepository,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
    ){}

    #[Route('/player/{id}/edit-form', name: 'edit_form_player', methods: 'GET')]
    public function editForm(Player $player): Response
    {
        $htmxAttrs = [
            'hx-post' => "/player/{$player->getId()}/edit",
            // Editing player info will require regenerating page.
            'hx-target' => "body",
        ];

        return $this->render('player/add-edit-form.html.twig', [
            'htmx_attrs' => HtmlFormatter::formatAsAttributes($htmxAttrs),
            'player' => $player,
            'title' => "Edit Player {$player->getName()}",
        ]);
    }

    #[Route('/player/add-form', name: 'add_form_player', methods: 'GET')]
    public function addForm(Request $request): Response
    {
        $history_id = $request->query->get('history');
        $htmxAttrs = [
            'hx-post' => "/player/add",
            'hx-target' => ".players-list",
            'hx-swap' => 'outerHTML',
        ];

        return $this->render('player/add-edit-form.html.twig', [
            'htmx_attrs' => HtmlFormatter::formatAsAttributes($htmxAttrs),
            'history_id' => $history_id,
            'player' => [
                'name' => '',
                'legacy' => '',
                'active' => true,
                'lens' => false,
            ],
            'title' => "Add New Player",
        ]);
    }

    #[Route('/player/add', name: 'add_player', methods: 'POST')]
    public function add(Request $request): Response
    {
        $errors = [];
        $info = $this->extractRequestInfo($request, null);
        $history = $this->historyRepository->findOneBy(['id' => $info['history_id']]);
        $newPlayer = new Player(
            $info['name'],
            $info['history'],
            $info['isActive'],
            $info['legacy'],
            $info['isLens']
        );

        if (empty($history)) {
            $errors[] = "history - No valid history found.";
        }

        $errors = array_merge($errors, $this->checkErrors($newPlayer));
        if (count($errors) > 0) {
           return $this->errorResponse($errors);
        }

        $players = $this->playerRepository->findAllByActiveAndHistory($history);
        if ($newPlayer->isLens()) {
            $this->updateCurrentLens($players);
        }

        $this->entityManager->persist($newPlayer);
        $this->entityManager->flush();
        return $this->render('history/players.html.twig', [
            'players' => [...$players, $newPlayer],
            'hideForm' => true,
        ]);
    }

    #[Route('/player/{id}/edit', name: 'edit_player', methods: 'POST')]
    public function edit(Player $player, Request $request): Response
    {

        $info = $this->extractRequestInfo($request, $player);
        $player->setName($info['name']);
        $player->setLegacy($info['legacy']);
        $player->setLens($info['isLens']);
        $player->setActive($info['isActive']);

        $errors = $this->checkErrors($player);
        if (count($errors) > 0) {
            return $this->errorResponse($errors);
        }

        // If player is set as lens ensure other players are unset.
        if ($player->isLens()) {
            $players = $this->playerRepository->findAllByActiveAndHistory($player->getHistory());
            $this->updateCurrentLens($players);
        }

        $this->entityManager->flush();
        return $this->redirectToRoute('app_history', [
                'id' => $player->getHistory()->getId(),
            ]
        );
    }

    #[Route('/player/form/hide', name: 'hide_player_form', methods: 'GET')]
    public function hideInfoForm(): Response
    {
        return new Response(
            '<div id="player-dialog" class="backdrop hidden"></div>'
        );
    }

    public function extractRequestInfo(Request $request, ?Player $player): array {
        $name = $request->getPayload()->get('name');
        $legacy = $request->getPayload()->get('legacy');
        $isLens = (bool) $request->getPayload()->get('lens', false);
        $isActive = (bool) $request->getPayload()->get('active', false);
        $history_id = $request->getPayload()->get('history', null);
        $history = null;

        if (!empty($player)) {
            $history = $player->getHistory();
        }

        return [
            'name' => $name,
            'legacy' => $legacy,
            'isLens' => $isLens,
            'isActive' => $isActive,
            'history_id' => $history_id,
            'history' => $history,
        ];
    }

    public function checkErrors(Player $player): array {
        $errors = [];
        foreach ($this->validator->validate($player) as $error) {
            $errors[] = "{$error->getPropertyPath()} - {$error->getMessage()}";
        }

        return $errors;
    }

    public function errorResponse($errors): Response {
        return $this->render('common/errors.html.twig', [
            'errors' => $errors,
        ], new Response('', Response::HTTP_BAD_REQUEST, [
            'HX-Retarget' => '.form-errors',
            'HX-Reswap' => 'outerHTML',
        ]));
    }

    public function updateCurrentLens(array $players): array {
        foreach ($players as $p) {
            $p->setLens(false);
        }

        return $players;
    }
}
