<?php

namespace App\Form\Profile;

use App\Entity\User;
use App\Form\Security\RegistrationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    public function getParent(): string
    {
        return RegistrationType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->remove('password');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'profile',
        ]);
    }
}
