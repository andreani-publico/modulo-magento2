<?php

namespace Ids\Andreani\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class InstallSchema
 *
 * @description Instalador de tablas. Equivalente a los installer de magento 1.
 * @author Mauro Maximiliano Martinez <mmartinez@ids.net.ar>
 * @package Ids\Andreani\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @description Instalador de las tablas:
     *                                      - ids_andreani_provincia
     *                                      - ids_andreani_zona
     *                                      - ids_andreani_tarifa
     *                                      - ids_andreani_codigo_postal
     *                                      - ids_andreani_sucursal
     *
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $idsAndreaniProvincia = $installer->getConnection()
            ->newTable($installer->getTable('ids_andreani_provincia'))
            ->addColumn(
                'provincia_id',
                Table::TYPE_SMALLINT,
                6,
                ['identity' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn('nombre', Table::TYPE_TEXT, 100, ['nullable' => true, 'default' => null]);

        $installer->getConnection()->createTable($idsAndreaniProvincia);

        $idsAndreaniZona = $installer->getConnection()
            ->newTable($installer->getTable('ids_andreani_zona'))
            ->addColumn(
                'zona_id',
                Table::TYPE_SMALLINT,
                6,
                ['identity' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn('nombre', Table::TYPE_TEXT, 40, ['nullable' => false]);

        $installer->getConnection()->createTable($idsAndreaniZona);

        $idsAndreaniTarifa = $installer->getConnection()
            ->newTable($installer->getTable('ids_andreani_tarifa'))
            ->addColumn(
                'tarifa_id',
                Table::TYPE_SMALLINT,
                6,
                ['identity' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn('rango', Table::TYPE_DECIMAL, '10,2', ['nullable' => false])
            ->addColumn('valor_estandar', Table::TYPE_DECIMAL, '10,2', ['nullable' => false])
            ->addColumn('valor_sucursal', Table::TYPE_DECIMAL, '10,2', ['nullable' => true,'default'=>null])
            ->addColumn('valor_urgente', Table::TYPE_DECIMAL, '10,2', ['nullable' => true,'default'=>null])
            ->addColumn('zona_id', Table::TYPE_SMALLINT, 6 , ['nullable' => false])
            ->addIndex(
                $installer->getIdxName('ids_andreani_tarifa', ['zona_id']),
                ['zona_id']
            )
            ->addForeignKey(
                $installer->getFkName('ids_andreani_tarifa', 'zona_id', 'ids_andreani_zona', 'zona_id'),
                'zona_id',
                'ids_andreani_zona',
                'zona_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );

        $installer->getConnection()->createTable($idsAndreaniTarifa);


        $idsAndreaniCodigoPostal = $installer->getConnection()
            ->newTable($installer->getTable('ids_andreani_codigo_postal'))
            ->addColumn(
                'codigo_postal_id',
                Table::TYPE_INTEGER,
                6,
                ['identity' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn('sucursal', Table::TYPE_TEXT, 40, ['nullable' => false])
            ->addColumn('localidad', Table::TYPE_TEXT, 40, ['nullable' => false])
            ->addColumn('codigo_postal', Table::TYPE_INTEGER, 6, ['nullable' => false])
            ->addColumn('provincia_id', Table::TYPE_SMALLINT, 6 , ['nullable' => false])
            ->addColumn('zona_id', Table::TYPE_SMALLINT, 6 , ['nullable' => false])
            ->addIndex(
                $installer->getIdxName('ids_andreani_codigo_postal', ['provincia_id']),
                ['provincia_id']
            )
            ->addIndex(
                $installer->getIdxName('ids_andreani_codigo_postal', ['zona_id']),
                ['zona_id']
            )
            ->addForeignKey(
                $installer->getFkName('ids_andreani_codigo_postal', 'provincia_id', 'ids_andreani_provincia', 'provincia_id'),
                'provincia_id',
                'ids_andreani_provincia',
                'provincia_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('ids_andreani_codigo_postal', 'zona_id', 'ids_andreani_zona', 'zona_id'),
                'zona_id',
                'ids_andreani_zona',
                'zona_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );

        $installer->getConnection()->createTable($idsAndreaniCodigoPostal);

        $idsAndreaniSucursal = $installer->getConnection()
            ->newTable($installer->getTable('ids_andreani_sucursal'))
            ->addColumn(
                'sucursal_id',
                Table::TYPE_SMALLINT,
                6,
                ['identity' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn('nombre', Table::TYPE_TEXT, 40, ['nullable' => false])
            ->addColumn('direccion', Table::TYPE_TEXT, 60, ['nullable' => false])
            ->addColumn('telefono', Table::TYPE_TEXT, 60, ['nullable' => true,'default'=>null])
            ->addColumn('codigo_postal', Table::TYPE_INTEGER, 6, ['nullable' => false])
            ->addColumn('provincia_id', Table::TYPE_SMALLINT, 6 , ['nullable' => false])
            ->addColumn('codigo_sucursal', Table::TYPE_SMALLINT, 6 , ['nullable' => true,'default'=>null])
            ->addIndex(
                $installer->getIdxName('ids_andreani_sucursal', ['provincia_id']),
                ['provincia_id']
            )
            ->addForeignKey(
                $installer->getFkName('ids_andreani_sucursal', 'provincia_id', 'ids_andreani_provincia', 'provincia_id'),
                'provincia_id',
                'ids_andreani_provincia',
                'provincia_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );

        $installer->getConnection()->createTable($idsAndreaniSucursal);

        $column = [
            'type'    => Table::TYPE_BLOB,
            'length'  => '',
            'nullable'=> true,
            'comment' => 'Data del WS para generar la guía PDF.',
            'default' => null
        ];
        $installer->getConnection()->addColumn($installer->getTable('sales_shipment'), 'andreani_datos_guia', $column);

        $column = [
            'type'    => Table::TYPE_INTEGER,
            'nullable'=> true,
            'comment' => 'Codigo de sucursal andreani',
            'default' => null
        ];
        $installer->getConnection()->addColumn($installer->getTable('quote'), 'codigo_sucursal_andreani', $column);

        $column = [
            'type'    => Table::TYPE_INTEGER,
            'nullable'=> true,
            'comment' => 'Codigo de sucursal andreani',
            'default' => null
        ];
        $installer->getConnection()->addColumn($installer->getTable('sales_order'), 'codigo_sucursal_andreani', $column);

        $column = [
            'type'    => Table::TYPE_INTEGER,
            'nullable'=> true,
            'comment' => 'Numero de documento del cliente',
            'default' => null
        ];
        $installer->getConnection()->addColumn($installer->getTable('sales_order'), 'customer_dni', $column);

        $column = [
            'type'    => Table::TYPE_INTEGER,
            'nullable'=> true,
            'comment' => 'Numero de documento del cliente',
            'default' => null
        ];
        $installer->getConnection()->addColumn($installer->getTable('sales_order_address'), 'dni', $column);

        $column = [
            'type'    => Table::TYPE_INTEGER,
            'nullable'=> true,
            'comment' => 'Numero de documento del cliente',
            'default' => null
        ];
        $installer->getConnection()->addColumn($installer->getTable('quote_address'), 'dni', $column);

        $column = [
            'type'    => Table::TYPE_INTEGER,
            'nullable'=> true,
            'comment' => 'Celular del cliente',
            'default' => null
        ];
        $installer->getConnection()->addColumn($installer->getTable('sales_order_address'), 'celular', $column);
        $installer->getConnection()->addColumn($installer->getTable('quote_address'), 'celular', $column);


        $column = [
            'type'    => Table::TYPE_INTEGER,
            'nullable'=> true,
            'comment' => 'Altura de la calle de la dirección del cliente',
            'default' => null
        ];
        $installer->getConnection()->addColumn($installer->getTable('sales_order_address'), 'altura', $column);
        $installer->getConnection()->addColumn($installer->getTable('quote_address'), 'altura', $column);


        $column = [
            'type'    => Table::TYPE_INTEGER,
            'nullable'=> true,
            'comment' => 'Número del piso la dirección del cliente',
            'default' => null
        ];
        $installer->getConnection()->addColumn($installer->getTable('sales_order_address'), 'piso', $column);
        $installer->getConnection()->addColumn($installer->getTable('quote_address'), 'piso', $column);



        $column = [
            'type'    => Table::TYPE_TEXT,
            'nullable'=> true,
            'comment' => 'Departamento del piso la dirección del cliente',
            'default' => null
        ];
        $installer->getConnection()->addColumn($installer->getTable('sales_order_address'), 'departamento', $column);
        $installer->getConnection()->addColumn($installer->getTable('quote_address'), 'departamento', $column);


        $column = [
            'type'    => Table::TYPE_TEXT,
            'nullable'=> true,
            'comment' => 'Observaciones del cliente',
            'default' => null
        ];
        $installer->getConnection()->addColumn($installer->getTable('sales_order_address'), 'observaciones', $column);
        $installer->getConnection()->addColumn($installer->getTable('quote_address'), 'observaciones', $column);

        $installer->endSetup();
    }

}