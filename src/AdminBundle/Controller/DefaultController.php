<?php

namespace AdminBundle\Controller;

use AdminBundle\Form\FilterType;
use FOS\UserBundle\Propel\UserQuery;
use SiteBundle\Model\TemplatesQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SiteBundle\Model\Settings;
use SiteBundle\Model\SettingsQuery;
use SiteBundle\Model\PagesQuery;
use SiteBundle\Model\ObjectsQuery;
use SiteBundle\Model\MessagesQuery;
use AdminBundle\Form\SettingsType;
use AdminBundle\Controller\Image;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Process\Process;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction(Request $request)
    {

        $status = @$request->query->get('status') ?: (@$request->cookies->get('status') ?: '1');
        $on_page = @$request->query->get('on_page') ?: (@$request->cookies->get('on_page') ?: '20');
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        $messages_query = MessagesQuery::create();

        // Поиск
        if (@$request->query->get('query')) $messages_query->filterByName('%' . $request->query->get('query') . '%');
        // Фильтр
        $filter_form = $this->createForm(new FilterType());

        $filter_form
            ->add('Status', 'choice', array(
                'choices' => array('1'=>'Новые','2'=>'Отказ','3'=>'Успех'),
                'label' => 'Статус сообщения',
                'attr' => array('class' => 'form-control filter_change'),
                'multiple' => false,
                'required' => true
            ));
        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')){
            $users = UserQuery::create()->find()->toKeyValue('id', 'Username');
            $filter_form->add('UserId', 'choice', array(
                'empty_value' => '- все -',
                'choices' => $users,
                'label' => 'Менеджер',
                'attr' => array('class' => 'form-control filter_change'),
                'multiple' => false,
                'required' => false
            ));
        } else {
            $users = array($user->getId() => $user->getUsername());
            $filter_form->add('UserId', 'choice', array(
                'choices' => $users,
                'label' => 'Менеджер',
                'attr' => array('class' => 'form-control'),
                'multiple' => false,
                'required' => true
            ));
        }

        $filter_form->handleRequest($request);
        $filter_array = array();
        if ($filter_form->isValid()) {
            $filter_fields = $filter_form->getData();
            foreach ($filter_fields as $name => $filter_field) {
                if ($filter_field) $filter_array[$name] = $filter_field;
            }
        }

        if (!@$filter_array['Status']) $filter_array['Status'] = 1;

        $messages_query->filterByArray($filter_array);
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')){
            $messages_query->filterByUserId($user->getId());
                //->_or()->filterByUserId(null, \Criteria::ISNULL);
        }
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') && !$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')){
            $messages_query->filterByUserId($user->getId())->_or()->filterByUserId(null, \Criteria::ISNULL);
        }

        $paginator = $this->get('knp_paginator');
        $items = $paginator->paginate(
            $messages_query,
            $this->get('request')->query->get('page', 1),
            $on_page
        );

        $response = $this->render('AdminBundle:Default:index.html.twig',array(
            'pages' => PagesQuery::create()->find()->count(),
            'objects' => ObjectsQuery::create()->find()->count(),
            'messages' => $items,
            'on_page' => $on_page,
            'filter_form' => $filter_form->createView(),
        ));
        $response->headers->setCookie(new Cookie('status', $status, time() + 3600 * 24 * 7, $this->generateUrl('admin_default_index')));
        $response->headers->setCookie(new Cookie('on_page', $on_page, time() + 3600 * 24 * 7, $this->generateUrl('admin_default_index')));
        return $response;
    }

    /**
     * @Route("/settings")
     */
    public function settingsAction(Request $request)
    {
        $settings = SettingsQuery::create()
            ->findOne();
        if (!$settings) $settings = new Settings();

        $form = $this->createForm(new SettingsType(), $settings);
        $form->handleRequest($request);
        $dir = 'images';
        if ($form->isValid()) {
            if ($form['favicon']->getData()) {
                $file_type = $form['favicon']->getData()->getMimeType();
                if ($file_type == 'image/x-icon') {
                    $form['favicon']->getData()->move($this->getParameter('assetic.write_to'), 'favicon.ico');
                }
            }
            if ($form['logo_top']->getData()) {
                $file_type = $form['logo_top']->getData()->getMimeType();
                switch($file_type) {
                    case 'image/png': $Filename = 'logo_top.png'; break;
                    default: $Filename = NULL;
                }
                if ($Filename) {
                    $form['logo_top']->getData()->move($dir, $Filename);
                    $logo_image = new Image($dir . '/' . $Filename);
                    $logo_image->fit_to_height(100);
                    $logo_image->save($dir . '/' . $Filename);

                }
            }
            if ($form['logo_bottom']->getData()) {
                $file_type = $form['logo_bottom']->getData()->getMimeType();
                switch($file_type) {
                    case 'image/png': $Filename = 'logo_bottom.png'; break;
                    default: $Filename = NULL;
                }
                if ($Filename) {
                    $form['logo_bottom']->getData()->move($dir, $Filename);
                    $logo_image = new Image($dir . '/' . $Filename);
                    $watermark = new Image($dir . '/' . $Filename);
                    $logo_image->fit_to_width(300);
                    $logo_image->save($dir . '/' . $Filename);
                    $watermark->fit_to_width(100);
                    $watermark->save($dir . '/watermark.png');
                }
            }
            $settings->save();
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Настройки успешно сохранены!'
            );
        }

        return $this->render('AdminBundle:Default:settings.html.twig', array(
            'form' => $form->createView()
        ));
    }
	
	/**
     * @Route("/run/{param}")
     */
    public function runAction($param = NULL)
    {

        $settings = SettingsQuery::create()
            ->findOne();

        $php_path = $settings->getPhpPath() ? : 'php';

        if ($param) {
          if ($param == 'ver') $process = new Process($php_path.' -v');
          if ($param == 'assets') $process = new Process($php_path.' '.$this->get('kernel')->getRootDir().'/console assetic:dump --env=prod');
          if ($param == 'cache') $process = new Process($php_path.' '.$this->get('kernel')->getRootDir().'/console cache:clear --env=prod');
		  if ($param == 'clear') $process = new Process('rm -r '.$this->get('kernel')->getRootDir().'/cache/prod');
          if ($param == 'sitemap') $process = new Process($php_path.' '.$this->get('kernel')->getRootDir().'/console presta:sitemap:dump ../web/');
          if ($param == 'propel') {
            $process = new Process($php_path.' '.$this->get('kernel')->getRootDir().'/console propel:mi:ge');
            $process->run();
            if (!$process->getErrorOutput()) {
              if ($process->getOutput()) $this->get('session')->getFlashBag()->add(
                'notice',
                $process->getOutput()
              );
              $process = new Process($php_path.' '.$this->get('kernel')->getRootDir().'/console propel:mi:mi');
              $process->run();
              if (!$process->getErrorOutput()) {
                if ($process->getOutput()) $this->get('session')->getFlashBag()->add(
                  'notice',
                  $process->getOutput()
                );
                $process = new Process($php_path.' '.$this->get('kernel')->getRootDir().'/console propel:bu');
                $process->run();
              }
            }
          } else {
            $process->run();
          }          
          if ($process->getErrorOutput()) $this->get('session')->getFlashBag()->add(
            'alert',
            $process->getErrorOutput()
          );
          if (!$process->getErrorOutput() && $process->getOutput()) $this->get('session')->getFlashBag()->add(
            'notice',
            $process->getOutput()
          );
        }
        
        return $this->redirect($this->generateUrl('admin_default_index'));
    }

    /**
     * @Route("/delivery")
     */
    public function deliveryAction(Request $request)
    {
        $settings = SettingsQuery::create()
            ->findOne();

        $items = TemplatesQuery::create()
            ->find();
        $array = [];
        foreach ($items as $item) {
            $array[$item->getId()] = $item->getName();
        }

        $form = $this->createFormBuilder()
            ->add('client', 'text', array('label' => 'Имя'))
            ->add('email', 'email', array('label' => 'Email'))
            ->add('template', 'choice', array(
                'choices'   => $array,
                'label'  => 'Шаблон письма',
                'required' => TRUE
            ))
            ->add('save', 'submit', array('label' => 'Отправить'))
            ->getForm();

        $query = @$request->request->all()['form'];

        $form->handleRequest($request);

        if ($form->isValid()) {
            $template = TemplatesQuery::create()
                ->filterById($query['template'])
                ->findOne();
            $content = preg_replace("/{name}/ix", $query['client'], $template->getDescription());
            $user = $this->container->get('security.context')->getToken()->getUser();

            $subject = 'Коммерческое предложение';
            $from = $settings->getName().' <'.$settings->getEmail().'>';
            $to = $query['email'];
            $body = $this->renderView(
                'AdminBundle:Form:mail.html.twig',
                array('content' => $content, 'email' => $to, 'agent' => $user )
            );
            if ($this->get('mail_helper')->sendMailing($from, $to, $subject, $body)) {
                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Успешно отправлено на '.$to
                );
                return $this->redirect($this->generateUrl('admin_default_delivery'));
            }
        }

        return $this->render('AdminBundle:Default:delivery.html.twig', array(
            'form' => $form->createView()
        ));
    }
	
	/**
     * @Route("/delete_message/{id}")
     */
    public function deleteMessageAction($id, Request $request)
    {
        $comment = @$request->request->get('comment')?:NULL;
        if ($id && $comment) {
            $message = MessagesQuery::create()->findPk($id);
            if ($message) {
                if (!$message->getUserId()) {
                    $user = $this->container->get('security.token_storage')->getToken()->getUser();
                    $message->setUserId($user->getId());
                }
                $message->setStatus(2)->setComment($comment)->save();
            }
        }
        return $this->redirect($this->generateUrl('admin_default_index'));
    }

    /**
     * @Route("/complete_message/{id}")
     */
    public function completeMessageAction($id, Request $request)
    {
        $comment = @$request->request->get('comment')?:NULL;
        if ($id && $comment) {
            $message = MessagesQuery::create()->findPk($id);
            if ($message) {
                if (!$message->getUserId()) {
                    $user = $this->container->get('security.token_storage')->getToken()->getUser();
                    $message->setUserId($user->getId());
                }
                $message->setStatus(3)->setComment($comment)->save();
            }
        }
        return $this->redirect($this->generateUrl('admin_default_index'));
    }

}
