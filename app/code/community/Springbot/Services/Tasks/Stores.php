<?php

class Springbot_Services_Tasks_Stores extends Springbot_Services
{
	public function run()
	{
		$stores = [];
		$helper = Mage::helper('combine/store');

		foreach (Mage::app()->getWebsites() as $website) {
			foreach ($website->getGroups() as $group) {
				foreach ($group->getStores() as $store) {
					$helper->setStore($store);
					$sbStoreId = $helper->getSpringbotStoreId();
					$sbStoreGuid = $helper->getGuid();

					$stores[] = array(
						"name"					=> $store->getName(),
						"code"					=> $store->getCode(),
						"url"					=> $store->getBaseUrl('link'),
						"secure_url"			=> $store->getBaseUrl('link', true),
						"media_url"				=> $store->getBaseUrl('media'),
						"website_id"			=> (int) $store->getWebsiteId(),
						"magento_store_id"		=> (int) $helper->getStoreId(),
						"springbot_store_id"	=> (isset($sbStoreId) ? (int) $sbStoreId : null),
						"springbot_store_guid"	=> (isset($sbStoreGuid) ? $sbStoreGuid : null)
					);
				}
			}
		}
		return $stores;
	}
}
