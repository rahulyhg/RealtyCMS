<?php

namespace SiteBundle\Model;

use SiteBundle\Model\om\BaseObjects;

class Objects extends BaseObjects
{

    public function getObjectImagess($criteria = null, \PropelPDO $con = null)
    {
        $partial = $this->collObjectImagessPartial && !$this->isNew();
        if (null === $this->collObjectImagess || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collObjectImagess) {
                // return empty collection
                $this->initObjectImagess();
            } else {
                $collObjectImagess = ObjectImagesQuery::create(null, $criteria)
                    ->filterByObjects($this)
                    ->orderBySrt()
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collObjectImagessPartial && count($collObjectImagess)) {
                        $this->initObjectImagess(false);

                        foreach ($collObjectImagess as $obj) {
                            if (false == $this->collObjectImagess->contains($obj)) {
                                $this->collObjectImagess->append($obj);
                            }
                        }

                        $this->collObjectImagessPartial = true;
                    }

                    $collObjectImagess->getInternalIterator()->rewind();

                    return $collObjectImagess;
                }

                if ($partial && $this->collObjectImagess) {
                    foreach ($this->collObjectImagess as $obj) {
                        if ($obj->isNew()) {
                            $collObjectImagess[] = $obj;
                        }
                    }
                }

                $this->collObjectImagess = $collObjectImagess;
                $this->collObjectImagessPartial = false;
            }
        }

        return $this->collObjectImagess;
    }

    public function getImage()
    {
        $image = NULL;
        $images = $this->getObjectImagess();
        if ($images) {
            $image = current($images);
        }
        return $image ? $image : NULL;
    }

    public function getObjectPrice()
    {
        return $this->getObjectTypes() ? ($this->getObjectTypes()->getLayouts() ? false : true) : true;
    }

    public function getPrice()
    {
        if ($this->getObjectTypes()) {
            if ($this->getObjectTypes()->getLayouts()) {
                $layouts = $this->getObjectLayoutss()->toKeyValue('Id','Price');
                if (count($layouts)) {
                    return min($layouts);
                } else return 0;
            } else return parent::getPrice();
        } else return parent::getPrice();
    }

    public function getParams($id, $normalized = false)
    {
        //$params = $this->getObjectParamss()->toKeyValue('FieldId',($normalized?'ValueNormalized':'Value'));
        $params = ObjectParamsQuery::create()->filterByObjects($this)->find()->toKeyValue('FieldId',($normalized?'ValueNormalized':'Value'));
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

    public function getObjectLayoutss($criteria = null, \PropelPDO $con = null)
    {
        $partial = $this->collObjectLayoutssPartial && !$this->isNew();
        if (null === $this->collObjectLayoutss || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collObjectLayoutss) {
                // return empty collection
                $this->initObjectLayoutss();
            } else {
                $collObjectLayoutss = ObjectLayoutsQuery::create(null, $criteria)
                    ->orderBySort()
                    ->filterByObjects($this)
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collObjectLayoutssPartial && count($collObjectLayoutss)) {
                        $this->initObjectLayoutss(false);

                        foreach ($collObjectLayoutss as $obj) {
                            if (false == $this->collObjectLayoutss->contains($obj)) {
                                $this->collObjectLayoutss->append($obj);
                            }
                        }

                        $this->collObjectLayoutssPartial = true;
                    }

                    $collObjectLayoutss->getInternalIterator()->rewind();

                    return $collObjectLayoutss;
                }

                if ($partial && $this->collObjectLayoutss) {
                    foreach ($this->collObjectLayoutss as $obj) {
                        if ($obj->isNew()) {
                            $collObjectLayoutss[] = $obj;
                        }
                    }
                }

                $this->collObjectLayoutss = $collObjectLayoutss;
                $this->collObjectLayoutssPartial = false;
            }
        }

        return $this->collObjectLayoutss;
    }
}
