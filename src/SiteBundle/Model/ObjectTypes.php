<?php

namespace SiteBundle\Model;

use SiteBundle\Model\om\BaseObjectTypes;

class ObjectTypes extends BaseObjectTypes
{
    public function min_price()
    {
        $price_array = array();
        $objects = $this->getObjectss();
        foreach ($objects as $object) {
            if ($object->getType() == 1) $price_array[] = $object->getPrice();
        }
        return count($price_array) ? min($price_array) : 0;
    }

    public function getObjectTypesFieldss($criteria = null, \PropelPDO $con = null)
    {
        $partial = $this->collObjectTypesFieldssPartial && !$this->isNew();
        if (null === $this->collObjectTypesFieldss || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collObjectTypesFieldss) {
                // return empty collection
                $this->initObjectTypesFieldss();
            } else {
                $collObjectTypesFieldss = ObjectTypesFieldsQuery::create(null, $criteria)
                    ->filterByObjectTypes($this)
                    ->orderBySort()
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collObjectTypesFieldssPartial && count($collObjectTypesFieldss)) {
                        $this->initObjectTypesFieldss(false);

                        foreach ($collObjectTypesFieldss as $obj) {
                            if (false == $this->collObjectTypesFieldss->contains($obj)) {
                                $this->collObjectTypesFieldss->append($obj);
                            }
                        }

                        $this->collObjectTypesFieldssPartial = true;
                    }

                    $collObjectTypesFieldss->getInternalIterator()->rewind();

                    return $collObjectTypesFieldss;
                }

                if ($partial && $this->collObjectTypesFieldss) {
                    foreach ($this->collObjectTypesFieldss as $obj) {
                        if ($obj->isNew()) {
                            $collObjectTypesFieldss[] = $obj;
                        }
                    }
                }

                $this->collObjectTypesFieldss = $collObjectTypesFieldss;
                $this->collObjectTypesFieldssPartial = false;
            }
        }

        return $this->collObjectTypesFieldss;
    }
}
