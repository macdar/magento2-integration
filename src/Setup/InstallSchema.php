<?php
namespace Synerise\Integration\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (!$installer->tableExists('synerise_cron_status')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('synerise_cron_status')
            )
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                    'ID'
                )
                ->addColumn(
                    'model',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    10,
                    ['nullable' => false],
                    'Data model'
                )
                ->addColumn(
                    'website_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    5,
                    ['nullable' => false, 'unsigned' => true],
                    'Website ID'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    5,
                    ['nullable' => false, 'unsigned' => true],
                    'Store ID'
                )
                ->addColumn(
                    'start_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true, 'unsigned' => true],
                    'Current ID'
                )
                ->addColumn(
                    'stop_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true, 'unsigned' => true],
                    'Stop ID'
                )
                ->addColumn(
                    'state',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    1,
                    ['nullable' => false, 'unsigned' => true],
                    'State'
                )
                ->addColumn(
                    'attempts',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => true, 'unsigned' => true],
                    'State'
                )
                ->addColumn(
                    'retry_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => true],
                    'Retry At'
                )
                ->addIndex(
                    $installer->getIdxName(
                        'synerise_cron_status',
                        ['model', 'website_id', 'store_id'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['model', 'website_id', 'store_id'],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->addIndex(
                    $installer->getIdxName(
                        'synerise_cron_status',
                        ['state'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                    ),
                    ['state'],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
                )
                ->setComment('Synerise cron status');

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('synerise_cron_queue')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('synerise_cron_queue')
            )
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                    'ID'
                )
                ->addColumn(
                    'model',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    10,
                    ['nullable' => false],
                    'Data model'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    5,
                    ['nullable' => false, 'unsigned' => true],
                    'Store ID'
                )
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'unsigned' => true],
                    'Entity ID'
                )
                ->addIndex(
                    $installer->getIdxName(
                        'synerise_cron_queue',
                        ['model', 'store_id', 'entity_id'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['model', 'store_id', 'entity_id'],
                    ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                )
                ->setComment('Synerise cron queue');

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('synerise_sync_subscriber')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('synerise_sync_subscriber')
            )
                ->addColumn(
                    'subscriber_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                    'Subscriber ID'
                )
                ->addColumn(
                    'synerise_updated_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Synerise Updated At'
                )
                ->setComment('Subscriber synchronisation status');

            $installer->getConnection()->createTable($table);
        }

        if (!$installer->tableExists('synerise_sync_order')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('synerise_sync_order')
            )
                ->addColumn(
                    'order_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                    'Order ID'
                )
                ->addColumn(
                    'synerise_updated_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                    'Synerise Updated At'
                )
                ->setComment('Subscriber synchronisation status');

            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
