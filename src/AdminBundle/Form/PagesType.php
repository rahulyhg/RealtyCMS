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
use SiteBundle\Model\MenusQuery;

class PagesType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $menus = MenusQuery::create()
            ->find();
        $array_menu = [NULL=>'Не использовать'];
        foreach ($menus as $menu) {
            $array_menu[$menu->getId()] = $menu->getTitle();
        }

        $builder
            ->add('title', 'text', array('label'  => 'Заголовок',))
            ->add('alias', 'text', array('label'  => 'Алиас', 'required' => TRUE))
            ->add('description', 'textarea', array('label'  => 'Описание','attr' => array('rows' => 3)))
            ->add('keywords', 'text', array('label'  => 'Ключевые слова', 'required' => FALSE))
            ->add('author', 'text', array('label'  => 'Автор', 'required' => FALSE))
            ->add('menus_id', 'choice', array(
                'choices'   => $array_menu,
                'label'  => 'Привязать к меню',
                'required' => FALSE
            ))
            ->add('content', 'genemu_tinymce', array(
                    'label'     => 'Контент',
                    'required'  => false,
                    'configs'   => array(
                        'language'                =>'ru',
                        'height'                  => 400,
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
            ->add('save', 'submit', array('label'  => 'Сохранить',))
            ->getForm();

    }

    public function getName()
    {
        return 'pages';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SiteBundle\Model\Pages',
        ));
    }

}
