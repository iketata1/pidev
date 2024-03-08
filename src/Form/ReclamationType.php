<?php

namespace App\Form;

use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('iduser')
            ->add('description')
            ->add('date')
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'transactions non autorisées ou frauduleuses' => 'transactions non autorisées ou frauduleuses',
                    'problèmes liés aux opérations bancaires' => 'problèmes liés aux opérations bancaires',
                    'problémes liés aux cartes' => 'problémes liés aux cartes',
                    'frais et facturation' => 'frais et facturation',
                    'gestion de compte' => 'gestion de compte',
                    'problémes techniques' => 'problémes techniques'
                    

                ],
                'placeholder' => 'Choose a type',
                'required' => true
            ])
            ->add('etat')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
        ]);
    }
}
