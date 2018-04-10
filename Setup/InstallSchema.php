<?php

namespace OuterEdge\Reviews\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();
        
        /**
         * Add `display_on_global_review_list` to table 'review'
         */
        $table = $installer->getConnection()->addColumn(
            $installer->getTable('review'),
            'display_on_global_review_list',
            [
                'type'     => Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => false,
                'default'  => 0,
                'comment'  => 'Display on Global Review List'
            ]
        );

        $setup->endSetup();
    }
}
