<?php

namespace AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LayoutsFieldsValuesType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', 'text', array('label'  => 'Значение'))
            ->add('sort', 'text', array('label'  => 'Порядок', 'required' => false))
            ->getForm();

    }

    public function getName()
    {
        return 'layuots_fields_values';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SiteBundle\Model\LayoutsFieldsValues',
        ));
    }

}
