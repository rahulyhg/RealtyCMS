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

class SettingsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', 'text', array('label'  => 'Название сайта',))
            ->add('title', 'text', array('label'  => 'Заголовок',))
            ->add('description', 'textarea', array('label'  => 'Описание','attr' => array('rows' => 3)))
            ->add('keywords', 'text', array('label'  => 'Ключевые слова',))
            ->add('email', 'text', array('label'  => 'Email проекта',))
            ->add('content', 'genemu_tinymce', array(
                    'label'     => 'Контент',
                    'required'  => false,
                    'configs'   => array(
                        'language'                =>'ru',
                        'height'                  => 200,
                        'plugins'                 => array(
                            "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
                            "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                            "save table contextmenu directionality emoticons template paste colorpicker textpattern textcolor responsivefilemanager"
                        ),
                        'toolbar1' => ' styleselect | bold italic underline removeformat | fontselect fontsizeselect | forecolor backcolor | outdent indent',
                        'toolbar2' => ' undo redo blockquote | bullist numlist alignleft aligncenter alignright alignjustify | link unlink image media emoticons | preview code',
                        'image_advtab' => true,
                        'relative_urls' => false,
                        'remove_script_host' => false,
                        'filemanager_title' => "Файловый менеджер",
                        'external_filemanager_path' => "/tinymce/filemanager/",
                        'external_plugins' => array( "filemanager" => "/tinymce/filemanager/plugin.min.js")
                    )
                )
            )
            ->add('city', 'text', array('label'  => 'Город', 'required' => FALSE))
            ->add('address', 'text', array('label'  => 'Адрес', 'required' => FALSE))
            ->add('phone', 'text', array('label'  => 'Телефон 1', 'attr' => array('data-inputmask' => "'mask': '+9(999)999-9999'"), 'required' => TRUE))
            ->add('phone_two', 'text', array('label'  => 'Телефон 2', 'attr' => array('data-inputmask' => "'mask': '+9(999)999-9999'"), 'required' => TRUE))
			->add('counters', 'textarea', array('label'  => 'Счетчики (код)', 'required' => FALSE, 'attr' => array('rows' => 10)))
            ->add('robots', 'textarea', array('label'  => 'Robots.txt', 'required' => true, 'attr' => array('rows' => 10)))
            ->add('favicon', 'file', array('label'  => 'Иконка', 'required' => FALSE, 'mapped' => false))
            ->add('logo_top', 'file', array('label'  => 'Логотип верхний', 'required' => FALSE, 'mapped' => false))
            ->add('logo_bottom', 'file', array('label'  => 'Логотип нижний', 'required' => FALSE, 'mapped' => false))
            ->add('facebook', 'text', array('label'  => 'Страница Facebook', 'required' => FALSE))
            ->add('twitter', 'text', array('label'  => 'Страница Twitter', 'required' => FALSE))
            ->add('vk', 'text', array('label'  => 'Страница ВКонтакте', 'required' => FALSE))
            ->add('youtube', 'text', array('label'  => 'Страница Youtube', 'required' => FALSE))
            ->add('google', 'text', array('label'  => 'Страница Google+', 'required' => FALSE))
            ->add('instagram', 'text', array('label'  => 'Страница Instagram', 'required' => FALSE))
            ->add('linkedin', 'text', array('label'  => 'Страница Linkedin', 'required' => FALSE))            
            //->add('save', 'submit', array('label'  => 'Сохранить',))
            ->getForm();

    }

    public function getName()
    {
        return 'settings';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SiteBundle\Model\Settings',
        ));
    }

}
