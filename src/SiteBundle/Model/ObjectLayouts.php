<?php

namespace SiteBundle\Model;

use SiteBundle\Model\om\BaseObjectLayouts;

class ObjectLayouts extends BaseObjectLayouts
{
    public function getTypeObject()
    {
        return $this->getObjectId() ? $this->getObjects()->getTypeObject() : null;
    }

    public function getParams($id, $normalized = false)
    {
        $params = LayoutParamsQuery::create()->filterByObjectLayouts($this)->find()->toKeyValue('FieldId',($normalized?'ValueNormalized':'Value'));
        return @$params[$id]?:'';
    }

}
