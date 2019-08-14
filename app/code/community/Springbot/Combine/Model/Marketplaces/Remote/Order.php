<?php

class Springbot_Combine_Model_Marketplaces_Remote_Order extends Springbot_Combine_Model_Cron
{
	public function _construct()
	{
		$this->_init('combine/marketplaces_remote_order');
	}

	public function findByIncrementId($id)
	{
		$instance = $this->getCollection()->addFieldToFilter('increment_id', $id)->getFirstItem();

		# Return null if we get a blank object
		return $instance->getId() == null ? null : $instance;
	}

	public function getHumanMarketplaceType()
	{
		if($this->getMarketplaceType() == 'amz') {
			return 'Amazon';
		}
	}
}
