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
            $areas_array = [];
            foreach($options['data']->getAreass() as $item) {
                $areas_array[] = $item->getTitle();
            }
            $areas = implode(',',$areas_array);
        }

        $builder
            ->add('title', 'text', array('label'  => 'Название', 'required' => TRUE))
            ->add('areas', 'textarea', array('label'  => 'Районы (через запятую)', 'required' => FALSE, 'mapped'=>FALSE, 'data'=>$areas))
            //->add('save', 'submit', array('label'  => 'Сохранить',))
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
