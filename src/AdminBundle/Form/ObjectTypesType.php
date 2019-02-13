<?php

namespace AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectTypesType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder            
            ->add('title', 'text', array('label'  => 'Название', 'required' => TRUE))
			->add('alias', 'text', array('label'  => 'Алиас', 'required' => TRUE))
			->add('image', 'file', array('label'  => 'Изображение', 'required' => FALSE))
            ->add('save', 'submit', array('label'  => 'Сохранить',))
            ->getForm();

    }

    public function getName()
    {
        return 'object_types';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SiteBundle\Model\ObjectTypes',
        ));
    }

}
