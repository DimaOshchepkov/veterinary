<?php

namespace App\Controller;

use App\Dto\AppointmentListQuery;
use App\Dto\AppointmentRequest;
use App\Entity\Appointment;
use App\Exception\AppointmentCannotBeCancelledException;
use App\Exception\NoPetsException;
use App\Repository\AppointmentRepository;
use App\Security\Voter\AppointmentVoter;
use App\Services\AppointmentCanceller;
use App\Services\AppointmentFormDataProvider;
use App\Services\AppointmentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/appointments')]
#[IsGranted('ROLE_USER')]
final class AppointmentController extends AbstractController
{
    private const array LIMITS = [10, 20, 50, 100];

    public function __construct(
        private readonly AppointmentRepository $appointmentRepo,
        private readonly AppointmentService $appointmentService,
        private readonly AppointmentCanceller $appointmentCanceller,
        private readonly AppointmentFormDataProvider $formDataProvider,
    ) {}

    #[Route('', name: 'app_appointment_create', methods: ['POST'])]
    public function create(
        #[MapRequestPayload(
            validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY,
        )]
        AppointmentRequest $dto,
    ): JsonResponse {
        $appointment = $this->appointmentService->build($dto, $this->getUser());
        $this->appointmentRepo->save($appointment);

        return $this->json([
            'success' => true,
            'redirectUrl' => $this->generateUrl('app_appointment_index'),
        ], 201);
    }

    #[Route('', name: 'app_appointment_index', methods: ['GET'])]
    public function index(
        #[MapQueryString] AppointmentListQuery $query = new AppointmentListQuery(),
    ): Response {
        $owner = $this->getUser();
        $appointments = $this->appointmentRepo->paginateByOwner($owner, $query->isActive(), $query->page, $query->limit);

        return $this->render('appointment/index.html.twig', [
            'appointments' => $appointments,
            'query' => $query,
            'hasAny' => $appointments->getNbResults() > 0 || $this->appointmentRepo->ownerHasAny($owner),
        ]);
    }

    #[Route('/new', name: 'app_appointment_new', methods: ['GET'])]
    public function new(): Response
    {
        try {
            $data = $this->formDataProvider->forUser($this->getUser());
        } catch (NoPetsException $e) {
            $this->addFlash('warning', $e->getMessage());

            return $this->redirectToRoute('app_pet_new');
        }

        return $this->render('appointment/new.html.twig', $data);
    }

    #[Route('/{id}/cancel', name: 'app_appointment_cancel', methods: ['POST'])]
    #[IsCsrfTokenValid('submit')]
    #[IsGranted(AppointmentVoter::CANCEL, subject: 'appointment', statusCode: 404)]
    public function cancel(
        Appointment $appointment,
        #[MapQueryString] AppointmentListQuery $query = new AppointmentListQuery(),
    ): Response {
        try {
            $this->appointmentCanceller->cancel($appointment);
            $this->addFlash('success', 'Заявка отменена.');
        } catch (AppointmentCannotBeCancelledException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_appointment_index', $query->toRouteParams());
    }
}
