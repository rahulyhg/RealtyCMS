<?php
/**
 * Created by PhpStorm.
 * User: NNovi
 * Date: 15.03.2016
 * Time: 16:59
 */

namespace AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TownsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $areas = '';
        if ($options['data']->getAreass()) {
            $areas = implode(',',$options['data']->getAreass()->toKeyValue('ID','Title'));
        }

        $builder
            ->add('title', 'text', array('label'  => 'Название нас.пункта', 'required' => TRUE))
            ->add('pagetitle', 'text', array('label'  => 'Написание нас.пункта', 'required' => TRUE))
            ->add('areas', 'textarea', array('label'  => 'Районы/Микрорайоны (через запятую)', 'attr' => array('rows' => 8), 'required' => FALSE, 'mapped'=>FALSE, 'data'=>$areas))
            ->getForm();

    }

    public function getName()
    {
        return 'towns';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SiteBundle\Model\Towns',
        ));
    }

}
