<?php

namespace App\Controller;

use AMQPChannelException;
use AMQPConnectionException;
use AMQPExchangeException;
use AMQPQueueException;
use App\Services\QueueBroker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * @throws AMQPQueueException
     * @throws AMQPExchangeException
     * @throws AMQPChannelException
     * @throws AMQPConnectionException
     */
    #[Route('/api/message', methods: ['GET'])]
    public function message (): JsonResponse
    {

        $rabbit = new QueueBroker();
        $rabbit->getExchange();
        $rabbit->getQueue();
        $message = $rabbit->getMsgToQueue();

        return $this->json([
            'message' => $message,
        ]);
    }
}