<?php

class Springbot_Bmbleb_Block_Adminhtml_Order_Marketplaces extends Mage_Core_Block_Template
{
	protected $_order;
	protected $_mpOrder;

	public function getOrder()
	{
		if (is_null($this->_order)) {
			if (Mage::registry('current_order')) {
				$order = Mage::registry('current_order');
			}
			elseif (Mage::registry('order')) {
				$order = Mage::registry('order');
			}
			else {
				$order = new Varien_Object();
			}
			$this->_order = $order;
		}
		return $this->order;
	}

	public function isMarketplaces($order)
	{
		Springbot_Log::debug($order->debug());
		return $this->getMarketplacesOrder($order) != null;
	}

	public function getMarketplacesOrder($order = null)
	{
		if(is_null($this->_mpOrder)) {
			if(!is_null($order) && is_null($this->_order)) {
				$this->_order = $order;
			}
			$this->_mpOrder = Mage::getModel('combine/marketplaces_remote_order')
				->findByIncrementId($this->_order->getIncrementId());
		}
		return $this->_mpOrder;
	}
}
