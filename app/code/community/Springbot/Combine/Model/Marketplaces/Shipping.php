<?php

class Springbot_Combine_Model_Marketplaces_Shipping
	extends Mage_Shipping_Model_Carrier_Abstract
	implements Mage_Shipping_Model_Carrier_Interface
{
	protected $_code = 'sbShipping';

	public function getAllowedMethods()
	{
		return array($this->_code => $this->getConfigData('name'));
	}

	public function isTrackingAvailable()
	{
		return false;
	}

	public function collectRates(Mage_Shipping_Model_Rate_Request $request)
	{
		return Mage::getModel('shipping/rate_result');
	}
}
