<?php

class Springbot_Services_Post_Rule extends Springbot_Services_Post
{
	public function run()
	{
		$rule = Mage::getModel('salesrule/rule')->load($this->getEntityId());
		$rule->setStoreId($this->getStoreId());
		$harvester = Mage::getModel('combine/harvest_rules');
		$harvester->push($rule);
		$harvester->postSegment();
	}
}

