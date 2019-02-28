<?php

namespace SiteBundle\Model;

use SiteBundle\Model\om\BaseSettings;

class Settings extends BaseSettings
{
    public function getWtitle($cnt) {
        $method = 'getWhy'.$cnt.'Title';
        return $this->$method();
    }

    public function getWtext($cnt) {
        $method = 'getWhy'.$cnt.'Text';
        return $this->$method();
    }
}
