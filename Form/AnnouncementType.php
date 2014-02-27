<?php

namespace AHS\AdvertsPluginBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AnnouncementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', null, array('error_bubbling' => true));
        $builder->add('description', 'text', array('error_bubbling' => true));
        $builder->add('category', 'entity', array(
            'error_bubbling' => true,
            'class' => 'AHS\AdvertsPluginBundle\Entity\Category',
            'property' => 'name',
        ));
        $builder->add('price', null, array(
            'error_bubbling' => true,
            'invalid_message' => 'Cena musi być liczbą'
        ));
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

    public function getName()
    {
        return 'announcement';
    }
}