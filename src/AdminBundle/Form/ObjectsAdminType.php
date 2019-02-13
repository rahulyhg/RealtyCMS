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
use FOS\UserBundle\Propel\UserQuery;
use SiteBundle\Model\TownsQuery;
use SiteBundle\Model\AreasQuery;
use SiteBundle\Model\ObjectTypesQuery;

class ObjectsAdminType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $array_users = UserQuery::create()            
            ->find()->toKeyValue('id','Username');
		
        $towns = TownsQuery::create()
            ->find();
        $array_towns = [];
        $first_town = NULL;
        foreach ($towns as $town) {
            if (!$first_town) $first_town = $town->getId();
            $array_towns[$town->getId()] = $town->getTitle();
        }

        $array_areas = [];
        if ($options['data']->getTownId()) {
            $areas = AreasQuery::create()
                ->filterByTownId($options['data']->getTownId())
                ->find();
            foreach ($areas as $area) {
                $array_areas[$area->getId()] = $area->getTitle();
            }
        } elseif ($first_town) {
            $areas = AreasQuery::create()
                ->filterByTownId($first_town)
                ->find();
            foreach ($areas as $area) {
                $array_areas[$area->getId()] = $area->getTitle();
            }
        }
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
            ->add('published', 'checkbox', array('label'  => 'Опубликовано', 'required' => FALSE))
            ->add('for_all', 'checkbox', array('label'  => 'Видно всем', 'required' => FALSE))
            ->add('modered', 'checkbox', array('label'  => 'Одобрено модератором', 'required' => FALSE))
            ->add('xml', 'checkbox', array('label'  => 'Добавить в выгрузку', 'required' => FALSE))            
            ->add('user_id', 'choice', array(
                'choices'   => $array_users,
                'label'  => 'Консультант',
                'required' => TRUE
            ))
            ->add('title', 'text', array('label'  => 'Название',))
            ->add('town_id', 'choice', array(
                'choices'   => $array_towns,
                'label'  => 'Город',
                'required' => TRUE
            ))
            ->add('area_id', 'choice', array(
                'choices'   => $array_areas,
                'label'  => 'Район',
                'required' => FALSE
            ))
            ->add('address', 'text', array('label'  => 'Адрес', 'required' => FALSE))   
			->add('coordinates', 'text', array('label'  => 'Координаты', 'required' => FALSE))   			
            ->add('type', 'choice', array(
                'choices'   => $array_types,
                'label'  => 'Тип сделки',
                'required' => TRUE
            ))
            ->add('type_object', 'choice', array(
                'choices'   => $array_object_types,
                'label'  => 'Тип объекта',
                'required' => TRUE
            ))
			->add('period_id', 'choice', array(
                'choices'   => $array_period,
                'label'  => 'Срок сдачи',
                'required' => FALSE
            ))
            ->add('price', 'text', array('label'  => 'Цена'))
            ->add('square', 'text', array('label'  => 'Площадь'))
            ->add('sq_price', 'text', array('label'  => 'Цена/м²', 'attr' => array('readonly' => TRUE)))
            ->add('description', 'genemu_tinymce', array(
                    'label'     => 'Описание',
                    'required'  => false,
                    'configs'   => array(
                        'language'                =>'ru',
                        'height'                  => 400,
                        'plugins'                 => array(
                            "advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker",
                            "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                            "save table contextmenu directionality emoticons template paste textcolor responsivefilemanager"
                        ),
                        'theme_advanced_buttons1' => 'bold,italic,underline,undo,redo,link,unlink,forecolor,styleselect,removeformat,cleanup,code',
                        'relative_urls' => true,
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
        return 'objects';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SiteBundle\Model\Objects',
        ));
    }

}
