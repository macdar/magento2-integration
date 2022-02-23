<?php

namespace Synerise\Integration\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Synerise\ApiClient\Model\EventClientAction;
use Synerise\Integration\Helper\Api;
use Synerise\Integration\Helper\Customer;
use Synerise\Integration\Helper\Tracking;

class CustomerLogout implements ObserverInterface
{
    const EVENT = 'customer_logout';

    /**
     * @var Api
     */
    protected $apiHelper;

    /**
     * @var Customer
     */
    protected $customerHelper;

    /**
     * @var Tracking
     */
    protected $trackingHelper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        LoggerInterface $logger,
        Api $apiHelper,
        Tracking $trackingHelper,
        Customer $customerHelper
    ) {
        $this->logger = $logger;
        $this->apiHelper = $apiHelper;
        $this->trackingHelper = $trackingHelper;
        $this->customerHelper = $customerHelper;
    }

    public function execute(Observer $observer)
    {
        if (!$this->trackingHelper->isEventTrackingEnabled('customer_logout')) {
            return;
        }

        try {
            $customer = $observer->getEvent()->getCustomer();
            $eventClientAction = new EventClientAction([
                'time' => $this->trackingHelper->getCurrentTime(),
                'label' => $this->trackingHelper->getEventLabel(self::EVENT),
                'client' => $this->customerHelper->prepareIdentityParams(
                    $customer,
                    $this->trackingHelper->getClientUuid()
                ),
                'params' => array_merge(
                    [
                        "applicationName" => $this->trackingHelper->getApplicationName()
                    ],
                    $this->customerHelper->preapreAdditionalParams($customer)
                )
            ]);

            $this->apiHelper->getDefaultApiInstance()
                ->clientLoggedOut('4.4', $eventClientAction);

        } catch (\Exception $e) {
            $this->logger->error('Synerise Api request failed', ['exception' => $e]);
        }
    }
}
