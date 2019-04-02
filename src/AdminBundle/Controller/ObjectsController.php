<?php

namespace AdminBundle\Controller;

use AdminBundle\Form\FilterType;
use FOS\UserBundle\Propel\UserQuery;
use SiteBundle\Model\ObjectImages;
use SiteBundle\Model\ObjectImagesQuery;
use SiteBundle\Model\ObjectParams;
use SiteBundle\Model\ObjectParamsQuery;
use SiteBundle\Model\ObjectTypesFieldsQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SiteBundle\Model\Objects;
use \Criteria;
use SiteBundle\Model\ObjectsQuery;
use SiteBundle\Model\ObjectTypesQuery;
use AdminBundle\Form\ObjectsType;
use AdminBundle\Form\ObjectsAdminType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class ObjectsController extends Controller
{

	/**
     * @Route("/objects")
     */
    public function indexAction(Request $request)
    {

        $items_query = ObjectsQuery::create();

        // Сортировка
        $dir = @$request->query->get('dir') ?: (@$request->cookies->get('dir') ?: 'desc');
        $sort = @$request->query->get('sort') ?: (@$request->cookies->get('sort') ?: 'createdAt');
        $on_page = @$request->query->get('on_page') ?: (@$request->cookies->get('on_page') ?: '20');
        $items_query->orderByPublished('desc');
        $items_query->orderByModered('asc');
        $items_query->orderBy($sort, $dir);

        // Поиск
        if (@$request->query->get('query')) $items_query->filterByTitle('%' . $request->query->get('query') . '%')->_or()->filterByAddress('%' . $request->query->get('query') . '%');

        // Фильтр
        $filter_form = $this->createForm(new FilterType());
        $object_types = ObjectTypesQuery::create()->find()->toKeyValue('Id','Title');

        $filter_form
            ->add('TypeObject', 'choice', array(
                'empty_value' => '- все -',
                'choices' => $object_types,
                'label' => 'Тип объекта',
                'attr' => array('class' => 'form-control filter_change'),
                'multiple' => false,
                'required' => false
            ));
        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')){
            $users = UserQuery::create()->find()->toKeyValue('id', 'Username');
            $filter_form->add('UserId', 'choice', array(
                'empty_value' => '- все -',
                'choices' => $users,
                'label' => 'Специалист',
                'attr' => array('class' => 'form-control filter_change'),
                'multiple' => false,
                'required' => false
            ));
        } else {
            $user = $this->container->get('security.token_storage')->getToken()->getUser();
            $users = array($user->getId() => $user->getUsername());
            $filter_form->add('UserId', 'choice', array(
                'choices' => $users,
                'label' => 'Специалист',
                'attr' => array('class' => 'form-control'),
                'multiple' => false,
                'required' => true
            ));
        }

        $filter_form->handleRequest($request);
        if ($filter_form->isValid()) {
            $filter_fields = $filter_form->getData();
            $filter_array = array();
            foreach ($filter_fields as $name => $filter_field) {
                if ($filter_field) $filter_array[$name] = $filter_field;
            }
            $items_query->filterByArray($filter_array);
        }
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')){
            $items_query->filterByUserId($user->getId());
        }

        $paginator = $this->get('knp_paginator');
        $items = $paginator->paginate(
            $items_query,
            $this->get('request')->query->get('page', 1),
            $on_page
        );

        $response = $this->render('AdminBundle:Default:objects.html.twig', array(
            'items' => $items,
            'dir' => $dir,
            'sort' => $sort,
            'on_page' => $on_page,
            'filter_form' => $filter_form->createView(),
            'back' => $this->generateUrl('admin_default_index')
        ));
        $response->headers->setCookie(new Cookie('dir', $dir, time() + 3600 * 24 * 7, $this->generateUrl('admin_objects_index')));
        $response->headers->setCookie(new Cookie('sort', $sort, time() + 3600 * 24 * 7, $this->generateUrl('admin_objects_index')));
        $response->headers->setCookie(new Cookie('on_page', $on_page, time() + 3600 * 24 * 7, $this->generateUrl('admin_objects_index')));
        return $response;
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
            if ($item->getAddress() && $item->getTownId()) {
                $item->setCoordinates($this->get_coordinates('Россия, ' . $item->getTowns()->getTitle() . ', ' . $item->getAddress()));
                $item->setAddress($this->get_cool_address('Россия, ' . $item->getTowns()->getTitle() . ', ' . $item->getAddress()));
            }
            $item->save();
            if ($item->getTypeObject()) {
                $fields = ObjectTypesFieldsQuery::create()->filterByObjectTypeId($item->getTypeObject())->orderByType()->orderByName()->find();
                foreach ($fields as $field) {
                    if ($form['params_'. $field->getId()]->getData()) {
                        $param = ObjectParamsQuery::create()->filterByObjectId($item->getId())->filterByFieldId($field->getId())->findOne();
                        if (!$param) {
                            $param = new ObjectParams();
                            $param->setObjectId($item->getId());
                            $param->setFieldId($field->getId());
                        }
                        if ($field->getType() == 2 || $field->getType() == 4) {
                            $param->setValueId($form['params_'. $field->getId()]->getData());
                            $param->setTextValue(null);
                        } else {
                            $param->setTextValue($form['params_'. $field->getId()]->getData());
                            $param->setValueId(null);
                        }
                        $param->save();
                    }
                }
            }
            # генерируем Название
            $type_objects = $item->getObjectTypes();
            if ($type_objects->getGenerator()) {
                $nitem = ObjectsQuery::create()->findPk($item->getId());
                $generator = $type_objects->getGenerator();
                preg_match_all("/{(\d+)}/ix", $generator, $out, PREG_PATTERN_ORDER);
                foreach ($out[1] as $gvalue) {
                    $new_value = null;
                    $gid = (int)$gvalue;
                    $new_value = $nitem->getParams($gid, true);
                    $gpattern = "/{" . $gvalue . "\}/ix";
                    if ($new_value) {
                        $generator = preg_replace($gpattern, $new_value, $generator);
                    }
                }
                $nitem->setTitle($generator);
                $nitem->save();
            }
            $this->get('session')->getFlashBag()->add(
                'notice',
                '"'.$nitem->getTitle().'" успешно добавлено!'
            );
            return $this->redirect($this->generateUrl('admin_objects_images', array('id'=>$item->getId())));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'title' => 'Создание',
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
            if ($item->getAddress() && $item->getTownId()) {
                $item->setCoordinates($this->get_coordinates('Россия, ' . $item->getTowns()->getTitle() . ', ' . $item->getAddress()));
                $item->setAddress($this->get_cool_address('Россия, ' . $item->getTowns()->getTitle() . ', ' . $item->getAddress()));
            }
			$item->save();
            if ($item->getTypeObject()) {
                $fields = ObjectTypesFieldsQuery::create()->filterByObjectTypeId($item->getTypeObject())->orderByType()->orderByName()->find();
                foreach ($fields as $field) {
                    if ($form['params_'. $field->getId()]->getData()) {
                        $param = ObjectParamsQuery::create()->filterByObjectId($item->getId())->filterByFieldId($field->getId())->findOne();
                        if (!$param) {
                            $param = new ObjectParams();
                            $param->setObjectId($item->getId());
                            $param->setFieldId($field->getId());
                        }
                        if ($field->getType() == 2 || $field->getType() == 4) {
                            $param->setValueId($form['params_'. $field->getId()]->getData());
                            $param->setTextValue(null);
                        } else {
                            $param->setTextValue($form['params_'. $field->getId()]->getData());
                            $param->setValueId(null);
                        }
                        $param->save();
                    }
                }
            }
            # генерируем Название
            $type_objects = $item->getObjectTypes();
            if ($type_objects->getGenerator()) {
                $nitem = ObjectsQuery::create()->findPk($item->getId());
                $generator = $type_objects->getGenerator();
                preg_match_all("/{(\d+)}/ix", $generator, $out, PREG_PATTERN_ORDER);
                foreach ($out[1] as $gvalue) {
                    $new_value = null;
                    $gid = (int)$gvalue;
                    $new_value = $nitem->getParams($gid, true);
                    $gpattern = "/{" . $gvalue . "\}/ix";
                    if ($new_value) {
                        $generator = preg_replace($gpattern, $new_value, $generator);
                    }
                }
                $nitem->setTitle($generator);
                $nitem->save();
            }
            $this->get('session')->getFlashBag()->add(
                'notice',
                '"'.$nitem->getTitle().'" успешно сохранено!'
            );
            return $this->redirect($this->generateUrl('admin_objects_edit', array('id' => $id)));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'item' => $item,
            'title' => 'Редактирование',
            'form' 		=> $form->createView(),
            'object_layouts' => $item->getObjectTypes()->getLayouts(),
            'back' => $this->generateUrl('admin_objects_index')
        ));
    }
    
    /**
     * @Route("/objects/copy/{id}")
     */
    public function copyAction($id, Request $request)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $old_item = ObjectsQuery::create()
            ->filterById($id)
            ->findOne();
        $item = new Objects();
        $item->setDescription($old_item->getDescription());
        $item->setTownId($old_item->getTownId());
        $item->setAreaId($old_item->getAreaId());
        $item->setAddress($old_item->getAddress());
		$item->setCoordinates($old_item->getCoordinates());
        $item->setType($old_item->getType());
        $item->setTypeObject($old_item->getTypeObject());
        $item->setPrice($old_item->getPrice());
        $item->setPublished(false);
        $item->setModered(false);
        $item->setForAll(false);
        $item->setXml(false);
        $item->setUserId($user->getId());
        $item->setObjectParamss($old_item->getObjectParamss());

        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $form = $this->createForm(new ObjectsAdminType(), $item);
        } else {
            $form = $this->createForm(new ObjectsType(), $item);
        }
        $form->handleRequest($request);
        if ($form->isValid()) {
            if (!$item->getCoordinates() && $item->getAddress() && $item->getTownId()) $item->setCoordinates($this->get_coordinates('Россия, ' . $item->getTowns()->getTitle().', '.$item->getAddress()));
            $item->save();
            if ($item->getTypeObject()) {
                $fields = ObjectTypesFieldsQuery::create()->filterByObjectTypeId($item->getTypeObject())->orderByType()->orderByName()->find();
                foreach ($fields as $field) {
                    if ($form['params_'. $field->getId()]->getData()) {
                        $param = ObjectParamsQuery::create()->filterByObjectId($item->getId())->filterByFieldId($field->getId())->findOne();
                        if (!$param) {
                            $param = new ObjectParams();
                            $param->setObjectId($item->getId());
                            $param->setFieldId($field->getId());
                        }
                        if ($field->getType() == 2 || $field->getType() == 4) {
                            $param->setValueId($form['params_'. $field->getId()]->getData());
                            $param->setTextValue(null);
                        } else {
                            $param->setTextValue($form['params_'. $field->getId()]->getData());
                            $param->setValueId(null);
                        }
                        $param->save();
                    }
                }
                # генерируем Название
                $type_objects = $item->getObjectTypes();
                if ($type_objects->getGenerator()) {
                    $nitem = ObjectsQuery::create()->findPk($item->getId());
                    $generator = $type_objects->getGenerator();
                    preg_match_all("/{(\d+)}/ix", $generator, $out, PREG_PATTERN_ORDER);
                    foreach ($out[1] as $gvalue) {
                        $new_value = null;
                        $gid = (int)$gvalue;
                        $new_value = $nitem->getParams($gid, true);
                        $gpattern = "/{" . $gvalue . "\}/ix";
                        if ($new_value) {
                            $generator = preg_replace($gpattern, $new_value, $generator);
                        }
                    }
                    $nitem->setTitle($generator);
                    $nitem->save();
                }
            }
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Успешно сохранено!'
            );
            return $this->redirect($this->generateUrl('admin_objects_images', array('id'=>$item->getId())));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'title' => 'Копирование',
            'form' 		=> $form->createView()
        ));
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
        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $form = $this->createForm(new ObjectsAdminType(), $item);
        } else {
            $form = $this->createForm(new ObjectsType(), $item);
        }

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
        if ($request->files->get('path')) {
            foreach ($request->files->get('path') as $path) {
                $nimage = new ObjectImages();
                $dir = 'images/objects';
                $file_type = $path->getMimeType();
                $uniqid = uniqid();
                switch ($file_type) {
                    case 'image/png':
                        $Filename = $uniqid . '.png';
                        $Thumb = $uniqid . '_thumb.png';
                        break;
                    case 'image/jpeg':
                        $Filename = $uniqid . '.jpg';
                        $Thumb = $uniqid . '_thumb.jpg';
                        break;
                    case 'image/gif':
                        $Filename = $uniqid . '.gif';
                        $Thumb = $uniqid . '_thumb.gif';
                        break;
                    default:
                        $Filename = NULL;
                }
                if ($Filename) {
                    $path->move($dir, $Filename);
                    $image = new Image($dir . '/' . $Filename);
                    $image->overlay('images/watermark.png', 'bottom right',
                        .9, -5, -5);
                    $image->best_fit(1400, 1000);
                    $image->save($dir . '/' . $Filename);
                    $nimage->setPath($Filename);
                    $nimage->setObjectId($id);
                    $nimage->setAlt($item->getTitle());
                    $nimage->setTitle(@$request->request->get('title') ?: $item->getTitle());
                    if ($Thumb) {
                        $image->fit_to_width(400);
                        $image->save($dir . '/' . $Thumb);
                        $nimage->setThumb($Thumb);
                    }
                    $nimage->save();
                }
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
            //$real_address = $result->GeoObjectCollection->featureMember[0]->GeoObject->metaDataProperty->GeocoderMetaData->text;
            //if (@$result->GeoObjectCollection->featureMember[0]->GeoObject->metaDataProperty->GeocoderMetaData->Address->formatted)
            //    $real_address = $result->GeoObjectCollection->featureMember[0]->GeoObject->metaDataProperty->GeocoderMetaData->Address->formatted;
            $coord = $result->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos;
            $array_coord = str_replace(' ',',', $coord);
            return $array_coord;
        }
        return false;
    }

    // Получение координат из Яндекса по адресу
    function get_cool_address($address)
    {
        $urlXml = "https://geocode-maps.yandex.ru/1.x/?geocode=" . urlencode($address);
        $result = @simplexml_load_file($urlXml);
        if ($result) {
            return $result->GeoObjectCollection->featureMember[0]->GeoObject->name;
        }
        return false;
    }
}
