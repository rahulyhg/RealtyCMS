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

class AgentController extends Controller
{

	/**
     * @Route("/agents")
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
            ->orderBySort()
            ->find();
			
		$agents = UserQuery::create()
            ->filterByRole('ROLE_AGENT')
            ->find();

        $response = $this->render('SiteBundle:Default:agents.html.twig', array(
            'settings'      => $settings,
            'menus'         => $menus,
            'agents'        => $agents,
            'categories' 	=> $categories
        ));
        return $response;
    }

    /**
     * @Route("/agent/{id}")
     */
    public function agentAction($id, Request $request)
    {
        $settings = SettingsQuery::create()
            ->findOne();
        $menus = MenusQuery::create()
            ->filterByParentId(NULL)
            ->orderBySort()
            ->find();

        $categories = ObjectTypesQuery::create()
            ->orderBySort()
            ->find();

        $agent = UserQuery::create()
            ->findPk($id);

        $published_objects = ObjectsQuery::create()->filterByUserId($id)->filterByPublished(true)->find();
        $not_published_objects = ObjectsQuery::create()->filterByUserId($id)->filterByPublished(false)->find();

        $response = $this->render('SiteBundle:Default:agent.html.twig', array(
            'settings'      => $settings,
            'menus'         => $menus,
            'agent'         => $agent,
            'categories' 	=> $categories,
            'published_objects' => $published_objects,
            'not_published_objects' => $not_published_objects
        ));
        return $response;
    }

}
