<?php

namespace Classified\ErrorHandling\Consumer;

use PhpAmqpLib\Message\AMQPMessage;
use Classified\ErrorHandling\Service\ErrorManager;
use Classified\ErrorHandling\Exception\ValidationException;
use Classified\ErrorHandling\Exception\ErrorHandlingException;
use Classified\Shared\Consumer\AbstractFailSafeConsumer;

/**
 * Class ErrorCatchingConsumer.
 */
class ErrorCatchingConsumer extends AbstractFailSafeConsumer
{

    /**
     * @var ErrorManager
     */
    protected $errorManager;

    /**
     * ErrorCatchingConsumer constructor.
     *
     * @param ErrorManager $errorManager
     */
    public function __construct(ErrorManager $errorManager)
    {
        $this->errorManager = $errorManager;
    }

    /**
     * @param AMQPMessage $msg
     *
     * @throws ValidationException
     * @throws ErrorHandlingException
     */
    public function safeExecute(AMQPMessage $msg)
    {
        $errorDTO = $this->errorManager->deSerializeError($msg->body);
        $this->logger->info(
            sprintf('Consumed %s error', $errorDTO->getId()),
            [
                'error_id' => $errorDTO->getId(),
                'error_category' => $errorDTO->getCategory()->getName(),
            ]
        );
        $this->errorManager->process($errorDTO);
    }
}
