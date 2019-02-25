<?php

namespace SiteBundle\Model;

use SiteBundle\Model\om\BaseObjects;

class Objects extends BaseObjects
{
    public function getImage()
    {
        $image = NULL;
        $images = $this->getObjectImagess();
        if ($images) {
            $image = current($images);
        }
        return $image ? $image : NULL;
    }

    public function getParams($id, $normalized = false)
    {
        $params = $this->getObjectParamss()->toKeyValue('FieldId',($normalized?'ValueNormalized':'Value'));
        return @$params[$id]?:'';
    }
}
