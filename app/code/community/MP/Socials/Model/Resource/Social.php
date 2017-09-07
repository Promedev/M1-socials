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

/**
 * Class MP_Socials_Model_Resource_Social
 *
 * @category   MP
 * @package    MP_Socials
 * @author     Merchant Protocol Team <info@merchantprotocol.com>
 */
class MP_Socials_Model_Resource_Social extends Mage_Core_Model_Resource_Db_Abstract
{
    use MP_Socials_Trait;

    /**
     * MP_Socials_Model_Resource_Social constructor
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('mp_socials/social', 'entity_id');
    }

    /**
     * Load object data
     *
     * @param MP_Socials_Model_Social $object
     * @param mixed $value
     * @param string $field
     * @return $this
     */
    public function load(MP_Socials_Model_Social $object, $field, $value)
    {
        if (is_null($field)) {
            $field = $this->getIdFieldName();
        }

        $read = $this->_getReadAdapter();

        if ($read && !is_null($value)) {
            $select = $this->_getLoadSelect($field, $value, $object);

            if (!$object->hasAuthProvider()) {
                Mage::throwException($this->helper()->__('Auth Provider must be specified.'));
            }

            $select->where('auth_provider', (string) $object->getAuthProvider());

            if (!$this->getConfigShare()->isWebsiteScope()) {
                if (!$object->hasWebsiteId()) {
                    Mage::throwException($this->helper()->__('Website must be specified when using website scope.'));
                }

                $select->where('website_id', (int) $object->getWebsiteId());
            }

            $data = $read->fetchRow($select);

            if ($data) {
                $object->setData($data);
            }
        }

        $this->unserializeFields($object);
        $this->_afterLoad($object);

        return $this;
    }

    /**
     * Delete all social networks from the deleted customer
     *
     * @param int $customerId
     * @return $this
     * @throws Exception
     */
    public function deleteByCustomerId($customerId)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();

        try {
            $where = $adapter->quoteInto('customer_id = ?', $customerId);

            $adapter->delete($this->getMainTable(), $where);
            $adapter->commit();
        } catch (Exception $e) {
            $adapter->rollBack();

            throw $e;
        }

        return $this;
    }

    /**
     * Get config share model
     *
     * @return Mage_Customer_Model_Config_Share
     */
    protected function getConfigShare()
    {
        return Mage::getSingleton('customer/config_share');
    }
}
