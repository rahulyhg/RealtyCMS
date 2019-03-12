<?php

namespace AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LayoutsFieldsValuessType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', 'textarea', array('label'  => 'Значения (через ;)'))
            ->getForm();

    }

    public function getName()
    {
        return 'layouts_fields_values';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SiteBundle\Model\LayoutsFieldsValues',
        ));
    }

}
