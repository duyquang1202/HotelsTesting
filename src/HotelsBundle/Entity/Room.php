<?php

namespace HotelsBundle\Entity;

use Doctrine\Common\Util\Inflector;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use JMS\Serializer\Annotation\Groups;
/**
 * Room
 * @UniqueEntity(
 *     fields={"name"},
 *     message="room.identifier.unique"
 * )
 */
class Room extends BaseEntity
{
    /**
     * @var int
     * @Groups({"view"})
     */
    private $id;

    /**
     * @var string
     * @Groups({"view"})
     */
    private $name;
    
    /**
     * @var string
     * @Groups({"view"})
     */
    private $identifier;
    
   
    private $offers;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Room
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    public function get($fieldName)
    {
        $realFieldName = lcfirst(Inflector::classify($fieldName));
        return $this->$realFieldName;
    }
    
    public function set($fieldName, $value)
    {
        $realFieldName = lcfirst(Inflector::classify($fieldName));
        $this->$realFieldName = $value;
    }
}

