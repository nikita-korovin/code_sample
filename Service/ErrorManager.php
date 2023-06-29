<?php

namespace Classified\ErrorHandling\Service;

use JMS\Serializer\Serializer;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Classified\ErrorHandling\DTO\ErrorInterface;
use Classified\ErrorHandling\DTO\Serializer\SerializerFactory;
use Classified\ErrorHandling\Entity\ErrorCategory;
use Classified\ErrorHandling\Exception\ErrorHandlingException;
use Classified\ErrorHandling\Exception\ValidationException;
use Classified\ErrorHandling\Service\Processor\ErrorProcessorInterface;
use Classified\WebsiteBundle\Provider\AccountAdapterProviderInterface;

/**
 * Class ErrorManager.
 */
class ErrorManager implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use LoggerAwareTrait;

    /**
     * @var ErrorInterface[]
     */
    private $errorDTOs;

    /**
     * @var array
     */
    private $serializers;

    /**
     * @var ErrorProcessorInterface[]
     */
    private $processors;

    /**
     * @var AccountAdapterProviderInterface Account adapter provider
     */
    private $accountAdapterProvider;

    /**
     * Constructor.
     *
     * @param AccountAdapterProviderInterface $accountAdapterProvider Account adapter provider
     */
    public function __construct(AccountAdapterProviderInterface $accountAdapterProvider)
    {
        $this->accountAdapterProvider = $accountAdapterProvider;
    }

    /**
     * @param string $categoryName
     * @param string $errorClass
     */
    public function registerErrorDTO(string $categoryName, string $errorClass): void
    {
        $this->errorDTOs[$categoryName] = $errorClass;
    }

    /**
     * @param SerializerFactory $serializerFactory
     * @param $alias
     */
    public function addSerializer(SerializerFactory $serializerFactory, string $alias): void
    {
        $this->serializers[$alias] = $serializerFactory->createSerializer();
    }

    /**
     * @param ErrorProcessorInterface $errorProcessor
     * @param $alias
     */
    public function addProcessor(ErrorProcessorInterface $errorProcessor, $alias): void
    {
        $this->processors[$alias][] = $errorProcessor;
    }

    /**
     * @param string $category
     *
     * @return array
     */
    public function getProcessorsByCategory(string $category): array
    {
        return $this->processors[$category];
    }

    /**
     * @param array $data
     *
     * @return ErrorInterface
     *
     * @throws ErrorHandlingException
     */
    public function loadError(array $data): ErrorInterface
    {
        $error = $this->getErrorDTOByCategory($data['category']);

        $data['categoryEntity'] = $this->getErrorCategoryByName($data['category']);

        $error->load($data);

        return $error;
    }

    /**
     * @param ErrorInterface $error
     *
     * @return string
     */
    public function serializeError(ErrorInterface $error): string
    {
        $serializer = $this->getSerializerByCategory($error->getCategory()->getName());

        return $serializer->serialize($error, 'json');
    }

    /**
     * @param string $error
     *
     * @return ErrorInterface
     *
     * @throws ValidationException
     * @throws ErrorHandlingException
     */
    public function deSerializeError(string $error): ErrorInterface
    {
        $arr = json_decode($error, true);

        $serializer = $this->getSerializerByCategory($arr['error_category']);
        $errorDTO = $this->getErrorDTOByCategory($arr['error_category']);
        $arr['error_category'] = $errorDTO::NAME;

        /** @var ErrorInterface $deserializedDTO */
        $deserializedDTO = $serializer->deserialize(json_encode($arr), get_class($errorDTO), 'json');
        $deserializedDTO->setAccountAdapterProvider($this->accountAdapterProvider);

        $this->validate($deserializedDTO);

        return $deserializedDTO;
    }

    /**
     * @param ErrorInterface $error
     */
    public function process(ErrorInterface $error): void
    {
        $processors = $this->getProcessorsByCategory($categoryName = $error->getCategory()->getName());

        foreach ($processors as $processor) {
            $processor->processError($error);

            $this->logger->info(
                sprintf('Processed %s error by %s', $error->getId(), get_class($processor)),
                [
                    'error_id' => $error->getId(),
                    'error_category' => $categoryName,
                ]
            );
        }
    }

    /**
     * @param $category
     *
     * @return Serializer|null
     */
    private function getSerializerByCategory(string $category): ?Serializer
    {
        return $this->serializers[$category] ?? null;
    }

    /**
     * @param string $name
     *
     * @return ErrorCategory
     */
    private function getErrorCategoryByName(string $name): ErrorCategory
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository(ErrorCategory::class);

        return $repo->findOneBy(['name' => $name]);
    }

    /**
     * @param string $category
     *
     * @return ErrorInterface
     *
     * @throws ErrorHandlingException
     */
    private function getErrorDTOByCategory(string $category): ErrorInterface
    {
        if (!isset($this->errorDTOs[$category])) {
            throw new ErrorHandlingException(sprintf('There is no error DTO registered for category "%s"', $category));
        }

        if (!$this->errorDTOs[$category] instanceof ErrorInterface) {
            $this->errorDTOs[$category] = new $this->errorDTOs[$category]($this->accountAdapterProvider);
        }

        return $this->errorDTOs[$category];
    }

    /**
     * @param ErrorInterface $error
     *
     * @throws ValidationException
     */
    private function validate(ErrorInterface $error): void
    {
        $validator = $this->container->get('validator');
        $validationErrors = $validator->validate($error);

        if (count($validationErrors) > 0) {
            throw new ValidationException((string) $validationErrors);
        }
    }
}
