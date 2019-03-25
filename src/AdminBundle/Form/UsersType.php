<?php
/**
 * Created by PhpStorm.
 * User: NNovi
 * Date: 15.03.2016
 * Time: 16:59
 */

namespace AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Braincrafted\Bundle\BootstrapBundle\Util\LegacyFormHelper;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword as OldUserPassword;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class UsersType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (class_exists('Symfony\Component\Security\Core\Validator\Constraints\UserPassword')) {
            $constraint = new UserPassword();
        } else {
            $constraint = new OldUserPassword();
        }

        $this->buildUserForm($builder, $options);

    }

    public function buildUserForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('username', null, array('label' => 'form.username', 'translation_domain' => 'FOSUserBundle'))
            ->add('email', 'email', array('label' => 'form.email', 'translation_domain' => 'FOSUserBundle'))
            ->add('plainPassword', 'repeated', array(
                'type' => 'password',
                'options' => array('translation_domain' => 'FOSUserBundle'),
                'first_options' => array('label' => 'form.password'),
                'second_options' => array('label' => 'form.password_confirmation'),
                'invalid_message' => 'fos_user.password.mismatch',
                'required' => FALSE
            ))
            ->add('photo', 'file', array('label'  => 'Фотография', 'required' => FALSE))
            ->add('phone', 'text', array('label'  => 'Номер телефона', 'attr' => array('data-inputmask' => "'mask': '+9(999)999-9999'"), 'required' => FALSE))
            ->getForm();

    }

    public function getName()
    {
        return 'fos_user_profile';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'FOS\UserBundle\Propel\User',
            'intention'  => 'profile',
        ));
    }

}
