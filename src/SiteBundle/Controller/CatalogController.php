<?php

namespace SiteBundle\Controller;

use SiteBundle\Form\SearchType;
use SiteBundle\Model\ObjectsQuery;
use SiteBundle\Model\TownsQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SiteBundle\Model\SettingsQuery;
use SiteBundle\Model\MenusQuery;
use SiteBundle\Model\ObjectTypesQuery;
use \Criteria;
use SiteBundle\Model\SlidersQuery;
use SiteBundle\Model\PagesQuery;
use FOS\UserBundle\Propel\UserQuery;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;

class CatalogController extends Controller
{
    /**
     * @Route("/catalog/update")
     */
    public function updateAction(Request $request)
    {
        $town_id = @$request->query->get('town_id')?:NULL;        

        $search_form = $this->createForm(new SearchType(),[$town_id]);
        $search_form->submit($request);        

        return $this->render('SiteBundle:Form:search_form.html.twig', array(
            'search_form'   => $search_form->createView()
        ));
    }
	
	/**
     * @Route("/catalog/{alias}")
     */
    public function indexAction($alias = null, Request $request)
    {        
		if ($alias) {
			$cat = ObjectTypesQuery::create()            
					->filterByAlias($alias)->findOne();		
			if (!$cat) {
				throw $this->createNotFoundException(
					'Нет данного типа объектов'
				);
			}
			return $this->redirect($this->generateUrl('site_catalog_index', array('type_object' => $cat->getId())));			
		}		
		
		$settings = SettingsQuery::create()
            ->findOne();
        $menus = MenusQuery::create()
            ->find();

        $agent_id = @$request->query->get('agent_id')?:NULL;
        $town_id = @$request->query->get('town_id')?:NULL;
        $area_id = @$request->query->get('area_id')?:NULL; 
		$period_id = @$request->query->get('period_id')?:NULL;		
        $type_object = @$request->query->get('type_object')?:NULL;
		$type = @$request->query->get('type')?:NULL;
        $price_from = @$request->query->get('price_from')?:NULL;
        $price_to = @$request->query->get('price_to')?:NULL;
        //$sqprice_from = @$request->query->get('sqprice_from')?:NULL;
        //$sqprice_to = @$request->query->get('sqprice_to')?:NULL;
        $square_from = @$request->query->get('square_from')?:NULL;
        $square_to = @$request->query->get('square_to')?:NULL;

        $search_form = $this->createForm(new SearchType(),array($town_id));        
        $search_form->submit($request);

        $catalog_query = ObjectsQuery::create();
        //$catalog_query->filterByForAll(true);
        $catalog_query->filterByPublished(true);
        $catalog_query->filterByModered(true);
        if ($agent_id) $catalog_query->filterByUserId($agent_id);
        if ($town_id) $catalog_query->filterByTownId($town_id);
		if ($period_id) $catalog_query->filterByPeriodId($period_id);
        if ($area_id) $catalog_query->filterByAreaId($area_id);
        if ($type_object) $catalog_query->filterByTypeObject($type_object);
        if ($type) $catalog_query->filterByType($type);
        if ($price_from) $catalog_query->where('price >='.$price_from);
        if ($price_to) $catalog_query->where('price <='.$price_to);
        if ($square_from) $catalog_query->where('square >='.$square_from);
        if ($square_to) $catalog_query->where('square <='.$square_to);        
        //if ($sqprice_from) $catalog_query->where('sq_price >='.$sqprice_from);
        //if ($sqprice_to) $catalog_query->where('sq_price <='.$sqprice_to);

        $catalog_query->join('ObjectImages', Criteria::LEFT_JOIN);
        
		// Сортировка
        $dir = @$request->query->get('dir') ?: (@$request->cookies->get('dir') ?: 'asc');
        $sort = @$request->query->get('sorting') ?: (@$request->cookies->get('sorting') ?: 'price');
        $on_page = @$request->query->get('on_page') ?: (@$request->cookies->get('on_page') ?: '20');
        $catalog_query->orderBy($sort, $dir);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $catalog_query,
            $this->get('request')->query->get('page', 1),
            20
        );
		
		$categories = ObjectTypesQuery::create()            
            ->find();
			
		$category = null;
		if ($type_object) {
			$category = ObjectTypesQuery::create()            
            ->filterById($type_object)->findOne();
		}
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

        $response = $this->render('SiteBundle:Default:catalog.html.twig', array(
            'settings'      => $settings,
            'menus'         => $menus,
            'catalog'       => $pagination,
            'category'		=> $category,
            'categories' 	=> $categories,
            'search_form'   => $search_form->createView(),
            'period'		=> $array_period,
            'dir' 			=> $dir,
            'sort' 			=> $sort,
            'on_page' 		=> $on_page
        ));
        $response->headers->setCookie(new Cookie('dir', $dir, time() + 3600 * 24 * 7, $this->generateUrl('site_catalog_index')));
        $response->headers->setCookie(new Cookie('sort', $sort, time() + 3600 * 24 * 7, $this->generateUrl('site_catalog_index')));
        $response->headers->setCookie(new Cookie('on_page', $on_page, time() + 3600 * 24 * 7, $this->generateUrl('site_catalog_index')));
        return $response;
    }

    /**
     * @Route("/catalog/object/{id}", requirements={"id" = "\d+"})
     */
    public function objectAction($id, Request $request)
    {
        $settings = SettingsQuery::create()
            ->findOne();
        $menus = MenusQuery::create()
            ->find();
        $object = ObjectsQuery::create()
            ->findOneById($id);
			
		if (!$object) {
            throw $this->createNotFoundException(
                'Нет данного объекта'
            );
        }
		
		// Похожие объекты
		$price_avg = 500000; // +- цена
		$catalog_query = ObjectsQuery::create('Objects');
		$catalog_query->where('Objects.Id NOT IN ?', array($object->getId()));
        $catalog_query->filterByForAll(true);
        $catalog_query->filterByPublished(true);
        $catalog_query->filterByModered(true);        
        $catalog_query->filterByTownId($object->getTownId());
        $catalog_query->filterByAreaId($object->getAreaId());
        $catalog_query->filterByTypeObject($object->getTypeObject());
        $catalog_query->filterByType($object->getType());
        $catalog_query->where('price >='.($object->getPrice() - $price_avg));
        $catalog_query->where('price <='.($object->getPrice() + $price_avg));        

        $catalog_query->join('ObjectImages', Criteria::LEFT_JOIN);
        $catalog_query->orderBy('ObjectImages.srt', 'asc');
        $catalog_query->orderByUpdatedAt();
		$catalog_query->groupById();		
		
        $catalog = $catalog_query->limit(4)->find();
		
		$categories = ObjectTypesQuery::create()            
            ->find();
			
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
			
        return $this->render('SiteBundle:Default:object.html.twig', array(
            'settings'      => $settings,
            'menus'         => $menus,
			'categories' 	=> $categories,
			'catalog'		=> $catalog,
            'object'        => $object,
			'period'		=> $array_period
        ));
    }

}
