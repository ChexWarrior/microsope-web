<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Scene;
use App\Repository\EventRepository;
use App\Repository\PlayerRepository;
use App\Repository\SceneRepository;
use App\Service\HtmlFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SceneController extends TermController
{
    public function __construct(
        private SceneRepository $sceneRepository,
        private EventRepository $eventRepository,
        private EntityManagerInterface $entityManager,
        PlayerRepository $playerRepository,
        ValidatorInterface $validator
    )
    {
        parent::__construct($playerRepository, $validator);
    }

    /**
     * Renders form for adding a new scene.
     *
     * @param Event $event - The parent event of this scene.
     * @param int $defaultPlace - The default place for new scene.
     */
    #[Route('/scene/{id}/add-form', name: 'add_form_scene', methods: 'GET')]
    public function addForm(
        Event $event,
        #[MapQueryParameter(name:"place")] int $defaultPlace = 0
    ): Response {
        try {
            $lastPlace = $this->sceneRepository->findLastPlace($event);
        } catch (NoResultException $e) {
            $lastPlace = -1;
        }

        $players = $this->playerRepository->findAllByActiveAndHistory($event->getPeriod()->getHistory());
        $title = "Add New Scene";
        $term = [
            'description' => '',
            'place' => $defaultPlace,
            'tone' => ['value' => ''],
            'createdBy' => ['id' => -1],
        ];
        $htmxAttrs = [
            'hx-post' => '/scene/add',
            'hx-swap' => 'outerHTML',
            'hx-target' => "#event-{$event->getId()}",
        ];

        return $this->render('history/term-form.html.twig', [
            'title' => $title,
            'term' => $term,
            'lastPlace' => $lastPlace + 1,
            'players' => $players,
            'parentId' => $event->getId(),
            'htmx_attrs' => HtmlFormatter::formatAsAttributes($htmxAttrs),
        ]);
    }

    #[Route('/scene/{id}/edit-form', name: 'edit_form_scene', methods: 'GET')]
    public function editForm(Scene $scene): Response {
        $event = $scene->getEvent();
        try {
            $lastPlace = $this->sceneRepository->findLastPlace($event);
        } catch (NoResultException $e) {
            $lastPlace = -1;
        }

        $players = $this->playerRepository->findAllByActiveAndHistory($event->getPeriod()->getHistory());
        $title = 'Edit Scene ' . ($scene->getPlace() + 1);
        $htmxAttrs = [
            'hx-post' => "/scene/{$scene->getId()}/edit",
            'hx-swap' => 'outerHTML',
            'hx-target' => "#event-{$event->getId()}",
        ];

        return $this->render('history/term-form.html.twig', [
            'title' => $title,
            'term' => $scene,
            'lastPlace' => $lastPlace,
            'players' => $players,
            'parentId' => $event->getId(),
            'htmx_attrs' => HtmlFormatter::formatAsAttributes($htmxAttrs),
        ]);
    }

    #[Route('/scene/add', name: 'add_scene', methods: 'POST')]
    public function addScene(Request $request): Response {
        $termParams = $this->parseTermParameters($request);
        $parentEvent = $this->eventRepository->find($termParams['parentId']);

        try {
            $newScene = $this->addTerm(new Scene(), $termParams, $this->sceneRepository, $parentEvent);
            $errors = $this->validateTerm($newScene);
        } catch (NoResultException) {
            $errors = ['place - Must be greater than or equal to 0.'];
        }

        if (count($errors) > 0) {
            return $this->errorResponse($errors, '#term-errors');
        }

        $this->entityManager->persist($newScene);
        $this->entityManager->flush();

        return $this->redirectToRoute('event', [
            'id' => $parentEvent->getId(),
            'showScenes' => true,
        ]);
    }

    #[Route('/scene/{id}/edit', name: 'edit_scene', methods: 'POST')]
    public function editScene(Scene $scene, Request $request): Response {
        $termParams = $this->parseTermParameters($request);
        $parentEvent = $scene->getEvent();

        try {
            $editedScene = $this->editTerm($scene, $termParams, $this->sceneRepository, $parentEvent);
            $errors = $this->validateTerm($editedScene);
        } catch (NoResultException) {
            $errors = ['place - Must be greater than or equal to 0.'];
        }

        if (count($errors) > 0) {
            return $this->errorResponse($errors, '#term-errors');
        }

        $this->entityManager->flush();
        return $this->redirectToRoute('event', [
            'id' => $parentEvent->getId(),
            'showScenes' => true,
        ]);
    }
}
