<?php
namespace HotelsBundle\Controller;

use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\FlattenException as HttpFlattenException;
use Symfony\Component\Debug\Exception\FlattenException as DebugFlattenException;
use FOS\RestBundle\Controller\ExceptionController as BaseController;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Util\Codes;

class ExceptionController extends BaseController
{
    /**
     * Converts an Exception to a Response.
     *
     * @param Request                                    $request
     * @param HttpFlattenException|DebugFlattenException $exception
     * @param DebugLoggerInterface                       $logger
     * @param string                                     $format
     *
     * @return Response
     *
     * @throws \InvalidArgumentException
     */
    public function showAction(Request $request, $exception,
        DebugLoggerInterface $logger = null, $format = 'html')
    {
        if (!$exception instanceof DebugFlattenException
            && !$exception instanceof HttpFlattenException) {
            throw new \InvalidArgumentException(sprintf(
                'ExceptionController::showAction can only accept some exceptions (%s, %s), "%s" given',
                'Symfony\Component\HttpKernel\Exception\FlattenException',
                'Symfony\Component\Debug\Exception\FlattenException',
                get_class($exception)
            ));
        }

        $format = $this->getFormat($request, $format);
        if (null === $format) {
            $message = 'No matching accepted Response format could be determined, while handling: ';
            $message .= $this->getExceptionMessage($exception);

            return new Response($message, Codes::HTTP_NOT_ACCEPTABLE, $exception->getHeaders());
        }
        
        $currentContent = null;//$this->getAndCleanOutputBuffering();
        $code = $this->getStatusCode($exception);
        
        $viewHandler = $this->container->get('fos_rest.view_handler');
        $parameters = $this->getParameters(
            $viewHandler,
            $currentContent,
            $code,
            $exception,
            $logger,
            $format
        );
       
        try {
            if (!$viewHandler->isFormatTemplating($format)) {
                $parameters = $this->createExceptionWrapper($parameters);
            }
            if ($format === 'json') {
                $rawMessage = json_decode($parameters->getMessage());
                $errorKeyName = (is_array($rawMessage) && isset($rawMessage[0]))
                    ? $rawMessage[0]->message : $parameters->getMessage();
                $formatedMessage = array(
                    "status" => false,
                    "error" => array(
                        "message" =>  $this->container->get('translator')->trans(
                            $errorKeyName,
                            array(),
                            'messages'
                        )
                    )
                );
                $parameters = $formatedMessage;
            }
            $view = View::create($parameters, $code, $exception->getHeaders());
            $view->setFormat($format);

            if ($viewHandler->isFormatTemplating($format)) {
                $view->setTemplate($this->findTemplate(
                    $request,
                    $format,
                    $code,
                    $this->container->get('kernel')->isDebug()
                ));
            }

            $response = $viewHandler->handle($view);
        } catch (\Exception $e) {
            $message = 'An Exception was thrown while handling: ';
            $message .= $this->getExceptionMessage($exception);
            $response = new Response(
                $message,
                Codes::HTTP_INTERNAL_SERVER_ERROR,
                $exception->getHeaders());
        }

        return $response;
    }
}
