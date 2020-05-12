<?php

namespace App\Form;

use App\Entity\Discussion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class DiscussionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add( 'sujet', TextType::class, [
                'attr' => ['class' => 'form-control'] ] )
            ->add( 'titre', TextType::class, [
                'attr' => ['class' => 'form-control'] ] )
            ->add( 'message', TextareaType::class, [
                'attr' => array('class' => 'form-control', 'id' => 'message' ) ] )
            ->add( 'creer', SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary' ] ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Discussion::class,
        ]);
    }
}
