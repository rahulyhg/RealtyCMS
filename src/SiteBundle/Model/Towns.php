<?php

namespace SiteBundle\Model;

use SiteBundle\Model\om\BaseTowns;

class Towns extends BaseTowns
{
    public function getAreass($criteria = null, PropelPDO $con = null)
    {
        $partial = $this->collAreassPartial && !$this->isNew();
        if (null === $this->collAreass || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collAreass) {
                // return empty collection
                $this->initAreass();
            } else {
                $collAreass = AreasQuery::create(null, $criteria)
                    ->filterByTowns($this)
                    ->orderByTitle()
                    ->find($con);
                if (null !== $criteria) {
                    if (false !== $this->collAreassPartial && count($collAreass)) {
                        $this->initAreass(false);

                        foreach ($collAreass as $obj) {
                            if (false == $this->collAreass->contains($obj)) {
                                $this->collAreass->append($obj);
                            }
                        }

                        $this->collAreassPartial = true;
                    }

                    $collAreass->getInternalIterator()->rewind();

                    return $collAreass;
                }

                if ($partial && $this->collAreass) {
                    foreach ($this->collAreass as $obj) {
                        if ($obj->isNew()) {
                            $collAreass[] = $obj;
                        }
                    }
                }

                $this->collAreass = $collAreass;
                $this->collAreassPartial = false;
            }
        }

        return $this->collAreass;
    }
}
