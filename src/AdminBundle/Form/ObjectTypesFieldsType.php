<?php

namespace AdminBundle\Form;

use SiteBundle\Model\ObjectTypesQuery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectTypesFieldsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $mask_array = array(
            "'mask': '9999'" => 'год',
            "'alias': 'numeric'" => 'число',
            "'mask': '9{1,2}/9{1,2}'" => '99/99',
            "'mask': '9{1,3}/9{1,3}/9{1,3}'" => '999/999/999'
        );

        $builder
            ->add('name', 'text', array('label'  => 'Название поля'))
            ->add('filter_name', 'text', array('label'  => 'Короткое название', 'required' => false))
            ->add('object_type_id', 'hidden')
            ->add('type', 'choice', array(
                'choices'   => array('1'=>'Текстовое поле', '2'=>'Список', '3'=>'Числовое поле', '4'=>'Числовой список', '5'=>'Флажок'),
                'label'  => 'Тип поля',
                'attr' => array('class' => 'form-control'),
            ))
            ->add('postfix', 'text', array('label'  => 'Постфикс', 'required' => false))
            ->add('mask', 'choice', array(
                'choices'   => $mask_array,
                'label'  => 'Маска поля',
                'required' => false,
                'attr' => array('class' => 'form-control'),
            ))
            ->add('required', 'checkbox', array('label'  => 'Обязательное поле', 'required' => false, 'data' => ($options['data']->getRequired() == 1) ? true : false))
            ->add('show_in_filter', 'checkbox', array('label'  => 'Показывать в фильтре', 'required' => false, 'data' => ($options['data']->getShowInFilter() == 1) ? true : false))
            ->add('show_in_table', 'checkbox', array('label'  => 'Показывать в плашке', 'required' => false, 'data' => ($options['data']->getShowInTable() == 1) ? true : false))
            //->add('listing', 'checkbox', array('label'  => 'Списком под фильтром', 'required' => false, 'data' => ($options['data']->getListing() == 1) ? true : false))
            //->add('show_on_map', 'checkbox', array('label'  => 'Указывать на карте', 'required' => false, 'data' => ($options['data']->getShowOnMap() == 1) ? true : false))
            //->add('save', 'submit', array('label'  => 'Сохранить',))
            ->getForm();

    }

    public function getName()
    {
        return 'object_types_fields';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SiteBundle\Model\ObjectTypesFields',
        ));
    }

}
