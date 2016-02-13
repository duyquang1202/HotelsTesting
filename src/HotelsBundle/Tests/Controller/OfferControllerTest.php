<?php

namespace HotelsBundle\Tests\Controller;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use HotelsBundle\Entity\Offer;
use HotelsBundle\Entity\Room;

class OfferControllerTest extends KernelTestCase
{
    protected $container;
    
    public function setUp()
    {
        self::bootKernel();
        $this->container = static::$kernel->getContainer();
    }
    
    public function testCheckExistsRoomByIdentifier()
    {
        $roomRepository = $this->container->get('hotels.room.repository');
        $identifer = '12672659_1_b';
        $room = $roomRepository->checkExistsRoomByIdentifier($identifer);
        $flag = false;
        if($room instanceof Room) {
            $flag = true;
        }
        
        $this->assertEquals(true,$flag);
    }
    
    public function testGetAvailableRooms()
    {
        $roomRepository = $this->container->get('hotels.room.repository');
        
        $date = '2016-02-26';
        $offer = new Offer();
        $offer->set('date',$date);
        $rooms = $roomRepository->getAvailableRooms($offer);
        $flag = false;
        foreach($rooms as $room) {
            if(trim($room['name']) == '1 Bedroom Classic') {
                $flag = true;
                break;
            }
        }
        $this->assertEquals(true,$flag);
    }
    
    
    public function testScrapeData()
    {
        $roomRepository = $this->container->get('hotels.room.repository');
        
        $date = '2016-02-26';
        $rooms = $roomRepository->scrapeData($date);
        $flag = false;
        foreach($rooms as $room) {
            if(trim($room['name']) == '1 Bedroom Classic') {
                $flag = true;
                break;
            }
        }
        $this->assertEquals(true,$flag);
    }
    
    public function testAddAvailableRooms()
    {
        $roomRepository = $this->container->get('hotels.room.repository');
        $rooms = [];
        $rooms[] = [
            'name' => 'RoomTest',
            'identifier' => 'i_roomtest'
        ];
        
        $roomObjects = $roomRepository->addAvailableRooms($rooms);
        
        $flag = false;
        $roomObject = $roomRepository->checkExistsRoomByIdentifier('i_roomtest');
        if($roomObject instanceof Room) {
            $flag = true;
        }
        
        $this->assertEquals(true,$flag);
    }
    
    public function testDeleteExistsOffersByDate()
    {
        $offerRepository = $this->container->get('hotels.offer.repository');
        $date = '2016-02-26';
        $offerRepository->deleteExistsOffersByDate([
            'date' => $date
        ]);
        $offers = $offerRepository->findBy([
            'date' => $date
        ]);
        $this->assertCount(0,$offers);
    }
    
    public function testAddAvailableOffers()
    {
        $offerRepository = $this->container->get('hotels.offer.repository');
        
        $roomRepository = $this->container->get('hotels.room.repository');
        
        $date = '2016-02-26';
        $offer = new Offer();
        $offer->set('date',$date);
        $rooms = $roomRepository->getAvailableRooms($offer);
        $roomObjects = $roomRepository->addAvailableRooms($rooms);
        $params = [
            'date' => $date,
            'rooms' => $roomObjects
        ];
        $offerRepository->addAvailableOffers($params);
        
        $offers = $offerRepository->findBy([
            'date' => $date
        ]);
        
        
        $this->assertGreaterThan(0,$offers);
    }
    
    public function testDeleteOffer()
    {
        $offerRepository = $this->container->get('hotels.offer.repository');
        $roomRepository = $this->container->get('hotels.room.repository');
        
        $date = '2016-02-26';
        $offer = new Offer();
        $offer->set('date',$date);
        $rooms = $roomRepository->getAvailableRooms($offer);
        $roomObjects = $roomRepository->addAvailableRooms($rooms);
        $params = [
            'date' => $date,
            'rooms' => $roomObjects
        ];
        $offerRepository->addAvailableOffers($params);
        
        $offers = $offerRepository->findBy([
            'date' => $date
        ]);
        $id = $offers[0]->get('id');
        $offerRepository->deleteOffer($offers[0]);
        $offer = $offerRepository->find($id);
        $flag = false;
        
        if(!$offer instanceof Offer) {
            $flag = true;
        }
        
        $this->assertEquals(true,$flag);
    }
}
