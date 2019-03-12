<?php

namespace AdminBundle\Controller;

use AdminBundle\Form\LayoutsFieldsType;
use AdminBundle\Form\LayoutsFieldsValuessType;
use AdminBundle\Form\LayoutsFieldsValuesType;
use AdminBundle\Form\LayoutsType;
use SiteBundle\Model\LayoutParams;
use SiteBundle\Model\LayoutParamsQuery;
use SiteBundle\Model\LayoutsFields;
use SiteBundle\Model\LayoutsFieldsQuery;
use SiteBundle\Model\LayoutsFieldsValues;
use SiteBundle\Model\LayoutsFieldsValuesQuery;
use SiteBundle\Model\ObjectLayouts;
use SiteBundle\Model\ObjectLayoutsQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class LayoutsController extends Controller
{

    /**
     * @Route("/layouts/{id}/add")
     */
    public function addAction($id, Request $request)
    {

        $item = new ObjectLayouts();
        $item->setObjectId($id);

        $form = $this->createForm(new LayoutsType(), $item);

        $form->handleRequest($request);
        $dir = 'images/objects/layouts';

        if ($form->isValid()) {
            if (!$item->getSort()) {
                $all_cnt = ObjectLayoutsQuery::create()->filterByObjectId($id)->count();
                $item->setSort(++$all_cnt);
            }
            $item->save();

            if ($form['image']->getData()) {
                $file_type = $form['image']->getData()->getMimeType();
                $f_name = uniqid();
                switch($file_type) {
                    case 'image/png': $Filename = $f_name.'.png'; $Thumbname = $f_name.'_thumb.png'; break;
                    case 'image/jpeg': $Filename = $f_name.'.jpg'; $Thumbname = $f_name.'_thumb.jpg'; break;
                    case 'image/gif': $Filename = $f_name.'.gif'; $Thumbname = $f_name.'_thumb.gif'; break;
                    default: $Filename = NULL;
                }
                if ($Filename) {
                    $form['image']->getData()->move($dir, $Filename);
                    $image = new Image($dir . '/' . $Filename);
                    $image->fit_to_width(800);
                    $image->save($dir . '/' . $Filename);
                    $item->setImage($Filename);
                    $image->fit_to_width(200);
                    $image->save($dir . '/' . $Thumbname);
                    $item->setThumb($Thumbname);
                    $item->save();
                }
            }

            if ($item->getObjects()->getObjectTypes()) {
                $fields = LayoutsFieldsQuery::create()->filterByObjectTypeId($item->getTypeObject())->orderByType()->orderByName()->find();
                foreach ($fields as $field) {
                    if ($form['params_'. $field->getId()]->getData()) {
                        $param = LayoutParamsQuery::create()->filterByLayoutId($item->getId())->filterByFieldId($field->getId())->findOne();
                        if (!$param) {
                            $param = new LayoutParams();
                            $param->setLayoutId($item->getId());
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
            if ($generator = $item->getObjects()->getObjectTypes()->getGeneratorLayout()) {
                $nitem = ObjectLayoutsQuery::create()->findPk($item->getId());
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
                'notice1',
                'Планировка "'.$item->getTitle().'" успешно добавлена!'
            );
            return $this->redirect($this->generateUrl('admin_objects_edit', array('id'=> $item->getObjectId())));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'title' => 'Создание',
            'form' 	=> $form->createView(),
            'back'  => $this->generateUrl('admin_objects_edit', array('id'=>$id))
        ));
    }

    /**
     * @Route("/layouts/edit/{id}")
     */
    public function editAction($id, Request $request)
    {

        $item = ObjectLayoutsQuery::create()->findPk($id);
        if (!$item) {
            throw $this->createNotFoundException('Планироввки не существует');
        }

        $dir = 'images/objects/layouts';
        $old_image = $item->getImage();
        $old_thumb = $item->getThumb();
        $item->setImage(NULL)->setThumb(NULL);

        $form = $this->createForm(new LayoutsType(), $item);

        $form->handleRequest($request);

        if ($form->isValid()) {
            if (!$item->getSort()) {
                $all_cnt = ObjectLayoutsQuery::create()->filterByObjectId($id)->count();
                $item->setSort(++$all_cnt);
            }
            $item->save();

            if ($form['image']->getData()) {
                $file_type = $form['image']->getData()->getMimeType();
                $f_name = uniqid();
                switch($file_type) {
                    case 'image/png': $Filename = $f_name.'.png'; $Thumbname = $f_name.'_thumb.png'; break;
                    case 'image/jpeg': $Filename = $f_name.'.jpg'; $Thumbname = $f_name.'_thumb.jpg'; break;
                    case 'image/gif': $Filename = $f_name.'.gif'; $Thumbname = $f_name.'_thumb.gif'; break;
                    default: $Filename = NULL;
                }
                if ($Filename) {
                    $form['image']->getData()->move($dir, $Filename);
                    $image = new Image($dir . '/' . $Filename);
                    $image->fit_to_width(800);
                    $image->save($dir . '/' . $Filename);
                    $item->setImage($Filename);
                    $image->fit_to_width(200);
                    $image->save($dir . '/' . $Thumbname);
                    $item->setThumb($Thumbname);
                } else {
                    $item->setImage($old_image);
                    $item->setThumb($old_thumb);
                }
            } else {
                $item->setImage($old_image);
                $item->setThumb($old_thumb);
            }
            $item->save();

            if ($item->getObjects()->getObjectTypes()) {
                $fields = LayoutsFieldsQuery::create()->filterByObjectTypeId($item->getTypeObject())->orderByType()->orderByName()->find();
                foreach ($fields as $field) {
                    if ($form['params_'. $field->getId()]->getData()) {
                        $param = LayoutParamsQuery::create()->filterByLayoutId($item->getId())->filterByFieldId($field->getId())->findOne();
                        if (!$param) {
                            $param = new LayoutParams();
                            $param->setLayoutId($item->getId());
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
            if ($generator = $item->getObjects()->getObjectTypes()->getGeneratorLayout()) {
                $nitem = ObjectLayoutsQuery::create()->findPk($item->getId());
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
                'notice1',
                'Планировка "'.$item->getTitle().'" успешно изменена!'
            );
            return $this->redirect($this->generateUrl('admin_objects_edit', array('id'=> $item->getObjectId())));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'title' => 'Редактирование',
            'form' 	=> $form->createView(),
            'photo' => $old_thumb ? '/'.$dir.'/'.$old_thumb : null,
            'back'  => $this->generateUrl('admin_objects_edit', array('id'=>$id))
        ));
    }

    /**
     * @Route("/layouts/delete/{id}")
     */
    public function deleteAction($id)
    {
        $item = ObjectLayoutsQuery::create()->findPk($id);
        if (!$item) {
            throw $this->createNotFoundException('Планироввки не существует');
        }

    $object_id = $item->getObjectId();

        if ($item->getImage()) {
            $fs = new Filesystem();
            try {
                $fs->remove( 'images/objects/layouts/'.$item->getImage() );
            } catch (IOExceptionInterface $e) {
                echo "Ошибка удаления изображения";
            }
        }
        if ($item->getThumb()) {
            $fs = new Filesystem();
            try {
                $fs->remove( 'images/objects/layouts/'.$item->getThumb() );
            } catch (IOExceptionInterface $e) {
                echo "Ошибка удаления изображения";
            }
        }
        $item->delete();
        $this->get('session')->getFlashBag()->add(
            'notice',
            'Успешно удалено!'
        );

        return $this->redirect($this->generateUrl('admin_objects_edit', array('id' => $object_id)));
    }

    /**
     * @Route("/layouts/sort")
     */
    public function sortAction(Request $request)
    {

        $array = $request->request->get('array');
        $cnt = 1;
        foreach ($array as $item) {
            $layout_item = ObjectLayoutsQuery::create()->findPk($item);
            $layout_item->setSort($cnt++);
            $layout_item->save();
        }
        return $this->redirect($request->headers->get('referer'));
    }



    /**
     * @Route("/layouts/{id}/add_field")
     */
    public function addFieldAction($id, Request $request)
    {

        $field = new LayoutsFields();
        $field->setObjectTypeId($id);

        $form = $this->createForm(new LayoutsFieldsType(), $field);

        $form->handleRequest($request);

        if ($form->isValid()) {

            if (!$field->getSort()) {
                $all_cnt = LayoutsFieldsQuery::create()->filterByObjectTypeId($id)->count();
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
            'form' 	=> $form->createView(),
            'back'  => $this->generateUrl('admin_objectstypes_edittypes', array('id'=> $id))
        ));
    }

    /**
     * @Route("/layouts/edit_field/{id}")
     */
    public function editFieldAction($id, Request $request)
    {

        $field = LayoutsFieldsQuery::create()->findPk($id);

        $form = $this->createForm(new LayoutsFieldsType(), $field);

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
            'layouts_type_field' => $field->getType(),
            'back' => $this->generateUrl('admin_objectstypes_edittypes', array('id'=> $field->getObjectTypeId()))
        ));
    }

    /**
     * @Route("/layouts/fileds/sort")
     */
    public function sortFieldsAction(Request $request)
    {

        $array = $request->request->get('array');
        $cnt=1;
        foreach ($array as $item) {
            $field = LayoutsFieldsQuery::create()->findPk($item);
            $field->setSort($cnt++);
            $field->save();
        }
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/layouts/delete_field/{id}")
     */
    public function deleteFieldAction($id, Request $request)
    {

        $field = LayoutsFieldsQuery::create()->findPk($id);

        if ($field) {
            $object_type_id = $field->getObjectTypeId();
            $field->delete();
            return $this->redirect($this->generateUrl('admin_objectstypes_edittypes', array('id'=> $object_type_id)));
        }

        return $this->redirect($request->headers->get('referer'));

    }

    /**
     * @Route("/layouts/{id}/add_value")
     */
    public function addValueAction($id, Request $request)
    {

        $value = new LayoutsFieldsValues();
        $value->setFieldId($id);

        $form = $this->createForm(new LayoutsFieldsValuesType(), $value);

        $form->handleRequest($request);

        if ($form->isValid()) {

            if (!$value->getSort()) {
                $all_cnt = LayoutsFieldsValuesQuery::create()->filterByFieldId($id)->count();
                $value->setSort(++$all_cnt);
            }

            $value->save();

            $this->get('session')->getFlashBag()->add(
                'notice1',
                'Значение "'.$value->getName().'" успешно добавлено!'
            );
            return $this->redirect($this->generateUrl('admin_layouts_editfield', array('id'=> $id)));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'title'     => 'Добавление значения',
            'form' 		=> $form->createView(),
            'back'  => $this->generateUrl('admin_layouts_editfield', array('id'=> $id))
        ));
    }

    /**
     * @Route("/layouts/{id}/add_values")
     */
    public function addValuesAction($id, Request $request)
    {

        $value = new LayoutsFieldsValues();
        $value->setFieldId($id);

        $form = $this->createForm(new LayoutsFieldsValuessType(), $value);

        $form->handleRequest($request);

        if ($form->isValid()) {

            if (@$value->getName()) {
                $all_cnt = LayoutsFieldsValuesQuery::create()->filterByFieldId($id)->count();
                $cnt = 0;
                $names = explode(';',$value->getName());
                foreach ($names as $name) {
                    $value = new LayoutsFieldsValues();
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
            return $this->redirect($this->generateUrl('admin_layouts_editfield', array('id'=> $id)));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'title'     => 'Добавление нескольких значений',
            'form' 		=> $form->createView(),
            'back'  => $this->generateUrl('admin_layouts_editfield', array('id'=> $id))
        ));
    }

    /**
     * @Route("/layouts/edit_value/{id}")
     */
    public function editValueAction($id, Request $request)
    {

        $value = LayoutsFieldsValuesQuery::create()->findPk($id);

        $form = $this->createForm(new LayoutsFieldsValuesType(), $value);

        $form->handleRequest($request);

        if ($form->isValid()) {

            $value->save();

            $this->get('session')->getFlashBag()->add(
                'notice1',
                'Значение "'.$value->getName().'" успешно изменено!'
            );
            return $this->redirect($this->generateUrl('admin_layouts_editfield', array('id'=> $value->getFieldId())));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'title'     => 'Редактирование',
            'form' 		=> $form->createView(),
            'back'      => $this->generateUrl('admin_layouts_editfield', array('id'=> $value->getFieldId()))
        ));
    }

    /**
     * @Route("/layouts/values/sort")
     */
    public function sortValuesAction(Request $request)
    {

        $array = $request->request->get('array');
        $cnt = 1;
        foreach ($array as $item) {
            $field = LayoutsFieldsValuesQuery::create()->findPk($item);
            $field->setSort($cnt++);
            $field->save();
        }
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/layouts/delete_value/{id}")
     */
    public function deleteValueAction($id, Request $request)
    {

        $value = LayoutsFieldsValuesQuery::create()->findPk($id);

        if ($value) {
            $field_id = $value->getFieldId();
            $value->delete();
            return $this->redirect($this->generateUrl('admin_layouts_editfield', array('id'=> $field_id)));
        }

        return $this->redirect($request->headers->get('referer'));

    }

}
