<?php

class Springbot_Services_Post_Coupon extends Springbot_Services_Post
{
	public function run()
	{
		$coupon = Mage::getModel('salesrule/coupon')->load($this->getEntityId());
		$coupon->setStoreId($this->getStoreId());
		$harvester = Mage::getModel('combine/harvest_coupons');
		$harvester->push($coupon);
		$harvester->postSegment();
	}
}

