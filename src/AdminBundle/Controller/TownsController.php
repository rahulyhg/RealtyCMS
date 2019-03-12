<?php

namespace AdminBundle\Controller;

use SiteBundle\Model\Areas;
use SiteBundle\Model\AreasQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SiteBundle\Model\Towns;
use SiteBundle\Model\TownsQuery;
use AdminBundle\Form\TownsType;
use Symfony\Component\HttpFoundation\Request;

class TownsController extends Controller
{

    /**
     * @Route("/towns")
     */
    public function indexAction()
    {
        $items = TownsQuery::create()
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

        return $this->render('AdminBundle:Default:towns.html.twig',array(
            'pagination' 		=> $pagination,
            'back' => $this->generateUrl('admin_default_index')
        ));
    }

    /**
 * @Route("/towns/add")
 */
    public function addAction(Request $request)
    {
        $item = new Towns();
        $cnt = 0;

        $form = $this->createForm(new TownsType(), $item);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $item->save();
            if ($form['areas']->getData()) {
                $cnt=0;
                $areas = explode(',',$form['areas']->getData());
                foreach ($areas as $area_name) {
                    $area = new Areas();
                    $area->setTitle(trim($area_name));
                    $area->setTownId($item->getId());
                    $area->save();
                    $cnt++;
                }
            }
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Успешно добавлено!'.($cnt?' Добавлено районов: '.$cnt:'')
            );
            return $this->redirect($this->generateUrl('admin_towns_index'));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'form' 		=> $form->createView()
        ));
    }

    /**
     * @Route("/towns/edit/{id}")
     */
    public function editAction($id, Request $request)
    {
        $item = TownsQuery::create()
            ->filterById($id)
            ->findOne();
        $cnt = 0;

        $form = $this->createForm(new TownsType(), $item);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $item->save();
            if ($form['areas']->getData()) {
                $cnt=0;
                AreasQuery::create()
                    ->filterByTownId($item->getId())
                    ->delete();
                $areas = explode(',',$form['areas']->getData());
                foreach ($areas as $area_name) {
                    $area = new Areas();
                    $area->setTitle(trim($area_name));
                    $area->setTownId($item->getId());
                    $area->save();
                    $cnt++;
                }
            }
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Успешно сохранено!'.($cnt?' Добавлено районов: '.$cnt:'')
            );
            return $this->redirect($this->generateUrl('admin_towns_index'));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'form' 		=> $form->createView()
        ));
    }

    /**
     * @Route("/towns/delete/{id}")
     */
    public function deleteAction($id)
    {
        $item = TownsQuery::create()
            ->filterById($id)
            ->findOne();

        if ($item) {
            $item->delete();
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Успешно удалено!'
            );
        }
        return $this->redirect($this->generateUrl('admin_towns_index'));
    }


}
