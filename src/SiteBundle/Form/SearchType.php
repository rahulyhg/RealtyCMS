<?php
/**
 * Created by PhpStorm.
 * User: NNovi
 * Date: 15.03.2016
 * Time: 16:59
 */

namespace SiteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use SiteBundle\Model\TownsQuery;
use SiteBundle\Model\AreasQuery;
use SiteBundle\Model\ObjectTypesQuery;

class SearchType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $towns = TownsQuery::create()
            ->find();
        $array_towns = [];
        $first_town = NULL;
        foreach ($towns as $town) {
            if (!$first_town) $first_town = $town->getId();
            $array_towns[$town->getId()] = $town->getTitle();
        }

        $array_areas = [];

        if (@$options['data']) {
            $areas = AreasQuery::create()
                ->filterByTownId($options['data'])
                ->orderByTitle()
                ->find();
            foreach ($areas as $area) {
                $array_areas[$area->getId()] = $area->getTitle();
            }
        }/* elseif ($first_town) {
            $areas = AreasQuery::create()
                ->filterByTownId($first_town)
                ->find();
            foreach ($areas as $area) {
                $array_areas[$area->getId()] = $area->getTitle();
            }
        }*/
        $array_types = array(
            1 => 'Продажа',
            2 => 'Аренда'
        );
		$array_period = array(
            '1_2018' => 'I кв. 2018',
            '2_2018' => 'II кв. 2018',
			'3_2018' => 'III кв. 2018',
			'4_2018' => 'IV кв. 2018',
			'1_2019' => 'I кв. 2019',
            '2_2019' => 'II кв. 2019',
			'3_2019' => 'III кв. 2019',
			'4_2019' => 'IV кв. 2019',
			'1_2020' => 'I кв. 2020',
            '2_2020' => 'II кв. 2020',
			'3_2020' => 'III кв. 2020',
			'4_2020' => 'IV кв. 2020'
        );
        $array_object_types = ObjectTypesQuery::create()
            ->find()->toKeyValue('id','title');
        
		$builder
            ->add('agent_id', 'hidden', array(            
                'required'  => FALSE
            ))
            ->add('type_object', 'choice', array(
                'choices'   => $array_object_types,
                'label'     => 'Тип объекта',
                'attr'      => array('class'=>'form-control selectpicker','title'=>'Выберите тип объекта'),
                'empty_value' => '- любой -',
				'multiple' => false,
				'required'  => FALSE
            ))
            ->add('type', 'choice', array(
                'choices'   => $array_types,
                'label'     => 'Тип сделки',
                'attr'      => array('class'=>'form-control selectpicker','title'=>'Выберите тип сделки'),
                'empty_value' => '- любой -',
				'multiple' => false,
				'required'  => FALSE
            ))
            ->add('town_id', 'choice', array(
                'choices'   => $array_towns,
                'label'     => 'Город',
                'attr'      => array('class'=>'form-control changeable selectpicker','title'=>'Выберите город'),
                'empty_value' => '- любой -',
				'multiple' => false,
				'required'  => FALSE
            ))
            ->add('area_id', 'choice', array(
                'choices'   => $array_areas,
                'label'     => 'Район',                
                'attr'      => array(
                  'class'=>'form-control selectpicker',
                  'title'=>'Выберите район',
                  'data-actions-box'=>'false',
                  'disabled' => $array_areas?false:true
                  ),
                'required'  => FALSE,
                'multiple'  => TRUE
            ))
            ->add('price_from', 'money', array(
                'label'     => 'Цена',
                'precision' => 0,
                'required'  => FALSE,
                'attr'      => array('placeholder' => 'от')
            ))
            ->add('price_to', 'money', array(
                'precision' => 0,
                'required'  => FALSE,
                'attr'      => array('placeholder' => 'до')
            ))
			/*
            ->add('sqprice_from', 'money', array(
                'label'     => 'Цена/м²',
                'precision' => 0,
                'required'  => FALSE,
                'attr'      => array('placeholder' => 'от')
            ))
            ->add('sqprice_to', 'money', array(
                'precision' => 0,
                'required'  => FALSE,
                'attr'      => array('placeholder' => 'до')
            ))
			*/
			->add('period_id', 'choice', array(
                'choices'   => $array_period,
                'label'     => 'Срок сдачи',
                'attr'      => array('class'=>'form-control changeable selectpicker','title'=>'Выберите срок сдачи'),
                'empty_value' => '- любой -',
				'multiple' => false,
				'required'  => FALSE
            ))
            ->add('square_from', 'money', array(
                'label'     => 'Площадь (м²)',
                'precision' => 0,
                'required'  => FALSE,
                'attr'      => array('placeholder' => 'от')
            ))
            ->add('square_to', 'money', array(
                'precision' => 0,
                'required'  => FALSE,
                'attr'      => array('placeholder' => 'до')
            ))            
            ->add('save', 'submit', array(
              'label' => 'Подобрать',
              'attr'  => array(
                'class'=>'btn btn-primary-o form-control'
            )))
            ->getForm();

    }

    public function getName()
    {

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection'   => false,
        ));
    }

}
