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

    public function getObjectParamss($criteria = null, \PropelPDO $con = null)
    {
        $partial = $this->collObjectParamssPartial && !$this->isNew();
        if (null === $this->collObjectParamss || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collObjectParamss) {
                // return empty collection
                $this->initObjectParamss();
            } else {
                $collObjectParamss = ObjectParamsQuery::create(null, $criteria)
                    ->filterByObjects($this)
                    ->useObjectTypesFieldsQuery()
                    ->orderBySort()
                    ->endUse()
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collObjectParamssPartial && count($collObjectParamss)) {
                        $this->initObjectParamss(false);

                        foreach ($collObjectParamss as $obj) {
                            if (false == $this->collObjectParamss->contains($obj)) {
                                $this->collObjectParamss->append($obj);
                            }
                        }

                        $this->collObjectParamssPartial = true;
                    }

                    $collObjectParamss->getInternalIterator()->rewind();

                    return $collObjectParamss;
                }

                if ($partial && $this->collObjectParamss) {
                    foreach ($this->collObjectParamss as $obj) {
                        if ($obj->isNew()) {
                            $collObjectParamss[] = $obj;
                        }
                    }
                }

                $this->collObjectParamss = $collObjectParamss;
                $this->collObjectParamssPartial = false;
            }
        }

        return $this->collObjectParamss;
    }

    public function getObjectParamssForTable($criteria = null, \PropelPDO $con = null)
    {
        $partial = $this->collObjectParamssPartial && !$this->isNew();
        if (null === $this->collObjectParamss || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collObjectParamss) {
                // return empty collection
                $this->initObjectParamss();
            } else {
                $collObjectParamss = ObjectParamsQuery::create(null, $criteria)
                    ->filterByObjects($this)
                    ->useObjectTypesFieldsQuery()
                    ->filterByShowInTable(true)
                    ->orderBySort()
                    ->endUse()
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collObjectParamssPartial && count($collObjectParamss)) {
                        $this->initObjectParamss(false);

                        foreach ($collObjectParamss as $obj) {
                            if (false == $this->collObjectParamss->contains($obj)) {
                                $this->collObjectParamss->append($obj);
                            }
                        }

                        $this->collObjectParamssPartial = true;
                    }

                    $collObjectParamss->getInternalIterator()->rewind();

                    return $collObjectParamss;
                }

                if ($partial && $this->collObjectParamss) {
                    foreach ($this->collObjectParamss as $obj) {
                        if ($obj->isNew()) {
                            $collObjectParamss[] = $obj;
                        }
                    }
                }

                $this->collObjectParamss = $collObjectParamss;
                $this->collObjectParamssPartial = false;
            }
        }

        return $this->collObjectParamss;
    }
}
