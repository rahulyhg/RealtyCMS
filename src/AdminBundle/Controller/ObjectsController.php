<?php

namespace AdminBundle\Controller;

use SiteBundle\Model\ObjectImages;
use SiteBundle\Model\ObjectImagesQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SiteBundle\Model\Objects;
use SiteBundle\Model\ObjectTypes;
use \Criteria;
use SiteBundle\Model\ObjectsQuery;
use SiteBundle\Model\ObjectTypesQuery;
use AdminBundle\Form\ObjectTypesType;
use AdminBundle\Form\ObjectsType;
use AdminBundle\Form\ObjectsAdminType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class ObjectsController extends Controller
{

    /**
     * @Route("/object_types")
     */
    public function indexTypesAction()
    {
        $items = ObjectTypesQuery::create()            
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

        return $this->render('AdminBundle:Default:object_types.html.twig',array(
            'pagination' 		=> $pagination
        ));
    }

    /**
 * @Route("/object_types/add")
 */
    public function addTypesAction(Request $request)
    {
        $item = new ObjectTypes();

        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $form = $this->createForm(new ObjectTypesType(), $item);
        }
        $form->handleRequest($request);
        if ($form->isValid()) {
            if ($form['image']->getData()) {
                $dir = 'images/cates';
                $file_type = $form['image']->getData()->getMimeType();
                switch($file_type) {
                    case 'image/png': $Filename = uniqid().'.png'; break;
                    case 'image/jpeg': $Filename = uniqid().'.jpg'; break;
                    case 'image/gif': $Filename = uniqid().'.gif'; break;
                    default: $Filename = NULL;
                }
                if ($Filename) {
                    $form['image']->getData()->move($dir, $Filename);
                    $item->setImage($Filename);
                    $image = new Image($dir.'/'.$Filename);
                    $image->save($dir.'/'.$Filename);
                }
            }
			$item->save();
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Успешно добавлено!'
            );
            return $this->redirect($this->generateUrl('admin_objects_indextypes'));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'form' 		=> $form->createView()
        ));
    }

    /**
     * @Route("/object_types/edit/{id}")
     */
    public function editTypesAction($id, Request $request)
    {
        $item = ObjectTypesQuery::create()
            ->filterById($id)
            ->findOne();
			
		$dir = 'images/cates';
		$oldimage = $item->getImage();
        $item->setImage(NULL);

        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {            
            $form = $this->createForm(new ObjectTypesType(), $item);
        }
        $form->handleRequest($request);
        if ($form->isValid()) {            
            if ($form['image']->getData()) {                
                $file_type = $form['image']->getData()->getMimeType();
                switch($file_type) {
                    case 'image/png': $Filename = uniqid().'.png'; break;
                    case 'image/jpeg': $Filename = uniqid().'.jpg'; break;
                    case 'image/gif': $Filename = uniqid().'.gif'; break;
                    default: $Filename = NULL;
                }
                if ($Filename) {
                    $form['image']->getData()->move($dir, $Filename);
                    $item->setImage($Filename);
                    $image = new Image($dir.'/'.$Filename);
                    $image->save($dir.'/'.$Filename);
					if ($oldimage) {
                        $fs = new Filesystem();
                        try {
                            $fs->remove( $dir.'/'.$oldimage );
                        } catch (IOExceptionInterface $e) {
                            echo "Ошибка удаления изображения";
                        }
                    }
                }
            } else {
                $item->setImage($oldimage);
            }			
			$item->save();
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Успешно сохранено!'
            );
            return $this->redirect($this->generateUrl('admin_objects_indextypes'));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'form' 		=> $form->createView(),
			'photo'     => $oldimage ? '/'.$dir.'/'.$oldimage : null
        ));
    }
	
	/**
     * @Route("/object_types/delete/{id}")
     */
    public function deleteTypesAction($id)
    {
        $item = ObjectTypesQuery::create()
            ->filterById($id)
            ->findOne();

        if ($item) {
            if ($item->getImage()) {
                $fs = new Filesystem();
                try {
                    $fs->remove( 'images/cates/'.$item->getImage() );
                } catch (IOExceptionInterface $e) {
                    echo "Ошибка удаления изображения";
                }
            }
			$item->delete();
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Успешно удалено!'
            );
        }
        return $this->redirect($this->generateUrl('admin_objects_indextypes'));
    }
	
	/**
     * @Route("/objects")
     */
    public function indexAction()
    {
        $items = ObjectsQuery::create()
            ->orderByPublished('desc')
            ->orderByModered()            
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

        return $this->render('AdminBundle:Default:objects.html.twig',array(
            'pagination' 		=> $pagination
        ));
    }

    /**
 * @Route("/objects/add")
 */
    public function addAction(Request $request)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $item = new Objects();
        $item->setUserId($user->getId());

        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $form = $this->createForm(new ObjectsAdminType(), $item);
        } else {
            $form = $this->createForm(new ObjectsType(), $item);
        }
        $form->handleRequest($request);
        if ($form->isValid()) {
            # Считаем цену за м²
            $item->setSqPrice((int)$item->getPrice()/$item->getSquare());
            if (!$item->getCoordinates() && $item->getAddress() && $item->getTownId()) $item->setCoordinates($this->get_coordinates('Россия, ' . $item->getTowns()->getTitle().', '.$item->getAddress()));
			$item->save();
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Успешно добавлено!'
            );
            return $this->redirect($this->generateUrl('admin_objects_images', array('id'=>$item->getId())));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'form' 		=> $form->createView()
        ));
    }

    /**
     * @Route("/objects/edit/{id}")
     */
    public function editAction($id, Request $request)
    {
        $item = ObjectsQuery::create()
            ->filterById($id)
            ->findOne();

        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $form = $this->createForm(new ObjectsAdminType(), $item);
        } else {
            $form = $this->createForm(new ObjectsType(), $item);
        }
        $form->handleRequest($request);
        if ($form->isValid()) {
            # Считаем цену за м²
            $item->setSqPrice((int)$item->getPrice()/$item->getSquare());
            if (!$item->getCoordinates() && $item->getAddress() && $item->getTownId()) $item->setCoordinates($this->get_coordinates('Россия, ' . $item->getTowns()->getTitle().', '.$item->getAddress()));
			$item->save();
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Успешно сохранено!'
            );
            return $this->redirect($this->generateUrl('admin_objects_index'));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'form' 		=> $form->createView()
        ));
    }
    
    /**
     * @Route("/objects/copy/{id}")
     */
    public function copyAction($id, Request $request)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $item = ObjectsQuery::create()
            ->filterById($id)
            ->findOne();
        $new_item = new Objects();        
        $new_item->setTitle($item->getTitle().'_копия');
        $new_item->setDescription($item->getDescription());
        $new_item->setTownId($item->getTownId());
        $new_item->setAreaId($item->getAreaId());
        $new_item->setAddress($item->getAddress());
		$new_item->setCoordinates($item->getCoordinates());
        $new_item->setType($item->getType());
        $new_item->setTypeObject($item->getTypeObject());
        $new_item->setPrice($item->getPrice());
        $new_item->setSquare($item->getSquare());
        $new_item->setSqPrice($item->getSqPrice());        
        $new_item->setPublished(false);
        $new_item->setModered(false);
        $new_item->setForAll(false);
        $new_item->setXml(false);
        $new_item->setUserId($user->getId());
        $new_item->save();        

        return $this->redirect($this->generateUrl('admin_objects_edit', array('id'=>$new_item->getId())));
    }

    /**
     * @Route("/objects/update")
     */
    public function updateAction(Request $request)
    {
        $req = $request->request->all();
        $object = $req['objects'];
        if (@$object['id']) {
            $item = ObjectsQuery::create()
                ->filterById($object['id'])
                ->findOne();
        } else {
            $item = new Objects();
        }
        if (@$object['town_id']) {
            $item->setTownId($object['town_id']);
            $item->setAreaId(NULL);
        }

        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $form = $this->createForm(new ObjectsAdminType(), $item);
        } else {
            $form = $this->createForm(new ObjectsType(), $item);
        }
        $form->handleRequest($request);

        return $this->render('AdminBundle:Form:update.html.twig',
            array('form' => $form->createView()));
    }

    /**
     * @Route("/objects/delete/{id}")
     */
    public function deleteAction($id)
    {
        $item = ObjectsQuery::create()
            ->filterById($id)
            ->findOne();

        if ($item) {
            $item->delete();
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Успешно удалено!'
            );
        }
        return $this->redirect($this->generateUrl('admin_objects_index'));
    }

    /**
     * @Route("/objects/images/sort/{id}")
     */
    public function sortAction($id, Request $request)
    {
        $req = $request->request->all();
        foreach ($req['array'] as $key=>$image_id) {
            $img = ObjectImagesQuery::create()
                ->filterById($image_id)
                ->findOne();
            $img->setSrt($key);
            $img->save();
        }

        $items = ObjectsQuery::create()
            ->filterById($id)
            ->joinWith('ObjectImages', Criteria::LEFT_JOIN)
            ->orderBy('ObjectImages.srt', 'asc')
            ->find();

        return $this->render('AdminBundle:Default:images.html.twig',array(
            'item' 		=> $items[0]
        ));
    }

    /**
     * @Route("/objects/images/{id}")
     */
    public function imagesAction($id, Request $request)
    {
        $items = ObjectsQuery::create()
            ->filterById($id)
            ->joinWith('ObjectImages', Criteria::LEFT_JOIN)
            ->orderBy('ObjectImages.srt', 'asc')
            ->find();

        return $this->render('AdminBundle:Default:images.html.twig',array(
            'item' 		=> $items[0]
        ));
    }

    /**
     * @Route("/objects/imgadd/{id}")
     */
    public function imgAddAction($id, Request $request)
    {
        $item = ObjectsQuery::create()
            ->filterById($id)
            ->findOne();
        $nimage = new ObjectImages();
        if ($request->files->get('path')) {
            $dir = 'images/objects';
            $file_type = $request->files->get('path')->getMimeType();
            $uniqid = uniqid();
            switch($file_type) {
                case 'image/png': $Filename = $uniqid.'.png';$Thumb = $uniqid.'_thumb.png'; break;
                case 'image/jpeg': $Filename = $uniqid.'.jpg';$Thumb = $uniqid.'_thumb.jpg'; break;
                case 'image/gif': $Filename = $uniqid.'.gif';$Thumb = $uniqid.'_thumb.gif'; break;
                default: $Filename = NULL;
            }
            if ($Filename) {
                $request->files->get('path')->move($dir, $Filename);
                $image = new Image($dir.'/'.$Filename);
                $image->save($dir.'/'.$Filename);
                $nimage->setPath($Filename);
                $nimage->setObjectId($id);
                $nimage->setAlt($item->getTitle());
                $nimage->setTitle(@$request->request->get('title')?:$item->getTitle());
                if ($Thumb) {
                    $image->fit_to_width(400);
                    $image->save($dir . '/' . $Thumb);
                    $nimage->setThumb($Thumb);
                }
                $nimage->save();
            }
        }

        return $this->render('AdminBundle:Default:images.html.twig',array(
            'item' 		=> $item
        ));
    }

    /**
     * @Route("/objects/imgedit")
     */
    public function imgeditAction(Request $request)
    {
        $id = $request->request->get('id');
        $item = ObjectImagesQuery::create()
            ->filterById($id)
            ->findOne();
        $title = $request->request->get('title');
        var_dump($title);

        if ($item && $title) {
            $item->setTitle($title);
            $item->save();
        }
        return $this->render('AdminBundle:Default:empty.html.twig');
    }
    
    /**
     * @Route("/objects/delimg")
     */
    public function delimgAction(Request $request)
    {
        $id = $request->request->get('id');
        $item = ObjectImagesQuery::create()
            ->filterById($id)
            ->findOne();
        $object_id = $item->getObjectId();

        if ($item) {
            if ($item->getPath()) {
                $fs = new Filesystem();
                try {
                    $fs->remove( 'images/objects/'.$item->getPath() );
                } catch (IOExceptionInterface $e) {
                    echo "Ошибка удаления изображения";
                }
            }
            $item->delete();
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Успешно удалено!'
            );
        }
        return $this->redirect($this->generateUrl('admin_objects_images',['id'=>$object_id]));
    }
	
	// Получение координат из Яндекса по адресу
    function get_coordinates($address)
    {
        $urlXml = "https://geocode-maps.yandex.ru/1.x/?geocode=" . urlencode($address);
        $result = @simplexml_load_file($urlXml);
        if ($result) {
            $real_address = $result->GeoObjectCollection->featureMember[0]->GeoObject->metaDataProperty->GeocoderMetaData->text;
            if (@$result->GeoObjectCollection->featureMember[0]->GeoObject->metaDataProperty->GeocoderMetaData->Address->formatted)
                $real_address = $result->GeoObjectCollection->featureMember[0]->GeoObject->metaDataProperty->GeocoderMetaData->Address->formatted;
            $coord = $result->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos;
            $array_coord = str_replace(' ',',', $coord);
            return $array_coord;
        }
        return false;
    }
}
