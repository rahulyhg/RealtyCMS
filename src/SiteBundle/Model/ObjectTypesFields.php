<?php

namespace SiteBundle\Model;

use SiteBundle\Model\om\BaseObjectTypesFields;

class ObjectTypesFields extends BaseObjectTypesFields
{
    public function getObjectTypesFieldsValuess($criteria = null, \PropelPDO $con = null)
    {
        $partial = $this->collObjectTypesFieldsValuessPartial && !$this->isNew();
        if (null === $this->collObjectTypesFieldsValuess || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collObjectTypesFieldsValuess) {
                // return empty collection
                $this->initObjectTypesFieldsValuess();
            } else {
                $collObjectTypesFieldsValuess = ObjectTypesFieldsValuesQuery::create(null, $criteria)
                    ->filterByObjectTypesFields($this)
                    ->orderBySort()
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collObjectTypesFieldsValuessPartial && count($collObjectTypesFieldsValuess)) {
                        $this->initObjectTypesFieldsValuess(false);

                        foreach ($collObjectTypesFieldsValuess as $obj) {
                            if (false == $this->collObjectTypesFieldsValuess->contains($obj)) {
                                $this->collObjectTypesFieldsValuess->append($obj);
                            }
                        }

                        $this->collObjectTypesFieldsValuessPartial = true;
                    }

                    $collObjectTypesFieldsValuess->getInternalIterator()->rewind();

                    return $collObjectTypesFieldsValuess;
                }

                if ($partial && $this->collObjectTypesFieldsValuess) {
                    foreach ($this->collObjectTypesFieldsValuess as $obj) {
                        if ($obj->isNew()) {
                            $collObjectTypesFieldsValuess[] = $obj;
                        }
                    }
                }

                $this->collObjectTypesFieldsValuess = $collObjectTypesFieldsValuess;
                $this->collObjectTypesFieldsValuessPartial = false;
            }
        }

        return $this->collObjectTypesFieldsValuess;
    }
}
