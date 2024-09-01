<?php

namespace App\Controller;

use App\Entity\Player;
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

        return $this->render('player/edit-form.html.twig', [
            'htmx_attrs' => HtmlFormatter::formatAsAttributes($htmxAttrs),
            'player' => $player,
        ]);
    }

    #[Route('/player/{id}/edit', name: 'edit_player', methods: 'POST')]
    public function edit(Player $player, Request $request): Response
    {
        $name = $request->getPayload()->get('name');
        $legacy = $request->getPayload()->get('legacy');
        $isLens = (bool) $request->getPayload()->get('lens', false);
        $isActive = (bool) $request->getPayload()->get('active', false);

        $player->setName($name);
        $player->setLegacy($legacy);
        $player->setLens($isLens);
        $player->setActive($isActive);

        $errors = [];
        foreach ($this->validator->validate($player) as $error) {
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
}
