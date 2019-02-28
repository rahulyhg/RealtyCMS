<?php

namespace SiteBundle\Controller;

use SiteBundle\Form\SearchType;
use SiteBundle\Model\Messages;
use SiteBundle\Model\ObjectsQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SiteBundle\Model\SettingsQuery;
use SiteBundle\Model\MenusQuery;
use SiteBundle\Model\SlidersQuery;
use SiteBundle\Model\ObjectTypesQuery;
use SiteBundle\Model\PagesQuery;
use FOS\UserBundle\Propel\UserQuery;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction(Request $request)
    {
        $settings = SettingsQuery::create()
            ->findOne();
        $menus = MenusQuery::create()
            ->find();
        $sliders = SlidersQuery::create()
            ->find();
        $nsliders = [];
        foreach($sliders as $slider) {
            $nsliders[$slider->getTitle()] = $slider;
        }
        $consultants = UserQuery::create()
            ->filterByRoles(array('role' => 'ROLE_AGENT'))
            ->find();
        $search_form = $this->createForm(new SearchType());
		
		$categories = ObjectTypesQuery::create()            
            ->find();

        return $this->render('SiteBundle:Default:index.html.twig', array(
            'settings'      => $settings,
            'menus'         => $menus,
            'sliders'       => $nsliders,
            'consultants'   => $consultants,
			'categories' 	=> $categories,
            'search_form'   => $search_form->createView()
        ));
    }

    /**
     * @Route("/post")
     */
    public function postAction(Request $request)
    {
        $settings = SettingsQuery::create()
            ->findOne();

        $name = @$request->request->get('name')?:NULL;
        $phone = @$request->request->get('phone')?:NULL;
        $object_id = @$request->request->get('object')?:NULL;
        if ($object_id) {
            $object = ObjectsQuery::create()
                ->findOneById($object_id);
        } else $object = NULL;
        $consultant_id = @$request->request->get('agent')?:NULL;
        if ($consultant_id) {
            $consultant = UserQuery::create()
                ->findOneById($consultant_id);
        } else $consultant = NULL;

        # Отсылаем сообщение
        $subject = 'Заявка с сайта '.$_SERVER['SERVER_NAME'];
        $from = $settings->getName().' <no-reply@'.$_SERVER['SERVER_NAME'].'>';

        $to = $settings->getEmail();
        $body = $this->renderView(
            'SiteBundle:Default:mail.html.twig',
            array('name' => $name, 'phone' => $phone, 'site' => $_SERVER['SERVER_NAME'], 'object' => $object, 'consultant' => $consultant)
        );
        $this->get('mail_helper')->sendMailing($from, $to, $subject, $body);
		if ($consultant) {
			$this->get('mail_helper')->sendMailing($from, $consultant->getEmail(), $subject, $body);
		}
        $message = new Messages();
        $message->setName($name);
        $message->setPhone($phone);
        $message->setObjectId($object?$object->getId():NULL);
        $message->setUserId($consultant?$consultant->getId():NULL);
        $message->setStatus(1);
        $message->save();

        return $this->render('SiteBundle:Default:post.html.twig', array(
            'name'  => $name
        ));
    }

    /**
     * @Route("/search")
     */
    public function searchAction(Request $request)
    {
        $search = @$request->request->get('search')?:(@$request->query->get('search')?:NULL);

        if ($search) {
            $page = PagesQuery::create()
                ->filterByTitle('%' . $search . '%')
                ->findOne();
            if ($page) return $this->redirect($this->generateUrl('site_default_page', array('alias' => $page->getAlias())));
            return $this->redirect($this->generateUrl('site_catalog_index', array('search_query' => $search)));
        }
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/{alias}")
     */
    public function pageAction($alias)
    {
        $settings = SettingsQuery::create()
            ->findOne();
        $menus = MenusQuery::create()
            ->find();
        $page = PagesQuery::create()
            ->filterByAlias($alias)
            ->findOne();
		$categories = ObjectTypesQuery::create()            
            ->find();
        return $this->render('SiteBundle:Default:page.html.twig', array(
            'settings'  => $settings,
            'menus'     => $menus,
			'categories' 	=> $categories,
            'page'      => $page
        ));
    }

}
