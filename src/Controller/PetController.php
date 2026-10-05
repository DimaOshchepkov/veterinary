<?php
namespace App\Controller;

use App\Entity\Pet;
use App\Form\PetType;
use App\Repository\PetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/pets')]
#[IsGranted('ROLE_USER')]
class PetController extends AbstractController
{
    #[Route('/', name: 'app_pet_index', methods: ['GET'])]
    public function index(PetRepository $petRepository): Response
    {
        $pets = $petRepository->findBy(['owner' => $this->getUser()], ['id' => 'ASC']);

        return $this->render('pet/index.html.twig', [
            'pets' => $pets,
        ]);
    }

    #[Route('/new', name: 'app_pet_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $pet = new Pet();
        $pet->setOwner($this->getUser());

        $form = $this->createForm(PetType::class, $pet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($pet);
            $entityManager->flush();

            $this->addFlash('success', 'Питомец успешно добавлен.');
            return $this->redirectToRoute('app_pet_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pet/form.html.twig', [
            'pet' => $pet,
            'form' => $form,
            'action' => 'new',
        ]);
    }

    #[Route('/{id}/edit', name: 'app_pet_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Pet $pet, EntityManagerInterface $entityManager): Response
    {
        // Проверка: пользователь может изменять только своих питомцев
        if ($pet->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Вы можете редактировать только своих питомцев.');
        }

        $form = $this->createForm(PetType::class, $pet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Данные питомца успешно обновлены.');
            return $this->redirectToRoute('app_pet_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pet/form.html.twig', [
            'pet' => $pet,
            'form' => $form,
            'action' => 'edit',
        ]);
    }

    #[Route('/{id}', name: 'app_pet_delete', methods: ['POST', 'DELETE'])]
    #[IsCsrfTokenValid('submit')]
    public function delete(Request $request, Pet $pet, EntityManagerInterface $entityManager): Response
    {
        if ($pet->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Вы можете удалять только своих питомцев.');
        }

        $entityManager->remove($pet);
        $entityManager->flush();
        $this->addFlash('success', 'Питомец успешно удален.');

        return $this->redirectToRoute('app_pet_index', [], Response::HTTP_SEE_OTHER);
    }
}
