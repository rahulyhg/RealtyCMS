<?php
/**
 * Created by PhpStorm.
 * User: NNovi
 * Date: 15.03.2016
 * Time: 16:59
 */

namespace SiteBundle\Form;

use SiteBundle\Model\ObjectTypesFieldsQuery;
use SiteBundle\Model\ObjectTypesFieldsValuesQuery;
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
            ->find()->toKeyValue('id','title');

        $array_areas = [];

        if (@$options['data']['town_id']) {
            $areas = AreasQuery::create()
                ->filterByTownId($options['data']['town_id'])
                ->orderByTitle()
                ->find();
            foreach ($areas as $area) {
                $array_areas[$area->getId()] = $area->getTitle();
            }
        }
        $array_types = array(
            1 => 'Продажа',
            2 => 'Аренда'
        );
        $array_object_types = ObjectTypesQuery::create()
            ->find()->toKeyValue('id','title');
        
		$builder
            ->add('agent_id', 'hidden', array(            
                'required'  => FALSE
            ))
            ->add('town_id', 'choice', array(
                'choices'   => $towns,
                'label'     => 'Город',
                'attr'      => array('class'=>'form-control changeable selectpicker','title'=>'Выберите город'),
                'empty_value' => '- любой -',
                'multiple' => FALSE,
                'required'  => FALSE
            ));
        if (@$options['data']['town_id']) {
            $builder
                ->add('area_id', 'choice', array(
                    'choices' => $array_areas,
                    'label' => 'Район',
                    'attr' => array(
                        'class' => 'form-control selectpicker',
                        'title' => 'Выберите район',
                        'data-actions-box' => (count($array_areas)>10 ? 'true' : 'false'),
                        'disabled' => $array_areas ? false : true
                    ),
                    'empty_value' => '- любой -',
                    'required' => FALSE,
                    'multiple' => TRUE
                ));
        }
        $builder
            ->add('type', 'choice', array(
                'choices'   => $array_types,
                'label'     => 'Тип сделки',
                'attr'      => array('class'=>'form-control selectpicker','title'=>'Выберите тип сделки'),
                'empty_value' => '- любой -',
                'multiple' => FALSE,
                'required'  => FALSE
            ))
            ->add('type_object', 'choice', array(
                'choices'   => $array_object_types,
                'label'     => 'Тип объекта',
                'attr'      => array('class'=>'form-control selectpicker changeable','title'=>'Выберите тип объекта'),
                'empty_value' => '- любой -',
				'multiple' => FALSE,
				'required'  => FALSE
            ))
            ->add('price', 'search', array(
                'label'     => 'Цена',
                'required'  => FALSE,
            ));

		# Дополнительные поля
        if (@$options['data']) {
            if (@$options['data']['type_object']) {

                # Добавление полей типа объекта
                $fields = ObjectTypesFieldsQuery::create()
                    ->filterByObjectTypeId($options['data']['type_object'])
                    ->filterByShowInFilter(true)
                    ->orderBySort()
                    ->find();
                if ($fields) {
                    foreach ($fields as $field) {

                        /* @var $field \SiteBundle\Model\ObjectTypesFields */
                        # Типы полей: 1-текст; 2-список; 3-число; 4-числовой список; 5-флажок
                        if ($field->getType() == 5) {
                            $builder->add('params_' . $field->getId(), 'checkbox', array(
                                'label' => $field->getName() . ($field->getPostfix() ? ' (' . $field->getPostfix() . ')' : '') . ':',
                                'required' => false,
                                'mapped' => false));
                        } elseif ($field->getType() == 2 || $field->getType() == 4) {
                            $values = ObjectTypesFieldsValuesQuery::create()->filterByFieldId($field->getId())->find()->toKeyValue('Id', 'Name');
                            asort($values);
                            if ($values)
                                $builder->add('params_' . $field->getId(), 'choice', array(
                                        'label' => $field->getName() . ($field->getPostfix() ? ' (' . $field->getPostfix() . ')' : '') . ':',
                                        'empty_value' => 'выбрать',
                                        'choices' => $values,
                                        'attr' => array(
                                            'class' => 'form-control selectpicker',
                                            'data-actions-box' => (count($values)>10 ? 'true' : 'false'),
                                        ),
                                        'required' => false,
                                        'multiple' => true,
                                        'mapped' => false)
                                );
                        } elseif ($field->getType() == 3) {
                            $builder->add('params_' . $field->getId(), 'search', array(
                                    'label' => $field->getName() . ($field->getPostfix() ? ' (' . $field->getPostfix() . ')' : '') . ':',
                                    'attr' => array('class' => 'form-control'),
                                    'required' => false,
                                    'mapped' => false)
                            );
                        } else {
                            $builder->add('params_' . $field->getId(), 'text', array(
                                    'label' => $field->getName() . ($field->getPostfix() ? ' (' . $field->getPostfix() . ')' : '') . ':',
                                    'attr' => array('class' => 'form-control','data-inputmask' => $field->getMask() ?: ''),
                                    'required' => false,
                                    'mapped' => false)
                            );
                        }
                    }
                }

            }
        }
        /*$builder
            ->add('save', 'submit', array(
              'label' => 'Подобрать',
              'attr'  => array(
                'class'=>'btn btn-primary-o form-control'
            )));*/
        $builder
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
