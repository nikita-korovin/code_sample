<?php

namespace Classified\ErrorHandling\Service\Processor;

use Classified\ErrorHandling\DTO\ErrorInterface;

interface ErrorProcessorInterface
{
    /**
     * Send Error DTO either to DA or Notifications or both, depending on category.
     *
     * @param ErrorInterface $error
     */
    public function processError(ErrorInterface $error): void;
}
