<?php

namespace Synerise\Integration\Test\Integration\Observer\Event\Wishlist;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Event\Config as EventConfig;
use Magento\TestFramework\Helper\Bootstrap;
use Ramsey\Uuid\Uuid;
use Synerise\Integration\Helper\Api\Event\Favorites;
use Synerise\Integration\Helper\Event;
use Synerise\Integration\Observer\Event\Wishlist\AddProduct;

class AddProductObserverTest extends \PHPUnit\Framework\TestCase
{
    const STORE_ID = 1;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var EventConfig
     */
    private $eventConfig;

    /**
     * @var Favorites
     */
    private $favoritesHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Event
     */
    private $event;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->eventConfig = $this->objectManager->create(EventConfig::class);
        $this->productRepository = $this->objectManager->get(ProductRepository::class);
        $this->favoritesHelper = $this->objectManager->get(Favorites::class);
        $this->event = $this->objectManager->create(Event::class);
    }

    public function testObserverRegistration()
    {
        $observers = $this->eventConfig->getObservers('wishlist_add_product');

        $this->assertArrayHasKey('synerise_wishlist_add_product', $observers);
        $this->assertSame(
            AddProduct::class,
            $observers['synerise_wishlist_add_product']['instance']
        );
    }

    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSendAddToFavoritesEvent()
    {
        $product = $this->productRepository->get('configurable', true, null, true);

        $uuid = (string) Uuid::Uuid4();

        list ($body, $statusCode, $headers) = $this->event->sendEvent(
            AddProduct::EVENT,
            $this->favoritesHelper->prepareClientAddedProductToFavoritesRequest(
                AddProduct::EVENT,
                $product,
                $uuid
            ),
            self::STORE_ID
        );

        $this->assertEquals(202, $statusCode);
    }
}