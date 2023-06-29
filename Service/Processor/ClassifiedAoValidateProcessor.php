namespace Classified\WebsiteBundle\ErrorHandling\Processor;

use Classified\ErrorHandling\DTO\ErrorInterface;
use Classified\ErrorHandling\Service\Processor\ErrorProcessorInterface;
use Classified\WebsiteBundle\Managers\AlertsDataManager;

/**
 * Processes AO errors for Classified
 */
class ClassifiedAoValidateProcessor implements ErrorProcessorInterface
{
    /**
     * @var AlertsDataManager
     */
    private $alertsDataManager;

    /**
     * ErrorProcessor constructor.
     *
     * @param AlertsDataManager $alertsDataManager
     */
    public function __construct(AlertsDataManager $alertsDataManager)
    {
        $this->alertsDataManager = $alertsDataManager;
    }

    /**
     * @param ErrorInterface $error
     *
     * @throws \Exception
     */
    public function processError(ErrorInterface $error): void
    {
        $this->alertsDataManager->alertOnClassifiedValidationError($error);
    }
}
