<?php

namespace AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectTypesFieldsValuessType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', 'textarea', array('label'  => 'Значения (через ;)'))
            //->add('sort', 'text', array('label'  => 'Порядок', 'required' => false))
            //->add('save', 'submit', array('label'  => 'Сохранить',))
            ->getForm();

    }

    public function getName()
    {
        return 'object_types_fields_values';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SiteBundle\Model\ObjectTypesFieldsValues',
        ));
    }

}
