<?php

namespace AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectTypesType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('title', 'text', array('label'  => 'Название', 'required' => TRUE))
			->add('alias', 'text', array('label'  => 'Алиас', 'required' => TRUE))
			->add('image', 'file', array('label'  => 'Изображение', 'required' => FALSE))
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
            )
            ->add('generator', 'text', array('label'  => 'Генератор Названий Объектов', 'required' => false))
            ->add('sort', 'text', array('label'  => 'Порядок', 'required' => FALSE))
            ->add('live', 'checkbox', array('label' => 'Жилая недвижимость', 'required' => FALSE))
            ->add('layouts', 'checkbox', array('label' => 'Содержит планировки', 'required' => FALSE))
            ->add('generator_layout', 'text', array('label'  => 'Генератор Названий Планировок', 'required' => false))
            ->getForm();

    }

    public function getName()
    {
        return 'object_types';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SiteBundle\Model\ObjectTypes',
        ));
    }

}
