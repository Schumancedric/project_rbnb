<?php
// phpcs:disable
namespace App\Form;

use App\Entity\Annonces;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnoncesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('adresse')
            ->add('tel')
            ->add('url')
            ->add('prix')
            ->add('image1', FileType::class, array('data_class' => null))
            ->add('image2', FileType::class, array('data_class' => null))
            ->add('image3', FileType::class, array('data_class' => null))
            ->add('image4', FileType::class, array('data_class' => null));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Annonces::class,
        ]);
    }
}
