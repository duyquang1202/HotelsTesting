<?php

namespace HotelsBundle\Repository;
use HotelsBundle\Entity\Offer;
use HotelsBundle\Entity\Room;
use Goutte\Client;
/**
 * RoomRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RoomRepository extends \Doctrine\ORM\EntityRepository
{
    public function findRooms($params)
    {
        $offer = new Offer();
        $offer->set('date',$params['date']);
        return $this->getAvailableRooms($offer);
    }
    public function getAvailableRooms(Offer $offer)
    {
        $date = $offer->get('date');
        $date = new \DateTime($date);
        $checkOutDate = $date->format('d/m/Y');
        $checkIn = $date->modify( '-1 day' );
        $checkInDate = $checkIn->format('d/m/Y');
        $client = new Client();
        $point = "http://vi.hotels.com/hotel/details.html?tab=description&q-localised-check-in=$checkInDate&hotel-id=555246&q-room-0-adults=2&YGF=1&MGT=7&WOE=7&q-localised-check-out=$checkOutDate&WOD=6&ZSX=0&SYE=3&q-room-0-children=0";
         
        $crawler = $client->request('GET', $point);
        $rooms = $crawler->filter('ul.rooms  > li.room > div.room-info')->each(function ($node) {
            $name = $node->filter('h3')->text();
            $linkImage = $node->filter('.room-images-link')->link()->getUri();
            $info = pathinfo($linkImage);
            $identifier = basename($linkImage,'.'.$info['extension']);
            return [
                'identifier' => $identifier,
                'name' => $name
            ];
        });
        
        return $rooms;
    }
    
    public function scrapeData($date)
    {
        $offer = new Offer();
        $offer->set('date',$date);
        return $this->getAvailableRooms($offer);
    }
    
    public function checkExistsRoomByIdentifier($identifer)
    {
        $room = $this->findOneBy([
            'identifier' => $identifer
        ]);
        return $room;
    }
    
    public function addAvailableRooms($rooms = [])
    {
        if(count($rooms) == 0) {
            return false;
        }
        $roomObjects = [];
        foreach($rooms as $room) {
            $roomObject = $this->checkExistsRoomByIdentifier($room['identifier']);
           if(! $roomObject instanceof Room) {
               $roomObject = new Room();
               $roomObject->set('name', $room['name']);
               $roomObject->set('identifier', $room['identifier']);
           } else{
                $roomObject->set('name', $room['name']);
           }
           $this->_em->persist($roomObject);
           $this->_em->flush();
           $roomObjects[] = $roomObject;
        }
        return $roomObjects;
    }
}
