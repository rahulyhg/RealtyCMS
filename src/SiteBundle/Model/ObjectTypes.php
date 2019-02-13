<?php

namespace SiteBundle\Model;

use SiteBundle\Model\om\BaseObjectTypes;

class ObjectTypes extends BaseObjectTypes
{
    public function min_price()
    {
        $price_array = array();
        foreach ($this->getObjectss() as  $object) {
            $price_array[] = $object->getPrice();
        }
        return $price_array ? min($price_array) : 0;
    }
}
