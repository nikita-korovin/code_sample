<?php

namespace Confidential\ErrorHandling\Service;

use Psr\Log\LoggerAwareTrait;
use Classified\ErrorHandling\Producer\ErrorCatchingProducer;
use Classified\ErrorHandling\Exception\ErrorHandlingException;
use Symfony\Component\Yaml\Yaml;

/**
 * Primary facade for using ErrorHandling
 *
 * Class ErrorCatcher
 */
class ErrorCatcher
{
    use LoggerAwareTrait;

    /**
     * @var ErrorCatchingProducer
     */
    protected $producer;

    /**
     * @var ErrorManager
     */
    protected $errorManager;

    /**
     * ErrorCatcher constructor.
     *
     * @param ErrorCatchingProducer $producer
     * @param ErrorManager          $errorManager
     */
    public function __construct(ErrorCatchingProducer $producer, ErrorManager $errorManager)
    {
        $this->producer = $producer;
        $this->errorManager = $errorManager;
    }

    /**
     * @param array $errorData
     *
     * @throws ErrorHandlingException
     */
    public function catch(array $errorData): void
    {
        $errorEntity = $this->errorManager->loadError($errorData);

        $this->producer->publishError($errorEntity);
    }
}
