<?php

class Springbot_Shadow_Helper_Prattler extends Mage_Core_Helper_Abstract
{
	public function getPrattlerToken()
	{
		$storeId = Mage::app()->getStore()->getStoreId();
		$token = Mage::helper('bmbleb/account')->getSavedSecurityToken();
		return sha1($token);
	}

	public function getPrattlerJsonResponse()
	{
		$jobs = Mage::getModel('combine/cron_queue')->getCollection();
		$events = Mage::getModel('combine/action')->getCollection();
		$response = array(
			'success' => true,
			'jobs' => $jobs->getActiveCount(),
			'events' => $events->getSize()
		);
		return json_encode($response);
	}

	public function getExceptionJsonResponse($e)
	{
		return json_encode(array(
			'success' => false,
			'message' => $e->getMessage(),
		));
	}
}
