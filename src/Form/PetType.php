<?php
namespace App\Form;

use App\Entity\Pet;
use App\Enum\PetGender;
use App\Enum\PetSpecies;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;

class PetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Кличка',
                'attr' => ['maxlength' => 100],
                'constraints' => [
                    new NotBlank(message: 'Поле "Кличка" обязательно для заполнения.'),
                    new Length(max: 100, maxMessage: 'Кличка не может быть длиннее 100 символов.'),
                ],
            ])
            ->add('species', ChoiceType::class, [
                'label' => 'Вид',
                'choices' => [
                    PetSpecies::CAT->label() => PetSpecies::CAT,
                    PetSpecies::DOG->label() => PetSpecies::DOG,
                    PetSpecies::OTHER->label() => PetSpecies::OTHER,
                ],
                'choice_label' => fn($choice) => $choice->label(),
                'placeholder' => 'Выберите вид',
                'constraints' => [new NotBlank(message: 'Поле "Вид" обязательно для заполнения.')],
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'Пол',
                'choices' => [
                    PetGender::MALE->label() => PetGender::MALE,
                    PetGender::FEMALE->label() => PetGender::FEMALE,
                ],
                'choice_label' => fn($choice) => $choice->label(),
                'expanded' => true,
                'constraints' => [new NotBlank(message: 'Поле "Пол" обязательно для заполнения.')],
            ])
            ->add('birthDate', DateType::class, [
                'label' => 'Дата рождения',
                'widget' => 'single_text',
                'html5' => true,
                'constraints' => [
                    new NotBlank(message: 'Поле "Дата рождения" обязательно для заполнения.'),
                    new LessThanOrEqual([
                        'value' => 'today',
                        'message' => 'Дата рождения не может быть в будущем.',
                    ]),
                ],
            ])
            ->add('breed', TextType::class, [
                'label' => 'Порода',
                'attr' => ['maxlength' => 100],
                'constraints' => [
                    new NotBlank(message: 'Поле "Порода" обязательно для заполнения.'),
                    new Length(max: 100, maxMessage: 'Порода не может быть длиннее 100 символов.'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Pet::class,
        ]);
    }
}
