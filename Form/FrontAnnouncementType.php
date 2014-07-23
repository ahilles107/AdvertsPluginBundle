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
 * @author Paweł Mikołajczuk <mikolajczuk.private@gmail.com>
 */

namespace AHS\AdvertsPluginBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Announcement form type
 */
class FrontAnnouncementType extends AbstractType
{
    /**
     * Options
     * @var array
     */
    protected $options;

    /**
     * Construct
     * @param array $options Form options
     */
    public function __construct(array $options = array())
    {
        $this->options = $options;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = null;
        if (!empty($this->options)) {
            $translator = $this->options['translator'];
        } else {
            $translator = $options['translator'];
        }

        $builder
            ->add('name', null, array(
                'error_bubbling' => true,
                'label' => $translator->trans('ads.label.name'),
                'constraints' => array(new Assert\NotBlank(array('message' => $translator->trans('ads.error.name'))))
            ))
            ->add('description', 'textarea', array(
                'error_bubbling' => true,
                'label' => $translator->trans('ads.label.description'),
                'constraints' => array(new Assert\NotBlank(array('message' => $translator->trans('ads.error.description'))))
            ))
            ->add('category', 'entity', array(
                'error_bubbling' => true,
                'label' => $translator->trans('ads.label.category'),
                'class' => 'AHS\AdvertsPluginBundle\Entity\Category',
                'property' => 'name',
            ))
            ->add('price', null, array(
                'error_bubbling' => true,
                'constraints' => array(
                    new Assert\NotBlank(array(
                        'message' => $translator->trans('ads.error.price.empty')
                    )),
                    new Assert\Range(array(
                        'min' => 0,
                        'minMessage' => $translator->trans('ads.error.price.range', array('{{ limit }}')),
                    )),
                    new Assert\Type(array(
                        'type' => "float",
                        'message' => $translator->trans('ads.error.price.type'),
                    ))
                )
            ))
            ->add('type', 'choice', array(
                'choices' => array(
                    '1'   => 'Oferuje',
                    '2' => 'Szukam'
                ),
                'error_bubbling' => true,
            ))
            ->add('terms_accepted', 'checkbox', array(
                'error_bubbling' => true,
                'constraints' => array(new Assert\True(array(
                    'message' => $translator->trans('ads.error.termsaccepted'
                ))))
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

        $resolver->setOptional(array(
            'translator',
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
