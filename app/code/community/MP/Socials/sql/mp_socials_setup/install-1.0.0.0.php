<?php
/**
 * Merchant Protocol
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Merchant Protocol Commercial License (MPCL 1.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://merchantprotocol.com/commercial-license/
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@merchantprotocol.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to http://www.merchantprotocol.com for more information.
 *
 * @category   MP
 * @package    MP_Socials
 * @copyright  Copyright (c) 2006-2017 Merchant Protocol LLC. and affiliates (https://merchantprotocol.com/)
 * @license    https://merchantprotocol.com/commercial-license/  Merchant Protocol Commercial License (MPCL 1.0)
 */

/* @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

/**
 * Create table 'mp_socials/social'
 */
$tableName = $installer->getTable('mp_socials/social');

if (!$installer->getConnection()->isTableExists($tableName)) {
    $table = $installer->getConnection()
        ->newTable($tableName)
        ->addColumn(
            'entity_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            10,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true
            ],
            'Relationship ID'
        )
        ->addColumn(
            'customer_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            10,
            [
                'unsigned' => true,
                'nullable' => true
            ],
            'Customer Id'
        )
        ->addColumn(
            'auth_id',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            null,
            [
                'unsigned' => true,
                'nullable' => true
            ],
            'Auth ID'
        )
        ->addColumn(
            'auth_token',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            null,
            [
                'unsigned' => true,
                'nullable' => true
            ],
            'Auth Token'
        )
        ->addColumn(
            'auth_provider',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            255,
            [
                'unsigned' => true,
                'nullable' => true
            ],
            'Auth Provider'
        )
        ->addColumn(
            'website_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            10,
            [
                'default'  => 0,
                'unsigned' => true,
                'nullable' => false
            ],
            'Website'
        )
        ->addColumn(
            'created_at',
            Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            null,
            [
                'nullable' => false,
                'default'  => Varien_Db_Ddl_Table::TIMESTAMP_INIT
            ],
            'Created At'
        )
        ->addColumn(
            'updated_at',
            Varien_Db_Ddl_Table::TYPE_TIMESTAMP,
            null,
            [
                'nullable' => false,
                'default'  => Varien_Db_Ddl_Table::TIMESTAMP_INIT_UPDATE
            ],
            'Updated At'
        );

    $installer->getConnection()->createTable($table);
}

$installer->endSetup();
