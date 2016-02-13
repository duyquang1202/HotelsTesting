<?php

namespace HotelsBundle\Entity;



/**
 * BaseEntity
 */
class BaseEntity
{
    
    public function setData($data)
    {
        if (!empty($data)) {
            foreach ($data as $fieldName => $value) {
                $this->set($fieldName, $value);
            }
        }
    
        return $this;
    }
}

