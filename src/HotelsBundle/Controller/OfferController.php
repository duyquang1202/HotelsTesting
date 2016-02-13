<?php
namespace HotelsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use HotelsBundle\Entity\Offer;

class OfferController extends FOSRestController implements ClassResourceInterface
{
    
/**
     * Find rooms by date
     * <br>
     *
     * @Get("/offers/find", name = "find", options = {"method_prefix" = false})
     * @QueryParam(name="date", description="date to find rooms", nullable=false)
     * @param ParamFetcherInterface $paramFetcher param fetcher service
     * @author Quang Vo <quang.vo@audiencemedia.com>
     * @return array
     */
    public function cgetAction(ParamFetcherInterface $paramFetcher)
    {
        $roomRepository = $this->get('hotels.room.repository');
        $params = $paramFetcher->all();
        if(!isset($params['date'])) {
             throw new NotFoundHttpException('offer.date.not_blank');
        }
        $rooms = $roomRepository->findRooms($params);
        return ['status' => true, 'data' => $rooms];
    }
    
    /**
     * Add offers
     * <br>
     *
     *
     * @Post("/offers", name = "create", options = {"method_prefix" = false})
     *
     * @param Request $request
     *            param request to create new catalog
     * @author Quang Vo <duyquang1202@gmail.com>
     * @return array
     */
    public function postAction(Request $request)
    {
       
        $headers = $request->headers->all();
        $data = $request->request->all();
        $offer = new Offer();
        $offer->setData($data);
        $errors = $this->container->get('validator')->validate($offer);
        
        if ($errors->count() > 0) {
            throw new BadRequestHttpException($this->get('jms_serializer')->serialize($errors, 'json'));
        }
        
        $roomRepository = $this->get('hotels.room.repository');
        $offerRepository = $this->get('hotels.offer.repository');
        
        $rooms = $roomRepository->getAvailableRooms($offer);
        
        $roomObjects = $roomRepository->addAvailableRooms($rooms);
        
        $params = [
            'date' => $offer->get('date'),
            'rooms' => $roomObjects
        ];
        
        $offers = $offerRepository->addAvailableOffers($params);
        $data = [];
        foreach ($offers as $offer) {
            $data[] = [
                'type' => 'offer',
                'attributes' => [
                    'id' => $offer->get('id'),
                    'date' => $offer->get('date')
                ],
                'relationships' => [
                    'room' => $offerRepository->getFinalResultByJMSGroup($offer->get('room'), 'view')
                ]
            ];
        }
        if (isset($headers['accept'][0]) && strtolower($headers['accept'][0]) == 'text/html') {
            return $this->render('HotelsBundle:Offer:post.html.twig', [
                'data' => $data,
                'date' => $offer->get('date')
            ]);
        }
        
        return [
            'status' => true,
            'data' => $data
        ];
    }

    /**
     * Delete Offer
     * <br>
     *
     * @Delete("/offers/{id}", name = "delete", options = {"method_prefix" = false})
     * 
     * @param int $id
     *            id of offer that is needed to delete
     * @author Quang Vo <duyquang1202@gmail.com>
     * @return array
     */
    public function deleteAction($id)
    {
        $offerRepository = $this->get('hotels.offer.repository');
        $offer = $offerRepository->find($id);
        if (! $offer instanceof Offer) {
            throw new NotFoundHttpException('offer.id.not_found');
        }
        $id = $offer->get('id');
        $offerRepository->deleteOffer($offer);
        return [
            'status' => true,
            'data' => [
                'type' => 'offer',
                'id' => $id
            ]
        ];
    }
}
