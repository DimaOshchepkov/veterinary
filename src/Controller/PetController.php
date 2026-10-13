<?php

namespace App\Controller;

use App\Entity\Pet;
use App\Form\PetType;
use App\Repository\PetRepository;
use App\Security\Voter\PetVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/pets')]
#[IsGranted('ROLE_USER')]
final class PetController extends AbstractController
{
    public function __construct(
        private readonly PetRepository $petRepository,
    ) {}

    #[Route('/', name: 'app_pet_index', methods: ['GET'])]
    public function index(): Response
    {
        $pets = $this->petRepository->findByOwnerOrdered($this->getUser());

        return $this->render('pet/index.html.twig', [
            'pets' => $pets,
        ]);
    }

    #[Route('/new', name: 'app_pet_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $pet = new Pet();
        $pet->setOwner($this->getUser());

        $form = $this->createForm(PetType::class, $pet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->petRepository->save($pet, flush: true);

            $this->addFlash('success', 'Питомец успешно добавлен.');
            return $this->redirectToRoute('app_pet_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pet/form.html.twig', [
            'form' => $form,
            'action' => 'new',
        ]);
    }

    #[Route('/{id}/edit', name: 'app_pet_edit', methods: ['GET', 'POST'])]

    #[IsGranted(PetVoter::EDIT, subject: 'pet', statusCode: 404)]
    public function edit(Request $request, Pet $pet): Response
    {
        $form = $this->createForm(PetType::class, $pet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->petRepository->save($pet, flush: true);

            $this->addFlash('success', 'Данные питомца успешно обновлены.');
            return $this->redirectToRoute('app_pet_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pet/form.html.twig', [
            'form' => $form,
            'action' => 'edit',
        ]);
    }

    #[Route('/{id}', name: 'app_pet_delete', methods: ['POST', 'DELETE'])]
    #[IsCsrfTokenValid('submit')]
    #[IsGranted(PetVoter::DELETE, subject: 'pet', statusCode: 404)]
    public function delete(Pet $pet): Response
    {
        // Репозиторий отвечает за удаление
        $this->petRepository->remove($pet, flush: true);

        $this->addFlash('success', 'Питомец успешно удален.');

        return $this->redirectToRoute('app_pet_index', [], Response::HTTP_SEE_OTHER);
    }
}
