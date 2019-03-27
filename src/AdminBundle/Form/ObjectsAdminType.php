<?php
/**
 * Created by PhpStorm.
 * User: NNovi
 * Date: 15.03.2016
 * Time: 16:59
 */

namespace AdminBundle\Form;

use Doctrine\Common\Collections\Criteria;
use SiteBundle\Model\ObjectTypesFieldsQuery;
use SiteBundle\Model\ObjectTypesFieldsValuesQuery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use FOS\UserBundle\Propel\UserQuery;
use SiteBundle\Model\TownsQuery;
use SiteBundle\Model\AreasQuery;
use SiteBundle\Model\ObjectTypesQuery;

class ObjectsAdminType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $users = UserQuery::create()
            ->find()->toKeyValue('id', 'Username');

        $towns = TownsQuery::create()
            ->find()->toKeyValue('id', 'Title');

        $areas = [];
        if ($options['data']->getTownId()) {
            $areas = AreasQuery::create()
                ->filterByTownId($options['data']->getTownId())
                ->find()->toKeyValue('id', 'Title');
        }
        $types = array(
            1 => 'Продажа',
            2 => 'Аренда'
        );

        $object_types = ObjectTypesQuery::create()
            ->find()->toKeyValue('id', 'title');

        $builder
            ->add('id', 'hidden')
            ->add('published', 'checkbox', array('label' => 'Опубликовано', 'required' => FALSE))
            //->add('for_all', 'checkbox', array('label' => 'Видно всем', 'required' => FALSE))
            ->add('modered', 'checkbox', array('label' => 'Одобрено модератором', 'required' => FALSE))
            //->add('xml', 'checkbox', array('label' => 'Добавить в выгрузку', 'required' => FALSE))
            ->add('info', 'textarea', array('label'  => 'Служебная информация','attr' => array('rows' => 3), 'required' => FALSE))
            ->add('user_id', 'choice', array(
                'choices' => $users,
                'attr' => array('class' => 'form-control'),
                'label' => 'Специалист',
                'required' => TRUE
            ))
            //->add('title', 'text', array('label' => 'Название',))
            ->add('town_id', 'choice', array(
                'empty_value' => 'выберите город',
                'choices' => $towns,
                'attr' => array('class' => 'changeable form-control'),
                'label' => 'Город',
                'required' => TRUE
            ));
        if ($options['data']->getTownId()) {
            $builder->
            add('area_id', 'choice', array(
                'choices' => $areas,
                'label' => 'Район',
                'attr' => array('class' => 'form-control'),
                'required' => FALSE
            ));
        }
        $builder
            ->add('address', 'text', array('label' => 'Адрес', 'required' => FALSE))
            //->add('coordinates', 'text', array('label' => 'Координаты', 'required' => FALSE))
            ->add('type', 'choice', array(
                'choices' => $types,
                'label' => 'Тип сделки',
                'attr' => array('class' => 'form-control'),
                'required' => TRUE
            ))
            ->add('type_object', 'choice', array(
                'empty_value' => 'выберите тип объекта',
                'choices' => $object_types,
                'label' => 'Тип объекта',
                'attr' => array('class' => 'changeable form-control'),
                'required' => TRUE
            ));
        if ($options['data']->getObjectPrice()) {
            $builder->add('price', 'text', array('label' => 'Стоимость объекта'));
        }
        $builder
            ->add('description', 'genemu_tinymce', array(
                    'label' => 'Описание',
                    'required' => false,
                    'configs' => array(
                        'language' => 'ru',
                        'height' => 200,
                        'plugins' => array(
                            "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
                            "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                            "save table contextmenu directionality emoticons template paste textcolor responsivefilemanager"
                        ),
                        'theme_advanced_buttons1' => 'bold,italic,underline,undo,redo,link,unlink,forecolor,styleselect,removeformat,cleanup,code',
                        'relative_urls' => true,
                        'filemanager_title' => "Файловый менеджер",
                        'external_filemanager_path' => "/tinymce/filemanager/",
                        'external_plugins' => array("filemanager" => "/tinymce/filemanager/plugin.min.js")
                    )
                )
            );

        # Дополнительные поля
        if ($options['data']->getTypeObject()) {

            $object_type = ObjectTypesQuery::create()->findPk($options['data']->getTypeObject());

            # Добавление полей категорий (подкатегорий)
            $fields = ObjectTypesFieldsQuery::create()
                ->leftJoinObjectTypesFieldsValues()
                ->filterByObjectTypeId($object_type->getId())
                ->orderByRequired()->orderByObjectTypeId()->orderByName()
                ->find();
            if ($fields) {
                foreach ($fields as $field) {

                    /* @var $field \SiteBundle\Model\ObjectTypesFields */
                    # Типы полей: 1-текст; 2-список; 5-флажок
                    if ($field->getType() == 5) {
                        $builder->add('params_' . $field->getId(), 'checkbox', array(
                                'label' => $field->getName() . ($field->getPostfix() ? ' (' . $field->getPostfix() . ')' : '') . ':',
                                'required' => $field->getRequired() ? true : false,
                                'data' => $options['data']->getParams($field->getId()),
                                'mapped' => false));
                    } elseif ($field->getType() == 2 || $field->getType() == 4) {
                        $values = ObjectTypesFieldsValuesQuery::create()->filterByFieldId($field->getId())->orderBySort()->find()->toKeyValue('Id', 'Name');
                        //asort($values);
                        if ($values)
                            $builder->add('params_' . $field->getId(), 'choice', array(
                                'label' => $field->getName() . ($field->getPostfix() ? ' (' . $field->getPostfix() . ')' : '') . ':',
                                'empty_value' => 'выбрать',
                                'choices' => $values,
                                'attr' => array('class' => 'form-control', 'data-value' => $options['data']->getParams($field->getId())),
                                'required' => $field->getRequired() ? true : false,
                                'data' => $options['data']->getParams($field->getId()),
                                'mapped' => false)
                            );
                    } else {
                        $builder->add('params_' . $field->getId(), 'text', array(
                            'label' => $field->getName() . ($field->getPostfix() ? ' (' . $field->getPostfix() . ')' : '') . ':',
                            'attr' => array('data-inputmask' => $field->getMask() ?: ''),
                            'required' => $field->getRequired() ? true : false,
                            'data' => $options['data']->getParams($field->getId()),
                            'mapped' => false)
                        );
                    }
                }
            }

        }

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();
            # Если тип пользователя компания
            if (@$data['town_id']) {
                $areas = AreasQuery::create()
                    ->filterByTownId($data['town_id'])
                    ->find()->toKeyValue('id', 'Title');
                $form->add('area_id', 'choice', array(
                    'choices' => $areas,
                    'label' => 'Район',
                    'attr' => array('class' => 'form-control'),
                    'required' => FALSE
                ));
            }

            # Дополнительные поля
            if (@$data['type_object']) {

                $object_type = ObjectTypesQuery::create()->findPk($data['type_object']);

                # Добавление полей категорий (подкатегорий)
                $fields = ObjectTypesFieldsQuery::create()
                    ->leftJoinObjectTypesFieldsValues()
                    ->filterByObjectTypeId($object_type->getId())
                    ->orderByRequired()->orderByObjectTypeId()->orderByName()
                    ->find();
                if ($fields) {
                    foreach ($fields as $field) {

                        /* @var $field \SiteBundle\Model\ObjectTypesFields */
                        # Типы полей: 1-текст; 2-список; 5-флажок
                        if ($field->getType() == 5) {
                            $form->add('params_' . $field->getId(), 'checkbox',
                                array(
                                    'label' => $field->getName() . ($field->getPostfix() ? ' (' . $field->getPostfix() . ')' : '') . ':',
                                    'required' => $field->getRequired() ? true : false,
                                    'mapped' => false));
                        } elseif ($field->getType() == 2 || $field->getType() == 4) {
                            $values = ObjectTypesFieldsValuesQuery::create()->filterByFieldId($field->getId())->orderBySort()->find()->toKeyValue('Id', 'Name');
                            //asort($values);
                            if ($values) $form->add('params_' . $field->getId(), 'choice',
                                array(
                                    'label' => $field->getName() . ($field->getPostfix() ? ' (' . $field->getPostfix() . ')' : '') . ':',
                                    'empty_value' => 'выбрать',
                                    'choices' => $values,
                                    'attr' => array('class' => 'form-control'),
                                    'required' => $field->getRequired() ? true : false,
                                    'mapped' => false));
                        } else {
                            $form->add('params_' . $field->getId(), 'text', array(
                                'label' => $field->getName() . ($field->getPostfix() ? ' (' . $field->getPostfix() . ')' : '') . ':',
                                'attr' => array('data-inputmask' => $field->getMask() ?: ''),
                                'required' => $field->getRequired() ? true : false,
                                'mapped' => false));
                        }
                    }
                }

            }

        });
        $builder->getForm();

    }

    public function getName()
    {
        return 'objects';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SiteBundle\Model\Objects',
        ));
    }

}
