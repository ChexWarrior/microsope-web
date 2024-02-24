<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Period;
use App\Repository\EventRepository;
use App\Repository\PeriodRepository;
use App\Repository\PlayerRepository;
use App\Service\HtmlFormatter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EventController extends TermController
{
    public function __construct(
        private PeriodRepository $periodRepository,
        private EventRepository $eventRepository,
        private EntityManagerInterface $entityManager,
        PlayerRepository $playerRepository,
        ValidatorInterface $validator
    ) {
        parent::__construct($playerRepository, $validator);
    }

    #[Route('/event/{id}', name: 'event', methods: 'GET')]
    public function getEvent(
        Event $event,
        #[MapQueryParameter(filter: \FILTER_VALIDATE_BOOL)] bool $showScenes
    ): Response {
        return $this->render('event/container.html.twig', [
            'hideTermForm' => true,
            'event' => $event,
            'numScenes' => $event->getScenes()->count(),
            'scenes' => $showScenes ? $event->getScenes() : [],
        ]);
    }

    /**
     * Renders the form for editing an event.
     *
     * @param Event $event - The event to edit.
     */
    #[Route('/event/{id}/edit-form', name: 'edit_form_event', methods: 'GET')]
    public function editForm(Event $event): Response {
        $period = $event->getPeriod();
        try {
            $lastPlace = $this->eventRepository->findLastPlace($period);
        } catch (NoResultException $e) {
            $lastPlace = -1;
        }

        $players = $this->playerRepository->findAllByActiveAndHistory($period->getHistory());
        $title = "Edit Event: " . ($event->getPlace() + 1);
        $htmxAttrs = [
            'hx-post' => "/event/{$event->getId()}/edit",
            'hx-swap' => 'outerHTML',
            'hx-target' => "#period-{$period->getId()}",
        ];

        return $this->render('history/term-form.html.twig', [
            'title' => $title,
            'term' => $event,
            'lastPlace' => $lastPlace,
            'players' => $players,
            'parentId' => $period->getId(),
            'htmx_attrs' => HtmlFormatter::formatAsAttributes($htmxAttrs),
        ]);
    }

    /**
     * Renders form for adding a new event.
     *
     * @param Period $period - The parent period of this event.
     * @param int $defaultPlace - The default place for new event.
     */
    #[Route('/event/{id}/add-form', name: 'add_form_event', methods: 'GET')]
    public function addForm(
        Period $period,
        #[MapQueryParameter(name:"place")] int $defaultPlace = 0
    ): Response {
        try {
            $lastPlace = $this->eventRepository->findLastPlace($period);
        } catch (NoResultException $e) {
            $lastPlace = -1;
        }

        $players = $this->playerRepository->findAllByActiveAndHistory($period->getHistory());
        $title = "Add New Event";
        $term = [
            'description' => '',
            'place' => $defaultPlace,
            'tone' => ['value' => ''],
            'createdBy' => ['id' => -1],
        ];
        $htmxAttrs = [
            'hx-post' => "/event/add",
            'hx-swap' => 'outerHTML',
            'hx-target' => "#period-{$period->getId()}",
        ];

        return $this->render('history/term-form.html.twig', [
            'title' => $title,
            'term' => $term,
            'lastPlace' => $lastPlace + 1,
            'players' => $players,
            'parentId' => $period->getId(),
            'htmx_attrs' => HtmlFormatter::formatAsAttributes($htmxAttrs),
        ]);
    }

    #[Route('/event/{id}/edit', name: 'edit_event', methods: 'POST')]
    public function editEvent(Event $event, Request $request): Response {
        $termParams = $this->parseTermParameters($request);
        $parentPeriod = $event->getPeriod();

        try {
            $editedEvent = $this->editTerm($event, $termParams, $this->eventRepository, $parentPeriod);
            $errors = $this->validateTerm($editedEvent);
        } catch(NoResultException) {
            // We failed to find a term with $termParams['place'].
            $errors = ['place - Must be greater than or equal to 0.'];
        }

        if (count($errors) > 0) {
            return $this->errorResponse($errors, '#term-errors');
        }

        $this->entityManager->flush();
        return $this->redirectToRoute('period', [
            'id' => $parentPeriod->getId(),
        ]);
    }

    #[Route('/event/add', name: 'add_event', methods: 'POST')]
    public function addEvent(Request $request): Response {
        $termParams = $this->parseTermParameters($request);
        $parentPeriod = $this->periodRepository->find($termParams['parentId']);

        try {
            $newEvent = $this->addTerm(new Event(), $termParams, $this->eventRepository, $parentPeriod);
            $errors = $this->validateTerm($newEvent);
        } catch (NoResultException) {
            $errors = ['place - Must be greater than or equal to 0.'];
        }

        if (count($errors) > 0) {
            return $this->errorResponse($errors, '#term-errors');
        }

        $this->entityManager->persist($newEvent);
        $this->entityManager->flush();
        return $this->redirectToRoute('period', [
            'id' => $parentPeriod->getId(),
        ]);
    }
}
