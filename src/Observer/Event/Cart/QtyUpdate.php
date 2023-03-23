<?php

namespace Synerise\Integration\Observer\Event\Cart;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;

class QtyUpdate extends Status implements ObserverInterface
{
    const EVENT = 'checkout_cart_update_items_after';

    public function execute(Observer $observer)
    {
        if (!$this->isLiveEventTrackingEnabled(self::EVENT)) {
            return;
        }

        if ($this->identityHelper->isAdminStore()) {
            return;
        }

        try {
            /** @var Quote $quote */
            $quote = $observer->getCart()->getQuote();
            $quote->collectTotals();

            $request = $this->cartHelper->prepareCartStatusRequest(
                $quote,
                $this->identityHelper->getClientUuid()
            );

            $this->publishOrSendEvent(self::EVENT, $request, $quote->getStoreId());


        } catch (Exception $e) {
            $this->logger->error('Synerise Error', ['exception' => $e]);
        }
    }
}
