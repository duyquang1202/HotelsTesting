<?php
namespace HotelsBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use HotelsBundle\Common\RestClient;
class OfferControllerFunctionalTest extends WebTestCase
{

    public function testAddOffers()
    {
        $domain = 'http://hotels.local';
        
        $endPointUri = '/api/offers';
        
        
        $restClient = new RestClient([
            'base_uri' => $domain
        ]);
     
        $formData = ['form_params' => [
            'date' => '2016-02-28'
        ]];
        $response = $restClient->run('post', $endPointUri, $formData);
      
        $httpStatus = $response->getStatusCode();
      
        if ($httpStatus != 200) {
            return [];
        }
        
        $response = json_decode($response->getBody(), true);
        $this->assertGreaterThan(0,count($response['data']));
        
    }
    
    public function testDeleteOffer()
    {
        $domain = 'http://hotels.local';
        
        $endPointUri = '/api/offers';
    
    
        $restClient = new RestClient([
            'base_uri' => $domain
        ]);
         
        $formData = ['form_params' => [
            'date' => '2016-02-28'
        ]];
        $response = $restClient->run('post', $endPointUri, $formData);
    
        $httpStatus = $response->getStatusCode();
    
        if ($httpStatus != 200) {
            return [];
        }
    
        $response = json_decode($response->getBody(), true);
        $id = $response['data'][0]['attributes']['id'];
        
        $newEndPointUri = $endPointUri.'/'.$id;
        
        
        $response = $restClient->run('delete', $newEndPointUri, []);
        
        $httpStatus = $response->getStatusCode();
        
        if ($httpStatus != 200) {
            return [];
        }
        $response = json_decode($response->getBody(), true);
        $this->assertEquals($id,$response['data']['id']);
    
    }
}
