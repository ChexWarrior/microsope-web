<?php

namespace App\Controller;

use App\Enum\Tone;
use App\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class TermController extends AbstractController
{
    public function __construct(
        protected PlayerRepository $playerRepository,
        protected ValidatorInterface $validator
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
