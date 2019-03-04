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

class MenusType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $menus = MenusQuery::create()
            ->find();
        $array_menu = [NULL=>'нет'];
        foreach ($menus as $menu) {
            $array_menu[$menu->getId()] = $menu->getTitle();
        }

        $builder
            ->add('parent_id', 'choice', array(
                'choices'   => $array_menu,
                'label'  => 'Родитель',
                'required' => FALSE,
                'attr' => array('class' => 'form-control'),
            ))
            ->add('title', 'text', array('label'  => 'Название', 'required' => TRUE))
            ->add('sort', 'text', array('label'  => 'Порядок', 'required' => FALSE))
            ->getForm();

    }

    public function getName()
    {
        return 'menus';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'SiteBundle\Model\Menus',
        ));
    }

}
