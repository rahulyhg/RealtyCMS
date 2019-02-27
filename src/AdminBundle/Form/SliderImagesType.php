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
use SiteBundle\Model\SlidersQuery;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SliderImagesType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $sliders = SlidersQuery::create()
            ->find()->toKeyValue('Id','Title');

        $builder
            ->add('slider_id', 'choice', array(
                'choices'   => $sliders,
                'label'  => 'Слайдер',
                'attr'  => array('class' => 'form-control'),
                'required' => TRUE
            ))
            ->add('title', 'text', array('label'  => 'Название'))
            ->add('alt', 'text', array('label'  => 'Alt'))
            ->add('link', 'text', array('label'  => 'Ссылка', 'required' => FALSE))
            ->add('path', 'file', array('label'  => 'Изображение', 'required' => FALSE))
            ->add('caption', 'genemu_tinymce', array(
                    'label'     => 'Контент',
                    'required'  => false,
                    'configs'   => array(
                        'language'                =>'ru',
                        'height'                  => 250,
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
            //->add('save', 'submit', array('label'  => 'Сохранить',))
            ->getForm();

    }

    public function getName()
    {
        return 'slider_images';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SiteBundle\Model\SliderImages',
        ));
    }

}
