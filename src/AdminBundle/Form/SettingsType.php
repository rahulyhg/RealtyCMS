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
            ->add('copyright', 'textarea', array('label'  => 'Copyright','attr' => array('rows' => 2)))
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
            ->add('phone', 'text', array('label'  => 'Телефон 1', 'required' => TRUE))
            ->add('phone_two', 'text', array('label'  => 'Телефон 2', 'required' => FALSE))
			->add('counters', 'textarea', array('label'  => 'Счетчики (код)', 'required' => FALSE, 'attr' => array('rows' => 8)))
            ->add('robots', 'textarea', array('label'  => 'Robots.txt', 'required' => true, 'attr' => array('rows' => 8)))
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
            ->add('php_path', 'text', array('label'  => 'Путь к PHP', 'required' => TRUE))
            ->add('why_1_title', 'text', array('label'  => 'Почему мы - Заголовок 1', 'required' => FALSE))
            ->add('why_1_text', 'textarea', array('label'  => 'Почему мы - Текст 1', 'required' => FALSE, 'attr' => array('rows' => 2)))
            ->add('why_2_title', 'text', array('label'  => 'Почему мы - Заголовок 2', 'required' => FALSE))
            ->add('why_2_text', 'textarea', array('label'  => 'Почему мы - Текст 2', 'required' => FALSE, 'attr' => array('rows' => 2)))
            ->add('why_3_title', 'text', array('label'  => 'Почему мы - Заголовок 3', 'required' => FALSE))
            ->add('why_3_text', 'textarea', array('label'  => 'Почему мы - Текст 3', 'required' => FALSE, 'attr' => array('rows' => 2)))
            ->add('why_4_title', 'text', array('label'  => 'Почему мы - Заголовок 4', 'required' => FALSE))
            ->add('why_4_text', 'textarea', array('label'  => 'Почему мы - Текст 4', 'required' => FALSE, 'attr' => array('rows' => 2)))
            ->add('why_5_title', 'text', array('label'  => 'Почему мы - Заголовок 5', 'required' => FALSE))
            ->add('why_5_text', 'textarea', array('label'  => 'Почему мы - Текст 5', 'required' => FALSE, 'attr' => array('rows' => 2)))
            ->add('why_6_title', 'text', array('label'  => 'Почему мы - Заголовок 6', 'required' => FALSE))
            ->add('why_6_text', 'textarea', array('label'  => 'Почему мы - Текст 6', 'required' => FALSE, 'attr' => array('rows' => 2)))
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
