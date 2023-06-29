<?php

namespace Classified\ErrorHandling\Producer;

use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Psr\Log\LoggerAwareTrait;
use Classified\ErrorHandling\DTO\ErrorInterface;
use Classified\ErrorHandling\Service\ErrorManager;

class ErrorCatchingProducer
{
    use LoggerAwareTrait;

    /**
     * @var ProducerInterface
     */
    protected $producer;

    /**
     * @var ErrorManager
     */
    protected $errorManager;

    /**
     * ErrorCatchingProducer constructor.
     *
     * @param ProducerInterface $producer
     * @param ErrorManager      $errorManager
     */
    public function __construct(ProducerInterface $producer, ErrorManager $errorManager)
    {
        $this->producer = $producer;
        $this->errorManager = $errorManager;
    }

    /**
     * @param ErrorInterface $error
     */
    public function publishError(ErrorInterface $error): void
    {
        $serializedError = $this->errorManager->serializeError($error);
        $this->producer->publish($serializedError);
        $this->logger->info(
            sprintf('Published error \'%s\'', $serializedError),
            [
                'error_id' => $error->getId(),
                'error_category' => $error->getCategory()->getName(),
            ]
        );
    }
}
