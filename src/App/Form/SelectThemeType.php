<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Theme;
use App\Entity\PublicDiscussion;

class SelectThemeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('theme', EntityType::class, [
                'class' => Theme::class,
                'property' => 'title',
                'required' => true,
            ])
            ->add('save', SubmitType::class)
        ;
    }
}