<?php

namespace AdminBundle\Controller;

use AdminBundle\Form\ObjectTypesFieldsType;
use AdminBundle\Form\ObjectTypesFieldsValuessType;
use AdminBundle\Form\ObjectTypesFieldsValuesType;
use SiteBundle\Model\ObjectTypesFields;
use SiteBundle\Model\ObjectTypesFieldsQuery;
use SiteBundle\Model\ObjectTypesFieldsValues;
use SiteBundle\Model\ObjectTypesFieldsValuesQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SiteBundle\Model\ObjectTypes;
use SiteBundle\Model\ObjectTypesQuery;
use AdminBundle\Form\ObjectTypesType;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class ObjectsTypesController extends Controller
{

    /**
     * @Route("/object_types")
     */
    public function indexTypesAction()
    {
        $items = ObjectTypesQuery::create()            
            ->orderBySort()
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
            'pagination' 		=> $pagination,
            'back' => $this->generateUrl('admin_default_index')
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
                    $image->fit_to_width(600);
                    $image->save($dir.'/'.$Filename);
                }
            }
			$item->save();
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Успешно добавлено!'
            );
            return $this->redirect($this->generateUrl('admin_objectstypes_edittypes', array('id'=> $item->getId())));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'title' => 'Создание',
            'form' 		=> $form->createView(),
            'back' => $this->generateUrl('admin_objectstypes_indextypes')
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
                    $image->fit_to_width(600);
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
            //return $this->redirect($this->generateUrl('admin_objectstypes_edittypes', array('id'=> $item->getId())));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'title' => 'Редактирование',
            'item'      => $item,
            'form' 		=> $form->createView(),
			'photo'     => $oldimage ? '/'.$dir.'/'.$oldimage : null,
            'object_types_fields' => true,
            'layouts' => $item->getLayouts(),
            'back' => $this->generateUrl('admin_objectstypes_indextypes')
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
        return $this->redirect($this->generateUrl('admin_objectstypes_indextypes'));
    }

    /**
     * @Route("/object_types/sort")
     */
    public function sortAction(Request $request)
    {

        $array = $request->request->get('array');
        $cnt = 1;
        foreach ($array as $item) {
            $object_type_item = ObjectTypesQuery::create()->findPk($item);
            $object_type_item->setSort($cnt++);
            $object_type_item->save();
        }
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/object_types/{id}/add_field")
     */
    public function addFieldAction($id, Request $request)
    {

        $field = new ObjectTypesFields();
        $field->setObjectTypeId($id);

        $form = $this->createForm(new ObjectTypesFieldsType(), $field);

        $form->handleRequest($request);

        if ($form->isValid()) {

            if (!$field->getSort()) {
                $all_cnt = ObjectTypesFieldsQuery::create()->filterByObjectTypeId($id)->count();
                $field->setSort(++$all_cnt);
            }

            $field->save();

            $this->get('session')->getFlashBag()->add(
                'notice1',
                'Поле "'.$field->getName().'" успешно добавлено!'
            );
            return $this->redirect($this->generateUrl('admin_objectstypes_edittypes', array('id'=> $id)));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'title' => 'Создание',
            'form' 		=> $form->createView(),
            'back' => $this->generateUrl('admin_objectstypes_edittypes', array('id'=> $id))
        ));
    }

    /**
     * @Route("/object_types/edit_field/{id}")
     */
    public function editFieldAction($id, Request $request)
    {

        $field = ObjectTypesFieldsQuery::create()->findPk($id);

        $form = $this->createForm(new ObjectTypesFieldsType(), $field);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $field->save();

            $this->get('session')->getFlashBag()->add(
                'notice1',
                'Поле "'.$field->getName().'" успешно изменено!'
            );
            if (!$field->getType() == 2 && !$field->getType() == 4)
                return $this->redirect($this->generateUrl('admin_objectstypes_edittypes', array('id'=> $field->getObjectTypeId())));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'title' => 'Редактирование',
            'item' => $field,
            'form' 		=> $form->createView(),
            'type_field' => $field->getType(),
            'back' => $this->generateUrl('admin_objectstypes_edittypes', array('id'=> $field->getObjectTypeId()))
        ));
    }

    /**
     * @Route("/object_types/fileds/sort")
     */
    public function sortFieldsAction(Request $request)
    {

        $array = $request->request->get('array');
        $cnt=1;
        foreach ($array as $item) {
            $field = ObjectTypesFieldsQuery::create()->findPk($item);
            $field->setSort($cnt++);
            $field->save();
        }
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/object_types/delete_field/{id}")
     */
    public function deleteFieldAction($id, Request $request)
    {

        $field = ObjectTypesFieldsQuery::create()->findPk($id);

        if ($field) {
            $object_type_id = $field->getObjectTypeId();
            $field->delete();
            return $this->redirect($this->generateUrl('admin_objectstypes_edittypes', array('id'=> $object_type_id)));
        }

        return $this->redirect($request->headers->get('referer'));

    }

    /**
     * @Route("/object_types/{id}/add_value")
     */
    public function addValueAction($id, Request $request)
    {

        $value = new ObjectTypesFieldsValues();
        $value->setFieldId($id);

        $form = $this->createForm(new ObjectTypesFieldsValuesType(), $value);

        $form->handleRequest($request);

        if ($form->isValid()) {

            if (!$value->getSort()) {
                $all_cnt = ObjectTypesFieldsValuesQuery::create()->filterByFieldId($id)->count();
                $value->setSort(++$all_cnt);
            }

            $value->save();

            $this->get('session')->getFlashBag()->add(
                'notice1',
                'Значение "'.$value->getName().'" успешно добавлено!'
            );
            return $this->redirect($this->generateUrl('admin_objectstypes_editfield', array('id'=> $id)));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'title'     => 'Добавление значения',
            'form' 		=> $form->createView(),
            'back'      => $this->generateUrl('admin_objectstypes_editfield', array('id'=> $id))
        ));
    }

    /**
     * @Route("/object_types/{id}/add_values")
     */
    public function addValuesAction($id, Request $request)
    {

        $value = new ObjectTypesFieldsValues();
        $value->setFieldId($id);

        $form = $this->createForm(new ObjectTypesFieldsValuessType(), $value);

        $form->handleRequest($request);

        if ($form->isValid()) {

            if (@$value->getName()) {
                $all_cnt = ObjectTypesFieldsValuesQuery::create()->filterByFieldId($id)->count();
                $cnt=0;
                $names = explode(';',$value->getName());
                foreach ($names as $name) {
                    $value = new ObjectTypesFieldsValues();
                    $value->setFieldId($id);
                    $value->setName($name);
                    $value->setSort(++$all_cnt);
                    $value->save();
                    $cnt++;
                }
            }

            $this->get('session')->getFlashBag()->add(
                'notice1',
                $cnt . ' значений успешно добавлено!'
            );
            return $this->redirect($this->generateUrl('admin_objectstypes_editfield', array('id'=> $id)));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'title'     => 'Добавление нескольких значений',
            'form' 		=> $form->createView(),
            'back'      => $this->generateUrl('admin_objectstypes_editfield', array('id'=> $id))
        ));
    }

    /**
     * @Route("/object_types/edit_value/{id}")
     */
    public function editValueAction($id, Request $request)
    {

        $value = ObjectTypesFieldsValuesQuery::create()->findPk($id);

        $form = $this->createForm(new ObjectTypesFieldsValuesType(), $value);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $value->save();

            $this->get('session')->getFlashBag()->add(
                'notice1',
                'Значение "'.$value->getName().'" успешно изменено!'
            );
            return $this->redirect($this->generateUrl('admin_objectstypes_editfield', array('id'=> $value->getFieldId())));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'title'     => 'Редактирование',
            'form' 		=> $form->createView(),
            'back'      => $this->generateUrl('admin_objectstypes_editfield', array('id'=> $value->getFieldId()))
        ));
    }

    /**
     * @Route("/object_types/values/sort")
     */
    public function sortValuesAction(Request $request)
    {

        $array = $request->request->get('array');
        $cnt=1;
        foreach ($array as $item) {
            $field = ObjectTypesFieldsValuesQuery::create()->findPk($item);
            $field->setSort($cnt++);
            $field->save();
        }
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/object_types/delete_value/{id}")
     */
    public function deleteValueAction($id, Request $request)
    {

        $value = ObjectTypesFieldsValuesQuery::create()->findPk($id);

        if ($value) {
            $field_id = $value->getFieldId();
            $value->delete();
            return $this->redirect($this->generateUrl('admin_objectstypes_editfield', array('id'=> $field_id)));
        }

        return $this->redirect($request->headers->get('referer'));

    }

}
