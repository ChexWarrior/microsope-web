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
            $lastPlace = $this->sceneRepository->findLastPlaceByEvent($event);
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
            $lastPlace = $this->sceneRepository->findLastPlaceByEvent($event);
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
        $event = $this->eventRepository->find($termParams['parentId']);

        try {
            $lastPlace = $this->sceneRepository->findLastPlaceByEvent($event);
        } catch (NoResultException $e) {
            $lastPlace = -1;
        }

        if ($termParams['place'] <= $lastPlace) {
            $scenesToUpdate = $this->sceneRepository
                ->findAllWithPlaceGreaterThanOrEqual($termParams['place'], $event);
            /** @var Scene $s */
            foreach ($scenesToUpdate as $s) {
                $s->setPlace($s->getPlace() + 1);
            }
        }

        $newScene = new Scene();
        $newScene->setDescription($termParams['description']);
        $newScene->setPlace($termParams['place']);
        $newScene->setTone($termParams['tone']);
        $newScene->setCreatedBy($termParams['createdBy']);
        $newScene->setEvent($event);
        $this->entityManager->persist($newScene);
        $this->entityManager->flush();

        return $this->redirectToRoute('event', [
            'id' => $event->getId(),
            'showScenes' => true,
        ]);
    }

    #[Route('/scene/{id}/edit', name: 'edit_scene', methods: 'POST')]
    public function editScene(Scene $scene, Request $request): Response {
        $termParams = $this->parseTermParameters($request);
        $event = $scene->getEvent();
        if  ($scene->getPlace() !== $termParams['place']) {
            $sceneToUpdate = $this->sceneRepository->findByPlace($termParams['place'], $event);
            $sceneToUpdate->setPlace($scene->getPlace());
        }

        $scene->setPlace($termParams['place']);
        $scene->setDescription($termParams['description']);
        $scene->setTone($termParams['tone']);
        $scene->setCreatedBy($termParams['createdBy']);
        $this->entityManager->flush();

        return $this->redirectToRoute('event', [
            'id' => $event->getId(),
            'showScenes' => true,
        ]);
    }
}
