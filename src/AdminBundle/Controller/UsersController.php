<?php

namespace AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\UserBundle\Propel\UserQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class UsersController extends Controller
{

    /**
     * @Route("/users")
     */
    public function indexAction()
    {
        $items = UserQuery::create()
            ->find();

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $items,
            $this->get('request')->query->get('page', 1),
            20
        );

        return $this->render('AdminBundle:Default:users.html.twig',array(
            'pagination' 		=> $pagination,
            'back' => $this->generateUrl('admin_default_index')
        ));
    }

    /**
 * @Route("/users/add")
 */
    public function addAction(Request $request)
    {
        $item = $this->container->get('fos_user.user_manager')->createUser();

        $formFactory = $this->container->get('fos_user.profile.form.factory');

        $form = $formFactory->createForm();
        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            $form
                ->add('position', 'text', array('label'  => 'Должность', 'required' => FALSE))
                ->add('enabled', 'checkbox', array('label' => 'Активен', 'required' => FALSE))
                ->add('roles', 'choice', array(
                'choices' => $this->getExistingRoles(),
                'data' => $item->getRoles(),
                'label' => 'Группа',
                'expanded' => true,
                'multiple' => true,
                'mapped' => true,
            ));
        }
        $form->setData($item);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form['photo']->getData()) {
                $dir = 'images/photo';
                $file_type = $form['photo']->getData()->getMimeType();
                switch($file_type) {
                    case 'image/png': $Filename = uniqid().'.png'; break;
                    case 'image/jpeg': $Filename = uniqid().'.jpg'; break;
                    case 'image/gif': $Filename = uniqid().'.gif'; break;
                    default: $Filename = NULL;
                }
                if ($Filename) {
                    $form['photo']->getData()->move($dir, $Filename);
                    $item->setPhoto($Filename);
                    $image = new Image($dir.'/'.$Filename);
                    $image->square(300);
                    $image->save($dir.'/'.$Filename);
                }
            }
            $this->container->get('fos_user.user_manager')->updateUser($item);
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Успешно добавлено!'
            );
            return $this->redirect($this->generateUrl('admin_users_index'));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'form' 		=> $form->createView()
        ));
    }

    public function getExistingRoles()
    {
        $roles_array = array('ROLE_AGENT' => 'Специалист','ROLE_ADMIN' => 'Администратор','ROLE_SUPER_ADMIN' => 'Суперпользователь');
        $roleHierarchy = $this->container->getParameter('security.role_hierarchy.roles');
        $roles = array_keys($roleHierarchy);

        foreach ($roles as $role) {
            $theRoles[$role] = $roles_array[$role];
        }
        return $theRoles;
    }

    /**
     * @Route("/users/edit/{id}")
     */
    public function editAction($id, Request $request)
    {
        $item = $this->container->get('fos_user.user_manager')->findUserBy(array('id' => $id));
        $photo = $item->getPhoto();
        $item->setPhoto(NULL);
        $formFactory = $this->container->get('fos_user.profile.form.factory');
        $form = $formFactory->createForm();
        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            $form
                ->add('position', 'text', array('label'  => 'Должность', 'required' => FALSE))
                ->add('enabled', 'checkbox', array('label' => 'Активен', 'required' => FALSE))
                ->add('roles', 'choice', array(
                'choices' => $this->getExistingRoles(),
                'data' => $item->getRoles(),
                'label' => 'Группа',
                'expanded' => true,
                'multiple' => true,
                'mapped' => true,
            ));
        }
        $form->setData($item);
        $form->handleRequest($request);
        if ($form->isValid()) {
            if ($form['photo']->getData()) {
                $dir = 'images/photo';
                $file_type = $form['photo']->getData()->getMimeType();
                switch($file_type) {
                    case 'image/png': $Filename = uniqid().'.png'; break;
                    case 'image/jpeg': $Filename = uniqid().'.jpg'; break;
                    case 'image/gif': $Filename = uniqid().'.gif'; break;
                    default: $Filename = NULL;
                }
                if ($Filename) {
                    $form['photo']->getData()->move($dir, $Filename);
                    $item->setPhoto($Filename);
                    $image = new Image($dir.'/'.$Filename);
                    $image->square(300);
                    $image->save($dir.'/'.$Filename);
                    if ($photo) {
                        $fs = new Filesystem();
                        try {
                            $fs->remove( $dir.'/'.$photo );
                        } catch (IOExceptionInterface $e) {
                            echo "Ошибка удаления изображения";
                        }
                    }
                }
            } else {
                $item->setPhoto($photo);
            }
            $this->container->get('fos_user.user_manager')->updateUser($item);
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Успешно сохранено!'
            );
            return $this->redirect($this->generateUrl('admin_users_index'));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'form' 		=> $form->createView(),
            'photo'     => '/images/photo/'.$photo
        ));
    }

    /**
     * @Route("/users/delete/{id}")
     */
    public function deleteAction($id)
    {
        $item = $this->container->get('fos_user.user_manager')->findUserBy(array('id' => $id));

        if ($item) {
            if ($item->getPhoto()) {
                $fs = new Filesystem();
                try {
                    $fs->remove( 'images/photo/'.$item->getPhoto() );
                } catch (IOExceptionInterface $e) {
                    echo "Ошибка удаления изображения";
                }
            }
            $this->container->get('fos_user.user_manager')->deleteUser($item);
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Успешно удалено!'
            );
        }
        return $this->redirect($this->generateUrl('admin_users_index'));
    }
}
