<?php

namespace SiteBundle\Model;

use SiteBundle\Model\om\BaseObjectParams;

class ObjectParams extends BaseObjectParams
{
    public function getValue() {
        return $this->getTextValue()?: $this->getValueId();
    }

    public function getValueNormalized() {
        return $this->getTextValue()?: $this->getObjectTypesFieldsValues()->getName();
    }
}
