<?php

class Springbot_Services_Harvest_Rules extends Springbot_Services_Harvest
{
	protected $_type = 'rules';

	public function run()
	{
		$collection = self::getCollection($this->getStoreId())
			->addFieldToFilter('rule_id', array('gt' => $this->getStartId()));
		$stopId = $this->getStopId();
		if ($stopId !== null) {
			$collection->addFieldToFilter('rule_id', array('lteq' => $this->getStopId()));
		}

		$this->_harvester = Mage::getModel('combine/harvest_rules')
			->setStoreId($this->getStoreId())
			->setDataSource($this->getDataSource())
			->setCollection($collection)
			->harvest();

		return parent::run();
	}

	public static function getCollection($storeId, $partition = null)
	{
		$websiteId = Mage::getModel('core/store')->load($storeId)->getWebsiteId();

		// Filter based on the website_ids string
		$collection = Mage::getModel('salesrule/rule')
			->getCollection()
			->addFieldToFilter('website_ids',
				array(
					array('like' => "%,{$websiteId},%"),
					array('like' => "{$websiteId},%"),
					array('like' => "%,{$websiteId}"),
					array('like' => "{$websiteId}"),
				)
			);

		if($partition) {
			$collection = parent::limitCollection($collection, $partition, 'rule_id');
		}
		return $collection;
	}
}
