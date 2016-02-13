<?php

namespace HotelsBundle\Entity;

use Doctrine\Common\Util\Inflector;
use Symfony\Component\Validator\Constraints as Assert;
use HotelsBundle\Entity\Room;
/**
 * Offer
 */
class Offer extends BaseEntity
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var \DateTime
     * 
     * @Assert\NotBlank(
     *     message = "offer.date.not_blank"
     * )
     * @Assert\Regex(
     *     pattern = "/^(19|20)\d\d[\-\/.](0[1-9]|1[012])[\-\/.](0[1-9]|[12][0-9]|3[01])$/",
     *     htmlPattern = "^(19|20)\d\d[\-\/.](0[1-9]|1[012])[\-\/.](0[1-9]|[12][0-9]|3[01])$",
     *     message = "offer.date.format"
     * )
     */
    private $date;
    
    private $roomId;
    
    private $room;
    
    public function setRoom(Room $room)
    {
        $this->room =$room;
        return $this;
    }


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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Offer
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
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

