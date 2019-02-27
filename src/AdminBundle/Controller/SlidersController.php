<?php

namespace AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SiteBundle\Model\Sliders;
use SiteBundle\Model\SlidersQuery;
use AdminBundle\Form\SlidersType;
use SiteBundle\Model\SliderImages;
use SiteBundle\Model\SliderImagesQuery;
use AdminBundle\Form\SliderImagesType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class SlidersController extends Controller
{

    /**
     * @Route("/sliders")
     */
    public function indexAction()
    {
        $items = SlidersQuery::create()            
            ->find();
        $data = [];
        if ($items) foreach ($items as $item){
          $data[] = array( 
            'actions' => array(
                        'view'    => NULL,
                        'edit'    => NULL, //$this->generateUrl('admin_sliders_edit',array('id'=>$item->getId())),
                        'delete'  => NULL, //$this->generateUrl('admin_sliders_delete',array('id'=>$item->getId())),
                      ),
            'item'   => array(
                        'ID'            => array('value'=>$item->getId()),
                        'Название'  => array(
                                            'value' =>$item->getTitle()
                                          ),                        
                        'Изображения' => array(
                                            'images' => $item->getSliderImagess(),
                                            'path'    => '/images/slider/',
                                            'add'     => $this->generateUrl('admin_sliders_image',array('id'=>$item->getId())),
                                            'edit'    => 'admin_sliders_editimage',
                                            'delete'  => 'admin_sliders_delimg',                                            
                                          ),                        
                      ),                        
          );          
        }        

        return $this->render('AdminBundle:Default:items.html.twig',array(
            'data' 		  => $data,
            'title'     => 'Слайдеры',
            'add_url'   => $this->generateUrl('admin_sliders_add'),
            'back_url'  => $this->generateUrl('admin_default_index'),
        ));
    }

    /**
     * @Route("/sliders/add")
     */
    public function addAction(Request $request)
    {
        $item = new Sliders();

        $form = $this->createForm(new SlidersType(), $item);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $item->save();
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Галерея успешно добавлена!'
            );
            return $this->redirect($this->generateUrl('admin_sliders_index'));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'form' 		  => $form->createView(),
            'title' 		=> 'Добавление',
            'parent' 		=> 'Галереи',
            'back_url'  => $this->generateUrl('admin_sliders_index'),
        ));
    }

    /**
     * @Route("/sliders/edit/{id}")
     */
    public function editAction($id, Request $request)
    {
        $item = SlidersQuery::create()
            ->filterById($id)
            ->findOne();

        $form = $this->createForm(new SlidersType(), $item);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $item->save();
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Галерея успешно сохранена!'
            );
            return $this->redirect($this->generateUrl('admin_sliders_index'));
        }

        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'form' 		  => $form->createView(),
            'title' 		=> 'Редактирование',
            'parent' 		=> 'Галереи',
            'back_url'  => $this->generateUrl('admin_sliders_index'),
        ));
    }

    /**
     * @Route("/sliders/delete/{id}")
     */
    public function deleteAction($id)
    {
        $item = SlidersQuery::create()
            ->filterById($id)
            ->findOne();

        if ($item) {
            foreach($item->getSliderImagess() as $image) {
              $this->delimgAction($image->getId());
            }
            $item->delete();
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Галерея успешно удалена!'
            );
        }
        return $this->redirect($this->generateUrl('admin_sliders_index'));
    }

    /**
     * @Route("/sliders/image/{id}")
     */
    public function imageAction($id, Request $request)
    {
        $slider = SlidersQuery::create()
            ->filterById($id)
            ->findOne();
        if (!$slider) {
            throw $this->createNotFoundException(
                'Нет доступных элементов'
            );
        }
        $dir = 'images/slider';
        $item = new SliderImages();
        $item->setSliderId($slider->getId());
        $form = $this->createForm(new SliderImagesType(), $item);
        $form->handleRequest($request);
        if ($form->isValid()) {
            if ($form['path']->getData()) {
                $file_type = $form['path']->getData()->getMimeType();
                switch($file_type) {
                    case 'image/png': $Filename = uniqid().'.png'; break;
                    case 'image/jpeg': $Filename = uniqid().'.jpg'; break;
                    case 'image/gif': $Filename = uniqid().'.gif'; break;
                    default: $Filename = NULL;
                }
                if ($Filename) {
                    $form['path']->getData()->move($dir, $Filename);
                    $item->setPath($Filename);
                    $image = new Image($dir.'/'.$Filename);
                    $image->save($dir.'/'.$Filename);
                }
            }
            $item->save();
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Изображение успешно добавлено!'
            );
            return $this->redirect($this->generateUrl('admin_sliders_index'));
        }
        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'form' 		=> $form->createView(),
            'title' 	=> 'Добавление',
            'parent' 	=> 'Изображения',
            'back_url'  => $this->generateUrl('admin_sliders_index'),
        ));
    }

    /**
     * @Route("/sliders/image/edit/{id}")
     */
    public function editImageAction($id, Request $request)
    {
        $item = SliderImagesQuery::create()
            ->filterById($id)
            ->findOne();
        if (!$item) {
            throw $this->createNotFoundException(
                'Нет доступных элементов'
            );
        }
        $dir = 'images/slider';
        $photo = $item->getPath();
        $item->setPath(NULL);
        $form = $this->createForm(new SliderImagesType(), $item);
        $form->handleRequest($request);
        if ($form->isValid()) {
            if ($form['path']->getData()) {
                $file_type = $form['path']->getData()->getMimeType();
                switch($file_type) {
                    case 'image/png': $Filename = uniqid().'.png'; break;
                    case 'image/jpeg': $Filename = uniqid().'.jpg'; break;
                    case 'image/gif': $Filename = uniqid().'.gif'; break;
                    default: $Filename = NULL;
                }
                if ($Filename) {
                    $form['path']->getData()->move($dir, $Filename);
                    $item->setPath($Filename);
                    $image = new Image($dir.'/'.$Filename);
                    $image->save($dir.'/'.$Filename);
                    if ($photo) {
                        $fs = new Filesystem();
                        try {
                            $fs->remove( $dir.'/'.$photo );
                        } catch (IOExceptionInterface $e) {
                            echo "Ошибка удаления изображения";
                        }
                    }
                }
            } else {
                $item->setPath($photo);
            }
            $item->save();
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Изображение успешно сохранено!'
            );
            return $this->redirect($this->generateUrl('admin_sliders_index'));
        }
        return $this->render('AdminBundle:Form:edit.html.twig',array(
            'form' 		=> $form->createView(),
            'title' 	=> 'Редактирование',
            'parent' 	=> 'Изображения',
            'photo'     => '/'.$dir.'/'.$photo,
            'back_url'  => $this->generateUrl('admin_sliders_index'),
        ));
    }

    /**
     * @Route("/sliders/delimg/{id}")
     */
    public function delimgAction($id)
    {
        $item = SliderImagesQuery::create()
            ->filterById($id)
            ->findOne();

        if ($item) {
            if ($item->getPath()) {
                $fs = new Filesystem();
                try {
                    $fs->remove( 'images/slider/'.$item->getPath() );
                } catch (IOExceptionInterface $e) {
                    echo "Ошибка удаления изображения";
                }
            }
            $item->delete();
            $this->get('session')->getFlashBag()->add(
                'notice',
                'Изображение успешно удалено!'
            );
        }
        return $this->redirect($this->generateUrl('admin_sliders_index'));
    }
    
    /**
     * @Route("/sliders/sort")
     */
    public function sortAction(Request $request)
    {
        
		$array = $request->request->get('array');
		$cnt=1;
		foreach ($array as $item) {
			$category = SliderImagesQuery::create()->findOneById($item);
			$category->setSort($cnt++);
			$category->save();
		}
        return new Response();
    }
}
