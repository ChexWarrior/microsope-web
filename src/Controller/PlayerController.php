<?php

namespace App\Controller;

use App\Entity\Player;
use App\Repository\PlayerRepository;
use App\Service\HtmlFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlayerController extends AbstractController
{
    public function __construct(
        private PlayerRepository $playerRepository
    ){}

    #[Route('/player/{id}/edit-form', name: 'edit_form_player')]
    public function editForm(Player $player): Response
    {
        $htmxAttrs = [
            'hx-post' => "/player/{$player->getId()}/edit",
            // Editing player info will require regenerating board.
            'hx-target' => "#board",
        ];

        return $this->render('player/edit-form.html.twig', [
            'htmx_attrs' => HtmlFormatter::formatAsAttributes($htmxAttrs),
            'player' => $player,
        ]);
    }
}
