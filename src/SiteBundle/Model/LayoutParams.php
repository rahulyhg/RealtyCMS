<?php

namespace SiteBundle\Model;

use SiteBundle\Model\om\BaseLayoutParams;

class LayoutParams extends BaseLayoutParams
{
    public function getValue() {
        return $this->getTextValue()?: $this->getValueId();
    }

    public function getValueNormalized() {
        return $this->getTextValue()?: $this->getLayoutsFieldsValues()->getName();
    }
}
