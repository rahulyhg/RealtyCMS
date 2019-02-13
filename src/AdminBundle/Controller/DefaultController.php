<?php

namespace AdminBundle\Controller;

use SiteBundle\Model\TemplatesQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SiteBundle\Model\Settings;
use SiteBundle\Model\SettingsQuery;
use SiteBundle\Model\PagesQuery;
use SiteBundle\Model\ObjectsQuery;
use SiteBundle\Model\MessagesQuery;
use AdminBundle\Form\SettingsType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction(Request $request)
    {
        $id = @$request->request->get('id')?:NULL;
        if ($id) {
            $message = MessagesQuery::create()->findPk($id);
            if ($message) $message->setStatus(2)->save();
        }
        return $this->render('AdminBundle:Default:index.html.twig',array(
            'pages' => PagesQuery::create()->find()->count(),            
            'objects' => ObjectsQuery::create()->find()->count(),
            'messages' => MessagesQuery::create()->filterByStatus(1)->find()
        ));		
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
        if ($form->isValid()) {
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
        
        if ($param) {
          if ($param == 'ver') $process = new Process('php -v');
          if ($param == 'assets') $process = new Process('php '.$this->get('kernel')->getRootDir().'/console assetic:dump --env=prod');
          if ($param == 'cache') $process = new Process('php '.$this->get('kernel')->getRootDir().'/console cache:clear --env=prod');
		  if ($param == 'clear') $process = new Process('rm -r '.$this->get('kernel')->getRootDir().'/cache/prod');
          if ($param == 'sitemap') $process = new Process('php '.$this->get('kernel')->getRootDir().'/console presta:sitemap:dump ../web/');
          if ($param == 'propel') {
            $process = new Process('php '.$this->get('kernel')->getRootDir().'/console propel:mi:ge');
            $process->run();
            if (!$process->getErrorOutput()) {
              if ($process->getOutput()) $this->get('session')->getFlashBag()->add(
                'notice',
                $process->getOutput()
              );
              $process = new Process('php '.$this->get('kernel')->getRootDir().'/console propel:mi:mi');
              $process->run();
              if (!$process->getErrorOutput()) {
                if ($process->getOutput()) $this->get('session')->getFlashBag()->add(
                  'notice',
                  $process->getOutput()
                );
                $process = new Process('php '.$this->get('kernel')->getRootDir().'/console propel:bu');
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
            $from = 'Свои Метры <no-reply@svoi-metry.ru>';
            $to = $query['email'];//'info@rr.ru';
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
     * @Route("/del_mess")
     */
    public function delMessAction(Request $request)
    {
        $id = @$request->query->get('id')?:NULL;
        if ($id) {
            $message = MessagesQuery::create()->findPk($id);
            if ($message) $message->setStatus(2)->save();
        }
        return $this->redirect($this->generateUrl('admin_default_index'));
    }

}
