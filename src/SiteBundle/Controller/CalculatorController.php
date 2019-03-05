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

class CalculatorController extends Controller
{

	/**
     * @Route("/calculator")
     */
    public function indexAction(Request $request)
    {        
		$settings = SettingsQuery::create()
            ->findOne();
        $menus = MenusQuery::create()
            ->filterByParentId(NULL)
            ->orderBySort()
            ->find();
		$categories = ObjectTypesQuery::create()            
            ->find();

        $response = $this->render('SiteBundle:Default:calculator.html.twig', array(
            'settings'      => $settings,
            'menus'         => $menus,
            'categories' 	=> $categories
        ));
        return $response;
    }

}
