<?php
/**
 * Created by PhpStorm.
 * User: NNovi
 * Date: 15.03.2016
 * Time: 16:59
 */

namespace AdminBundle\Form;

use Doctrine\Common\Collections\Criteria;
use SiteBundle\Model\LayoutsFieldsQuery;
use SiteBundle\Model\LayoutsFieldsValuesQuery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LayoutsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('id', 'hidden')
            ->add('info', 'textarea', array('label'  => 'Служебная информация','attr' => array('rows' => 3), 'required' => FALSE))
            //->add('published', 'checkbox', array('label' => 'Опубликовано', 'required' => FALSE))
            //->add('for_all', 'checkbox', array('label' => 'Видно всем', 'required' => FALSE))
            //->add('modered', 'checkbox', array('label' => 'Одобрено модератором', 'required' => FALSE))
            //->add('xml', 'checkbox', array('label' => 'Добавить в выгрузку', 'required' => FALSE))
            //->add('title', 'text', array('label' => 'Название',))
            //->add('info', 'textarea', array('label'  => 'Служебная информация','attr' => array('rows' => 3), 'required' => FALSE))
            ->add('price', 'text', array('label' => 'Цена'))
            ->add('image', 'file', array('label'  => 'Фотография', 'required' => FALSE))
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

            # Добавление полей типа объекта
            $fields = LayoutsFieldsQuery::create()
                ->filterByObjectTypeId($options['data']->getTypeObject())
                ->orderBySort()
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
                        $values = LayoutsFieldsValuesQuery::create()->filterByFieldId($field->getId())->orderBySort()->find()->toKeyValue('Id', 'Name');
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

            # Дополнительные поля
            if (@$data['type_object']) {

                # Добавление полей типа объекта
                $fields = LayoutsFieldsQuery::create()
                    ->filterByObjectTypeId($data['type_object'])
                    ->orderBySort()
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
                            $values = LayoutsFieldsValuesQuery::create()->filterByFieldId($field->getId())->orderBySort()->find()->toKeyValue('Id', 'Name');
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
        return 'object_layouts';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SiteBundle\Model\ObjectLayouts',
        ));
    }

}
