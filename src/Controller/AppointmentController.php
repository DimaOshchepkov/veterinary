<?php

namespace App\Controller;

use App\Dto\AppointmentRequest;
use App\Entity\Appointment;
use App\Enum\AppointmentLocation;
use App\Repository\AppointmentRepository;
use App\Repository\PetRepository;
use App\Repository\VeterinaryServiceRepository;
use App\Services\AppointmentService;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/appointments')]
#[IsGranted('ROLE_USER')]
final class AppointmentController extends AbstractController
{
    private const array LIMITS = [10, 20, 50, 100];


    #[Route('', name: 'app_appointment_create', methods: ['POST'])]
    public function create(
        Request $request,
        ValidatorInterface $validator,
        AppointmentService $appointmentService,
        UrlGeneratorInterface $urlGenerator,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true) ?? [];

        $dto = new AppointmentRequest();
        $dto->pet = $data['pet'] ?? null;
        $dto->service = $data['service'] ?? null;
        $dto->location = $data['location'] ?? null;
        $dto->address = $data['address'] ?? null;

        try {
            $dto->date = isset($data['date']) ? new \DateTimeImmutable($data['date']) : null;
            $dto->time = isset($data['time']) ? new \DateTimeImmutable($data['time']) : null;
        } catch (\Exception) {
            return $this->json([
                'violations' => [['propertyPath' => 'date', 'title' => 'Неверный формат даты']]
            ], 422);
        }

        $dtoErrors = $validator->validate($dto);
        if (count($dtoErrors) > 0) {
            $violations = [];
            foreach ($dtoErrors as $error) {
                $violations[] = [
                    'propertyPath' => $error->getPropertyPath(),
                    'title' => $error->getMessage(),
                ];
            }
            return $this->json(['violations' => $violations], 422);
        }

        try {
            $appointment = $appointmentService->createFromRequest($dto, $this->getUser());
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'violations' => [['propertyPath' => 'root', 'title' => $e->getMessage()]]
            ], 422);
        }

        $violations = $appointmentService->validate($appointment);
        if ($violations) {
            return $this->json(['violations' => $violations], 422);
        }

        $appointmentService->save($appointment);

        $redirectUrl = $urlGenerator->generate('app_appointment_index');

        return $this->json([
            'success' => true,
            'redirectUrl' => $redirectUrl,
        ], 201);
    }

    #[Route('', name: 'app_appointment_index', methods: ['GET'])]
    public function index(
        Request $request,
        AppointmentRepository $repo,
    ): Response {
        $active = 'inactive' !== $request->query->getString('status', 'active');
        $limit = $request->query->getInt('limit', 10);
        $limit = \in_array($limit, self::LIMITS, true) ? $limit : 10;
        $page = max(1, $request->query->getInt('page', 1));

        $queryBuilder = $repo->findByOwnerQueryBuilder($this->getUser(), $active);

        $adapter = new QueryAdapter($queryBuilder, fetchJoinCollection: false, useOutputWalkers: false);
        $appointments = new Pagerfanta($adapter);

        $appointments->setMaxPerPage($limit);
        $appointments->setCurrentPage($page);

        return $this->render('appointment/index.html.twig', [
            'appointments' => $appointments,
            'active' => $active,
            'limit' => $limit,
            'limits' => self::LIMITS,
            'hasAny' => $appointments->getNbResults() > 0
                || $repo->count(['owner' => $this->getUser()]) > 0,
        ]);
    }

    #[Route('/new', name: 'app_appointment_new', methods: ['GET'])]
    public function new(
        PetRepository $petRepo,
        VeterinaryServiceRepository $serviceRepo,
    ): Response {
        $pets = $petRepo->findBy(['owner' => $this->getUser()]);

        if (empty($pets)) {
            $this->addFlash('warning', 'Сначала добавьте хотя бы одного питомца.');
            return $this->redirectToRoute('app_pet_new');
        }

        $petsData = array_map(fn($pet) => [
            'id' => $pet->getId(),
            'label' => $pet->getSpeciesLabel() . ' ' . $pet->getName(),
        ], $pets);

        $servicesData = array_map(fn($service) => [
            'id' => $service->getId(),
            'label' => $service->getName(),
        ], $serviceRepo->findAll());

        return $this->render('appointment/new.html.twig', [
            'pets' => $petsData,
            'services' => $servicesData,
        ]);
    }

    #[Route('/{id}/cancel', name: 'app_appointment_cancel', methods: ['POST'])]
    #[IsCsrfTokenValid('submit')]
    public function cancel(Appointment $appointment, Request $request, EntityManagerInterface $em): Response
    {
        if ($appointment->getOwner() !== $this->getUser()) {
            throw $this->createNotFoundException();
        }

        try {
            $appointment->cancel();
            $em->flush();
            $this->addFlash('success', 'Заявка отменена.');
        } catch (\LogicException) {
            $this->addFlash('error', 'Эту заявку уже нельзя отменить.');
        }

        return $this->redirectToRoute('app_appointment_index',
            array_intersect_key($request->query->all(), array_flip(['status', 'limit', 'page'])));
    }
}
