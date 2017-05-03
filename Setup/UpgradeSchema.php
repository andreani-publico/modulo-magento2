<?php
/**
 * Author: Jhonattan Campo <jcampo@ids.net.ar>
 */
namespace Ids\Andreani\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class UpgradeSchema
 * @package Ids\Andreani\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if(version_compare($context->getVersion(), '1.0.1', '<')) {
            $guiaGenerada = $setup->getConnection()
                ->newTable($setup->getTable('ids_andreani_guia_generada'))
                ->addColumn(
                    'guia_id',
                    Table::TYPE_INTEGER,
                    11,
                    ['identity' => true, 'nullable' => false, 'primary' => true]

                )
                ->addColumn(
                    'fecha_generacion',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [
                        'nullable' => false,
                        'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
                    ]
                )
                ->addColumn(
                    'path_pdf',
                    Table::TYPE_TEXT,
                    200,
                    [
                        'nullable' => true,
                        'default' => null
                    ]
                )
                ->addColumn(
                    'shipment_increment_id',
                    Table::TYPE_TEXT,
                    2500,
                    [
                        'nullable' => true,
                        'default' => null
                    ]
                );

            $setup->getConnection()->createTable($guiaGenerada);

        }


        $setup->endSetup();
    }
}
