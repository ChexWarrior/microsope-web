<?php

namespace App\Controller;

use App\Entity\History;
use App\Entity\Term;
use App\Enum\Tone;
use App\Repository\PlayerRepository;
use App\Repository\TermRepositoryInterface;
use Doctrine\ORM\NoResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class TermController extends AbstractController
{
    public function __construct(
        protected PlayerRepository $playerRepository,
        protected ValidatorInterface $validator
    ){}

    public function getLastPlaceForTerm(History|Term $parent, TermRepositoryInterface $repo): int {
        try {
            return $repo->findLastPlace($parent);
        } catch (NoResultException) {
            return -1;
        }
    }

    public function getAllActivePlayers(History $history): array {
        return $this->playerRepository->findAllByActiveAndHistory($history);
    }

    /**
     * Update a term with $editData.
     *
     * Does not perform a db save, this is the job of the calling controller.
     */
    protected function editTerm(
        Term $term,
        array $editData,
        TermRepositoryInterface $repo,
        History|Term $parent
    ): Term {
        // If we changed the term's place swap it with whatever term was in that place.
        if ($term->getPlace() !== $editData['place']) {
            $swapTerm = $repo->findByPlace($editData['place'], $parent);
            $swapTerm->setPlace($term->getPlace());
        }

        $term->setPlace($editData['place']);
        $term->setDescription($editData['description']);
        $term->setTone($editData['tone']);
        $term->setCreatedBy($editData['createdBy']);

        return $term;
    }

    protected function addTerm(
        Term $newTerm,
        array $addData,
        TermRepositoryInterface $repo,
        History|Term $parent
    ): Term {
        // Find the greatest place value this term could have.
        try {
            $lastPlace = $repo->findLastPlace($parent);
        } catch (NoResultException) {
            // This is the first term in the parent.
            $lastPlace = -1;
        }

        // If this new term will be in the same place as an existing move the existing
        // and all terms after it up one.
        if ($addData['place'] <= $lastPlace) {
            $termsToSwap = $repo
                ->findAllWithPlaceGreaterThanOrEqual($addData['place'], $parent);
            /** @var Term $swap */
            foreach ($termsToSwap as $swap) {
                $swap->setPlace($swap->getPlace() + 1);
            }
        }

        $newTerm->setDescription($addData['description']);
        $newTerm->setPlace($addData['place']);
        $newTerm->setTone($addData['tone']);
        $newTerm->setCreatedBy($addData['createdBy']);
        $newTerm->setParent($parent);

        return $newTerm;
    }

    /**
     * Validates term with validator service.
     *
     * @param Term $term
     * @return string[] - An array of error messages, if empty then term is valid.
     */
    protected function validateTerm(Term $term): array {
        $errorMsgs = [];
        foreach ($this->validator->validate($term) as $error) {
            $errorMsgs[] = "{$error->getPropertyPath()} - {$error->getMessage()}";
        }

        return $errorMsgs;
    }

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

    /**
     * Return validaton errors to client.
     *
     * @param string[] $errors - List of errors to return to client.
     * @param string $target - CSS selector to return errors on client.
     */
    protected function errorResponse(array $errors, string $target): Response {
        return $this->render('common/errors.html.twig', [
            'errors' => $errors,
        ], new Response('', Response::HTTP_BAD_REQUEST, [
            'HX-Retarget' => $target,
            'HX-Reswap' => 'outerHTML',
        ]));
    }
}
