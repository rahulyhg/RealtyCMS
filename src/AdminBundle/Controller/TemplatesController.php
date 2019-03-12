<?php

namespace AdminBundle\Controller;

use AdminBundle\Form\TemplatesType;
use SiteBundle\Model\Templates;
use SiteBundle\Model\TemplatesQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class TemplatesController extends Controller
{
    /**
     * @Route("/templates")
     */
    public function indexAction()
    {
        $items = TemplatesQuery::create()
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

        return $this->render('AdminBundle:Default:templates.html.twig',array(
            'pagination' 		=> $pagination,
            'back' => $this->generateUrl('admin_default_index')
        ));
    }

    /**
     * @Route("/templates/add")
     */
    public function addAction(Request $request)
    {
        $item = new Templates();

        $form = $this->createForm(new TemplatesType(), $item);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $item->save();
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Успешно добавлено!'
            );
            return $this->redirect($this->generateUrl('admin_templates_index'));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'form' 		=> $form->createView()
        ));
    }

    /**
     * @Route("/templates/edit/{id}")
     */
    public function editAction($id, Request $request)
    {
        $item = TemplatesQuery::create()
            ->filterById($id)
            ->findOne();

        $form = $this->createForm(new TemplatesType(), $item);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $item->save();
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Успешно сохранено!'
            );
            return $this->redirect($this->generateUrl('admin_templates_index'));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'form' 		=> $form->createView()
        ));
    }

    /**
     * @Route("/templates/delete/{id}")
     */
    public function deleteAction($id)
    {
        $item = TemplatesQuery::create()
            ->filterById($id)
            ->findOne();

        if ($item) {
            $item->delete();
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Успешно удалено!'
            );
        }
        return $this->redirect($this->generateUrl('admin_templates_index'));
    }

}
