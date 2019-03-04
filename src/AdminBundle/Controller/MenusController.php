<?php

namespace AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SiteBundle\Model\Menus;
use SiteBundle\Model\MenusQuery;
use AdminBundle\Form\MenusType;
use Symfony\Component\HttpFoundation\Request;

class MenusController extends Controller
{

    /**
     * @Route("/menus")
     */
    public function indexAction()
    {
        $items = MenusQuery::create()
            ->orderByParentId()
            ->orderBySort()
            ->find();
        if (!$items) {
            throw $this->createNotFoundException(
                'Нет доступных элементов'
            );
        }

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $items,
            $this->get('request')->query->get('page', 1),
            20
        );

        return $this->render('AdminBundle:Default:menus.html.twig',array(
            'pagination' 		=> $pagination
        ));
    }

    /**
 * @Route("/menus/add")
 */
    public function addAction(Request $request)
    {
        $item = new Menus();

        $form = $this->createForm(new MenusType(), $item);
        $form->handleRequest($request);
        if ($form->isValid()) {
            if (!$item->getParentId()) $item->setParentId(NULL);
            $item->save();
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Успешно добавлено!'
            );
            return $this->redirect($this->generateUrl('admin_menus_index'));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'form' 		=> $form->createView()
        ));
    }

    /**
     * @Route("/menus/edit/{id}")
     */
    public function editAction($id, Request $request)
    {
        $item = MenusQuery::create()
            ->filterById($id)
            ->findOne();

        $form = $this->createForm(new MenusType(), $item);
        $form->handleRequest($request);
        if ($form->isValid()) {
            if (!$item->getParentId()) $item->setParentId(NULL);
            $item->save();
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Успешно сохранено!'
            );
            return $this->redirect($this->generateUrl('admin_menus_index'));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'form' 		=> $form->createView()
        ));
    }

    /**
     * @Route("/menus/delete/{id}")
     */
    public function deleteAction($id)
    {
        $item = MenusQuery::create()
            ->filterById($id)
            ->findOne();

        if ($item) {
            $item->delete();
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Успешно удалено!'
            );
        }
        return $this->redirect($this->generateUrl('admin_menus_index'));
    }

    /**
     * @Route("/menus/sort")
     */
    public function sortAction(Request $request)
    {

        $array = $request->request->get('array');
        $cnt=1;
        foreach ($array as $item) {
            $menu_item = MenusQuery::create()->findPk($item);
            $menu_item->setSort($cnt++);
            $menu_item->save();
        }
        return $this->redirect($request->headers->get('referer'));
    }
}
