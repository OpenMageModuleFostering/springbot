<?php

class Springbot_Services_Harvest_Coupons extends Springbot_Services_Harvest
{
	protected $_type = 'coupons';

	public function run()
	{
		$collection = self::getCollection($this->getStoreId())
			->addFieldToFilter('coupon_id', array('gt' => $this->getStartId()));
		$stopId = $this->getStopId();
		if ($stopId !== null) {
			$collection->addFieldToFilter('coupon_id', array('lteq' => $this->getStopId()));
		}

		$this->_harvester = Mage::getModel('combine/harvest_coupons')
			->setStoreId($this->getStoreId())
			->setDataSource($this->getDataSource())
			->setCollection($collection)
			->harvest();

		return parent::run();
	}

	public static function getCollection($storeId, $partition = null)
	{
		// Filter based on the website_ids string
		$collection = Mage::getModel('salesrule/coupon')->getCollection();

		if($partition) {
			$collection = parent::limitCollection($collection, $partition, 'coupon_id');
		}
		return $collection;
	}
}
