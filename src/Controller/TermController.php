<?php

namespace App\Controller;

use App\Enum\Tone;
use App\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

abstract class TermController extends AbstractController
{
    public function __construct(
        protected PlayerRepository $playerRepository
    ){}
    /**
     * Parses the common properties for terms from an incoming request.
     *
     * @param Request $request
     * @return array - Array of term values
     */
    protected function parseTermParameters(Request $request): array {
        $playerId = $request->getPayload()->get('player');

        return [
            'description' => $request->getPayload()->get('description'),
            'tone' => Tone::tryFrom($request->getPayload()->get('tone')),
            'place' => $request->getPayload()->get('order'),
            'createdBy' => $this->playerRepository->find($playerId),
            'parentId' => $request->getPayload()->get('parent'),
        ];
    }
}
