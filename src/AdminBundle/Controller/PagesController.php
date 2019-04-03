<?php

namespace AdminBundle\Controller;

use SiteBundle\Model\SettingsQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SiteBundle\Model\Pages;
use SiteBundle\Model\PagesQuery;
use AdminBundle\Form\PagesType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Process\Process;

class PagesController extends Controller
{

    /**
     * @Route("/pages")
     */
    public function indexAction()
    {
        $items = PagesQuery::create()
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

        return $this->render('AdminBundle:Default:pages.html.twig',array(
            'pagination' 		=> $pagination,
            'back' => $this->generateUrl('admin_default_index')
        ));
    }

    /**
 * @Route("/pages/add")
 */
    public function addAction(Request $request)
    {
        $item = new Pages();

        $form = $this->createForm(new PagesType(), $item);
        $form->handleRequest($request);
        if ($form->isValid()) {
            if (!$item->getMenusId()) $item->setMenusId(NULL);
            $item->save();
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Успешно добавлено!'
            );
            $settings = SettingsQuery::create()->findOne();
            $php_path = $settings->getPhpPath() ? : 'php';
            $process = new Process($php_path.' '.$this->get('kernel')->getRootDir().'/console presta:sitemap:dump ../web/');
            $process->run();
            return $this->redirect($this->generateUrl('admin_pages_index'));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'form' 		=> $form->createView()
        ));
    }

    /**
     * @Route("/pages/edit/{id}")
     */
    public function editAction($id, Request $request)
    {
        $item = PagesQuery::create()
            ->filterById($id)
            ->findOne();

        $form = $this->createForm(new PagesType(), $item);
        $form->handleRequest($request);
        if ($form->isValid()) {
            if (!$item->getMenusId()) $item->setMenusId(NULL);
            $item->save();
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Успешно сохранено!'
            );
            return $this->redirect($this->generateUrl('admin_pages_index'));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'form' 		=> $form->createView()
        ));
    }

    /**
     * @Route("/pages/delete/{id}")
     */
    public function deleteAction($id)
    {
        $item = PagesQuery::create()
            ->filterById($id)
            ->findOne();

        if ($item) {
            $item->delete();
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Успешно удалено!'
            );
            $settings = SettingsQuery::create()->findOne();
            $php_path = $settings->getPhpPath() ? : 'php';
            $process = new Process($php_path.' '.$this->get('kernel')->getRootDir().'/console presta:sitemap:dump ../web/');
            $process->run();
        }
        return $this->redirect($this->generateUrl('admin_pages_index'));
    }
}
