<?php

namespace Synerise\Integration\Controller\Adminhtml\Workspace;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Ui\Component\MassAction\Filter;
use Synerise\ApiClient\ApiException;
use Synerise\ApiClient\Model\ApiKeyPermissionCheckResponse;
use Synerise\Integration\Helper\Api;
use Synerise\Integration\Helper\Api\Factory\ApiKeyFactory;
use Synerise\Integration\Model\Workspace;
use Synerise\Integration\Model\ResourceModel\Workspace\CollectionFactory;


class MassUpdate extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level
     */
    const ADMIN_RESOURCE = 'Synerise_Integration::workspace';

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Filter
     */
    protected $filter;
    /**
     * @var Api
     */
    protected $apiHelper;

    /**
     * @var ApiKeyFactory
     */
    protected $apiKeyFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Api $apiHelper
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Api $apiHelper,
        ApiKeyFactory $apiKeyFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->apiHelper = $apiHelper;
        $this->apiKeyFactory = $apiKeyFactory;

        parent::__construct($context);
    }

    /**
     * Workspace delete action
     *
     * @return Redirect
     * @throws NotFoundException
     * @throws LocalizedException
     */
    public function execute(): Redirect
    {
        if (!$this->getRequest()->isPost()) {
            throw new NotFoundException(__('Page not found'));
        }
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $updated = 0;

        /** @var Workspace $workspace */
        foreach ($collection->getItems() as $workspace) {
            try {
                $this->update($workspace);
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
            }
            $updated++;
        }

        if ($updated) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been updated.', $updated)
            );
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }

    /**
     * @param $workspace
     * @throws ValidatorException
     * @throws ApiException
     */
    protected function update($workspace)
    {
        $permissionCheck = $this->checkPermissions($workspace->getApiKey());
        $missingPermissions = [];
        $permissions = $permissionCheck->getPermissions();
        foreach ($permissions as $permission => $isSet) {
            if (!$isSet) {
                $missingPermissions[] = $permission;
            }
        }

        $workspace
            ->setName($permissionCheck->getBusinessProfileName())
            ->setMissingPermissions(implode(PHP_EOL, $missingPermissions))
            ->save();
    }

    /**
     * @param string $apiKey
     * @return ApiKeyPermissionCheckResponse
     * @throws ApiException|ValidatorException
     */
    protected function checkPermissions(string $apiKey): ApiKeyPermissionCheckResponse
    {
        return $this->apiKeyFactory->create($this->apiHelper->getApiConfigByApiKey($apiKey))
            ->checkPermissions(Workspace::REQUIRED_PERMISSIONS);
    }
}