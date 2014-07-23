<?php

/*
 * This file is part of the Adverts Plugin.
 *
 * (c) Paweł Mikołajczuk <mikolajczuk.private@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package AHS\AdvertsPluginBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 */

namespace AHS\AdvertsPluginBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Settings form type
 */
class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enableNotify', 'checkbox', array(
                'error_bubbling' => true,
                'required' => false
            ))
            ->add('notificationEmail', 'email', array(
                'error_bubbling' => true,
                'required' => false
            ))
            ->add('review', 'checkbox', array(
                'error_bubbling' => true,
                'required' => false
            ))
            ->add('valid_time', 'number', array(
                'error_bubbling' => true,
                'required' => false,
                'precision' => 0
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => true,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'settings';
    }
}
