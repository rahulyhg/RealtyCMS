<?php

namespace SiteBundle\Controller;

use Propel\Bundle\PropelBundle\Util\PropelInflector;
use SiteBundle\Form\SearchType;
use SiteBundle\Model\ObjectParams;
use SiteBundle\Model\ObjectParamsQuery;
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

        $search_form = $this->createForm(new SearchType(), $request->query->all());
        $search_form->submit($request);

        return $this->render('SiteBundle:Form:search_form.html.twig', array(
            'search_form'   => $search_form->createView()
        ));
    }
	
	/**
     * @Route("/catalog")
     */
    public function indexAction(Request $request)
    {        
		$settings = SettingsQuery::create()
            ->findOne();
        $menus = MenusQuery::create()
            ->filterByParentId(NULL)
            ->orderBySort()
            ->find();

        $search_form = $this->createForm(new SearchType(),$request->query->all());
        $search_form->submit($request);

        # Фильтр
        $search_query = '';
        $price_where = '';
        $filter_array = array();
        $filter_array_id = array();
        $filter_array_value = array();
        $filter_array_between = array();


        foreach ($search_form->all() as $item) {
            if ($item->getData()) {
                //var_dump($item->getName(),$item->getData(),gettype($item->getData()));
                $item_name = $item->getName();
                $item_data = $item->getData();
                $var = @explode("_", $item_name);
                if ($var[0] == 'params') {
                    if (gettype($item_data)=='double') $price_array[$item_name] = $item_data;
                    if (gettype($item_data)=='integer') {
                        $var = @explode("_", $item_name);
                        //var_dump($var);
                        if (@$var[1]) {
                            $filter_array_id[$var[1]] = $item_data;
                        }
                    }
                    if (gettype($item_data)=='string') {
                        $var = @explode("_", $item_name);
                        $array = @explode("-", $item_data);
                        if (@$array[0] && @$array[1]) {
                            if (@$var[1]) {
                                $filter_array_between[$var[1]] = $array;
                            }
                        } elseif (@$array[0]!="" && @$array[1]!="") {
                            if (@$var[1]) {
                                $filter_array_value[$var[1]] = $item_data;
                            }
                        }
                    }
                    if (gettype($item_data)=='array') {
                        foreach ($item_data as $item_data_i) {
                            if (gettype($item_data_i)=='integer') {
                                $var = @explode("_", $item_name);
                                if (@$var[1]) {
                                    $filter_array_id[$var[1]][] = $item_data_i;
                                }
                            }
                            if (gettype($item_data_i)=='string') {
                                $var = @explode("_", $item_name);
                                $array = @explode("-", $item_data_i);
                                if (@$array[0] && @$array[1]) {
                                    if (@$var[1]) {
                                        $filter_array_between[$var[1]][] = $array;
                                    }
                                } elseif (@$array[0]!="" && @$array[1]!="") {
                                    if (@$var[1]) {
                                        $filter_array_value[$var[1]][] = $item_data_i;
                                    }
                                }
                            }
                        }
                    }
                } else if ($var[0] == 'search') {
                    $search_query = $item_data;
                } else {
                    if (gettype($item_data)=='string') {
                        $array = @explode("-", $item_data);
                        if (@$array[0]) {
                            $price_where = 'Objects.Price >= '. $array[0];
                        }
                        if (@$array[1]) {
                            $price_where .= ($price_where ? ' AND ' : '') .'Objects.Price <= '. $array[1];
                        }
                    } else {
                        $item_name = ucfirst(PropelInflector::camelize((string)$item_name));
                        if (is_array($item_data)) {
                            foreach ($item_data as $item_data_i) {
                                $filter_array[$item_name] = $item_data_i;
                            }
                        } else {
                            $filter_array[$item_name] = $item_data;
                        }
                    }
                }
            }
        }

        $catalog_query = ObjectsQuery::create('Objects');
        //$catalog_query->filterByForAll(true);
        $catalog_query->filterByPublished(true);
        $catalog_query->filterByModered(true);
        $catalog_query->filterByArray($filter_array);
        if ($price_where) $catalog_query->where($price_where);

        // Поиск
        if (!$search_query) $search_query = @$request->request->get('search_query')?:(@$request->query->get('search_query')?:NULL);
        if ($search_query) $catalog_query->filterByTitle('%' . $search_query . '%');

        $filterWhere = array();
        if ($filter_array_id) {
            foreach ($filter_array_id as $key => $filter_array_id_item) {
                if (is_array($filter_array_id_item)) {
                    $filterWhere[] = 'MAX(IF(Params.FieldId = '.$key.' AND Params.ValueId IN ('.implode(',', $filter_array_id_item).'), 1, 0)) = 1';
                } else {
                    $filterWhere[] = 'MAX(IF(Params.FieldId = '.$key.' AND Params.ValueId IN ('.$filter_array_id_item.'), 1, 0)) = 1';
                }
            }
        }
        if ($filter_array_value) {
            foreach ($filter_array_value as $key => $filter_array_id_item) {
                $filterWhere[] = 'MAX(IF(Params.FieldId = '.$key.' AND Params.TextValue IN ('.implode(',', $filter_array_value).'), 1, 0)) = 1';
            }
        }
        if ($filter_array_between) {
            foreach ($filter_array_between as $key => $filter_array_id_item) {
                $filterWhere[] = '(MAX(IF(Params.FieldId = '.$key.' AND Params.TextValue between '.$filter_array_id_item[0].' and '.$filter_array_id_item[1].', 1, 0)) = 1 OR MAX(IF(Params.FieldId = '.$key.' AND Values.Name between '.$filter_array_id_item[0].' and '.$filter_array_id_item[1].', 1, 0)) = 1)';
            }
        }

        if ($filterWhere){
            $objects_where = ObjectParamsQuery::create('Params')
                ->join('Params.ObjectTypesFieldsValues Values',Criteria::LEFT_JOIN)
                ->select('object_id')
                ->groupBy('object_id')
                ->having(implode(' AND ', $filterWhere))
                ->find();

            $catalog_query->where('Objects.Id IN ?', $objects_where);
        }
        
		// Сортировка
        $dir = @$request->query->get('dir') ?: (@$request->cookies->get('dir') ?: 'asc');
        $sort = @$request->query->get('sorting') ?: (@$request->cookies->get('sorting') ?: 'price');
        $on_page = @$request->query->get('on_page') ?: (@$request->cookies->get('on_page') ?: '12');
        $catalog_query->orderBy($sort, $dir);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $catalog_query,
            $this->get('request')->query->get('page', 1),
            $on_page
        );
		
		$categories = ObjectTypesQuery::create()            
            ->find();
			
		$category = null;
		if (@$request->query->get('type_object')) {
			$category = ObjectTypesQuery::create()            
            ->filterById(@$request->query->get('type_object'))->findOne();
		}

        $response = $this->render('SiteBundle:Default:catalog.html.twig', array(
            'settings'      => $settings,
            'menus'         => $menus,
            'catalog'       => $pagination,
            'category'		=> $category,
            'categories' 	=> $categories,
            'search_form'   => $search_form->createView(),
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
     * @Route("/catalog/{alias}")
     */
    public function aliasAction($alias, Request $request)
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
    }

    /**
     * @Route("/catalog/object/{id}", requirements={"id" = "\d+"})
     */
    public function objectAction($id, Request $request)
    {
        $settings = SettingsQuery::create()
            ->findOne();
        $menus = MenusQuery::create()
            ->filterByParentId(NULL)
            ->orderBySort()
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
        //$catalog_query->filterByForAll(true);
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
			
        return $this->render('SiteBundle:Default:object.html.twig', array(
            'settings'      => $settings,
            'menus'         => $menus,
			'categories' 	=> $categories,
			'catalog'		=> $catalog,
            'object'        => $object
        ));
    }

}
