<?php

namespace AHS\AdvertsPluginBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Announcement form type
 */
class AnnouncementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array(
                'error_bubbling' => true
            ))
            ->add('description', 'textarea', array(
                'error_bubbling' => true
            ))
            ->add('category', 'entity', array(
                'error_bubbling' => true,
                'class' => 'AHS\AdvertsPluginBundle\Entity\Category',
                'property' => 'name',
            ))
            ->add('reads', null, array(
                'error_bubbling' => true,
                'required' => false,
            ))
            ->add('publication', 'entity', array(
                'error_bubbling' => true,
                'class' => 'Newscoop\Entity\Publication',
                'property' => 'name',
                'required' => false,
            ))
            ->add('price', null, array(
                'error_bubbling' => true,
                'invalid_message' => 'Cena musi być liczbą'
            ))
            ->add('type', 'choice', array(
                'choices' => array(
                    '1'   => 'Oferuje',
                    '2' => 'Szukam'
                ),
                'error_bubbling' => true,
            ))
            ->add('valid_to', 'datetime', array(
                'error_bubbling' => true,
            ))
            ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection'   => false,
            'data_class' => 'AHS\AdvertsPluginBundle\Entity\Announcement',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'announcement';
    }
}
