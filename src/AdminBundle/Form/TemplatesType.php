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

class TemplatesType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', 'text', array('label'  => 'Название',))
            ->add('description', 'genemu_tinymce', array(
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
                        'toolbar1' => 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media',
                        'toolbar2' => 'preview | forecolor backcolor emoticons',
                        'image_advtab' => true,
                        'relative_urls' => false,
                        'remove_script_host' => false,
                        'filemanager_title' => "Файловый менеджер",
                        'external_filemanager_path' => "/tinymce/filemanager/",
                        'external_plugins' => array( "filemanager" => "/tinymce/filemanager/plugin.min.js")
                    )
                )
            )
            //->add('save', 'submit', array('label'  => 'Сохранить',))
            ->getForm();

    }

    public function getName()
    {
        return 'templates';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SiteBundle\Model\Templates',
        ));
    }

}
