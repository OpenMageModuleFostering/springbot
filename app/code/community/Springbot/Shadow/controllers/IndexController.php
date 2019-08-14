<?php
class Springbot_Shadow_IndexController extends Springbot_Shadow_Controller_Action
{
	/**
	 * Order of priority desc - "there can only be one!"
	 */
	private $_redirectIds = array(
		'sb',
		'redirect_mongo_id',
	);

	public function indexAction()
	{
		try {
			$params = $this->getRequest()->getParams();
			if (isset($params['run'])) {
				$this->runCaller();
			}
			else if (isset($params['email'])) {
				$this->emailCaller();
			}
			else if (isset($params['trackable']) && isset($params['type'])) {
				$this->trackableCaller();
			}
			else if (isset($params['healthcheck'])) {
				$this->healthcheckCaller();
			}
			else if (isset($params['harvest'])) {
				$this->harvestCaller();
			}
			else if (isset($params['jobs'])) {
				$this->jobsCaller();
			}
			else if (isset($params['delete_job'])) {
				$this->deleteJobCaller();
			}
			else if (isset($params['deliver_event_log'])) {
				$this->deliverEventLogCaller();
			}
			else if (isset($params['view_config'])) {
				$this->viewConfigCaller();
			}
			else if (isset($params['set_config'])) {
				$this->setConfigCaller();
			}
			else if (isset($params['view_log'])) {
				$this->viewLogCaller();
			}
			else if (isset($params['clear_cache'])) {
				$this->clearCacheCaller();
			}
			else if (isset($params['clear_stores'])) {
				$this->clearStoresCaller();
			}
			else if (isset($params['register_stores'])) {
				$this->registerStoresCaller();
			}
			else if (isset($params['debug'])) {
				$this->debugCaller();
			}
			else if (isset($params['clear_jobs'])) {
				$this->clearJobsCaller();
			}
			else if (isset($params['unlock_jobs'])) {
				$this->unlockJobsCaller();
			}
			else if (isset($params['reset_retries'])) {
				$this->resetRetries();
			}
		} catch (Exception $e) {
			$helper = Mage::helper('shadow/prattler');
			$this->getResponse()->setHeader('Content-type', 'application/json');
			$this->getResponse()->setBody($helper->getExceptionJsonResponse($e));
		}
	}

	private function runCaller()
	{
		if ($this->hasSbAuthToken()) {
			$helper = Mage::helper('shadow/prattler');
			$cronWorker = Mage::getModel('combine/cron_worker');
			$cronWorker->run();

			$this->getResponse()->setHeader('Content-type', 'application/json');
			$this->getResponse()->setBody($helper->getPrattlerJsonResponse());
		}
	}

	private function emailCaller()
	{
		if ($quote = Mage::getSingleton('checkout/session')->getQuote()) {
			$sessionQuoteExists = $quote->hasEntityId();

			// If there is no email address associated with the quote, check to see if one exists from our js listener
			if (!$quote->getCustomerEmail()) {
				$quote->setCustomerEmail($this->getRequest()->getParam('email'));
				$quote->save();
			}

			if (!$sessionQuoteExists) {
				Mage::getSingleton('checkout/session')->setQuoteId($quote->getId());
			}

			$this->getResponse()->setHeader('Content-type', 'application/json');
			$this->getResponse()->setBody('{}');
		}
	}

	private function trackableCaller()
	{
		if (Mage::getSingleton('customer/session')->isLoggedIn()) {
			$customerData = Mage::getSingleton('customer/session')->getCustomer();
			$customerId = $customerData->getId();
		}
		else {
			$customerId = null;
		}

		if ($quote = Mage::getModel('checkout/session')->getQuote()) {
			$quoteId = $quote->getId();
		}
		else {
			$quoteId = null;
		}

		Springbot_Boss::addTrackable(
			$this->getRequest()->getParam('type'),
			$this->getRequest()->getParam('trackable'),
			$quoteId,
			$customerId
		);
	}

	private function healthcheckCaller()
	{
		if ($this->hasSbAuthToken()) {
			$healthcheck = new Springbot_Services_Cmd_Healthcheck();

			if($this->getRequest()->getParam('store_id')) {
				$healthcheck->setStoreId($this->getRequest()->getParam('store_id'));
			}

			$healthcheck->run();
			$this->getResponse()->setHeader('Content-type', 'application/json');
			$this->getResponse()->setBody('{"success":true}');
		}
	}


	private function harvestCaller()
	{
		if ($this->hasSbAuthToken()) {
			$harvest = new Springbot_Services_Cmd_Harvest();
			$harvest->run();
			$this->getResponse()->setHeader('Content-type', 'application/json');
			$this->getResponse()->setBody('{"success":true}');
		}
	}

	private function jobsCaller()
	{
		if ($this->hasSbAuthToken()) {
			if (is_numeric($this->getRequest()->getParam('jobs'))) {
				$page = $this->getRequest()->getParam('jobs');
			}
			else {
				$page = 1;
			}
			$this->getResponse()->setHeader('Content-type', 'application/json');
			$collection = Mage::getModel('combine/cron_queue')->getCollection();
			$collection->setPageSize(20)->setCurPage($page);
			$this->getResponse()->setBody(json_encode($collection->toArray()));
		}
	}

	private function deleteJobCaller()
	{
		if ($this->hasSbAuthToken()) {
			if (is_numeric($this->getRequest()->getParam('delete_job'))) {
				$helper = Mage::helper('shadow/prattler');
				$resource = Mage::getModel('combine/cron_queue')->getResource();
				$resource->removeHarvestRow($this->getRequest()->getParam('delete_job'));
				$this->getResponse()->setHeader('Content-type', 'application/json');
				$this->getResponse()->setBody($helper->getPrattlerJsonResponse());
			}
		}
	}

	private function deliverEventLogCaller()
	{
		if ($this->hasSbAuthToken()) {
			$helper = Mage::helper('shadow/prattler');
			$deliverEventLog = new Springbot_Services_Tasks_DeliverEventLog();
			$deliverEventLog->run();
			$this->getResponse()->setHeader('Content-type', 'application/json');
			$this->getResponse()->setBody($helper->getPrattlerJsonResponse());
		}
	}

	private function viewConfigCaller()
	{
		if ($this->hasSbAuthToken()) {
			$this->getResponse()->setHeader('Content-type', 'application/json');
			$this->getResponse()->setBody(json_encode(Mage::getStoreConfig('springbot')));
		}
	}

	private function setConfigCaller()
	{
		if ($this->hasSbAuthToken()) {
			$this->getResponse()->setHeader('Content-type', 'application/json');
			$configKey = $this->getRequest()->getParam('set_config');
			$value = $this->getRequest()->getParam('value');
			if (isset($value)) {
				Mage::getModel('core/config')->saveConfig('springbot/' . $configKey, $value, 'default', 0);
				$this->getResponse()->setBody(json_encode(array('success' => true)));
			}
			else {
				$this->getResponse()->setBody(json_encode(array('success' => false)));
			}

		}
	}

	private function viewLogCaller()
	{
		if ($this->hasSbAuthToken()) {
			$logName = $this->getRequest()->getParam('view_log');
			$logName = str_replace('..', '', $logName);
			$logPath = Mage::getBaseDir('log') . DS . $logName;
			if (!is_file($logPath) || !is_readable($logPath)) {
				$this->getResponse()->setHeader('Content-type', 'application/json');
				$this->getResponse()->setBody('{"success": false}');
			}
			else {
				$this->getResponse()
					->setHttpResponseCode(200)
					->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true )
					->setHeader('Pragma', 'public', true )
					->setHeader('Content-type', 'application/force-download')
					->setHeader('Content-Length', filesize($logPath))
					->setHeader('Content-Disposition', 'attachment' . '; filename=' . basename($logPath) );
				$this->getResponse()->clearBody();
				$this->getResponse()->sendHeaders();
				readfile($logPath);
				exit;
			}
		}
	}

	private function clearCacheCaller()
	{
		if ($this->hasSbAuthToken()) {
			$this->getResponse()->setHeader('Content-type', 'application/json');
			try {
				$allTypes = Mage::app()->useCache();
				foreach($allTypes as $type => $blah) {
					Mage::app()->getCacheInstance()->cleanType($type);
				}
				$this->getResponse()->setBody(json_encode(array('success' => true)));
			}
			catch (Exception $e) {
				$this->getResponse()->setHeader('Content-type', 'application/json');
				$this->getResponse()->setBody(json_encode(
					array(
						'success' => false,
						'message' => $e->getMessage()
					)
				));
			}
		}
	}

	private function clearStoresCaller()
	{
		if ($this->hasSbAuthToken()) {
			$config = new Mage_Core_Model_Config();
			foreach (Mage::getStoreConfig('springbot/config') as $configKey => $configValue) {
				if (
					(substr($configKey, 0, strlen('store_id_')) == 'store_id_') ||
					(substr($configKey, 0, strlen('store_guid_')) == 'store_guid_') ||
					(substr($configKey, 0, strlen('security_token_')) == 'security_token_')
				) {
					Mage::getModel('core/config')->saveConfig('springbot/config/' . $configKey, null, 'default', 0);
				}
			}
			Mage::getConfig()->cleanCache();
			$this->getResponse()->setHeader('Content-type', 'application/json');
			$this->getResponse()->setBody(json_encode(array('success' => true)));
		}
	}

	private function registerStoresCaller()
	{
		if ($this->hasSbAuthToken()) {
			$service = new Springbot_Services_Store_Register;
			$helper =  Mage::helper('combine/harvest');
			foreach ($helper->getStoresToHarvest() as $store) {
				$service->setStoreId($store->getStoreId())->run();
			}
			Mage::getConfig()->cleanCache();
			$this->getResponse()->setHeader('Content-type', 'application/json');
			$this->getResponse()->setBody(json_encode(array('success' => true)));
		}
	}

	private function debugCaller()
	{
		if ($this->hasSbAuthToken()) {
			Mage::getConfig()->cleanCache();
			$this->getResponse()->setHeader('Content-type', 'application/json');
			$resource = Mage::getResourceModel('combine/debug');

			$this->getResponse()->setBody(json_encode(
				array(
					'customers' => $resource->getCustomersRaw(),
					'guests' => $resource->getGuestsRaw(),
					'subscribers' => $resource->getSubscribersRaw(),
					'products' => $resource->getProductsRaw(),
					'categories' => $resource->getCategoriesRaw(),
					'purchases' => $resource->getPurchasesRaw(),
					'carts' => $resource->getCartsRaw(),
				)
			));
		}
	}

	private function clearJobsCaller()
	{
		if ($this->hasSbAuthToken()) {
			$this->getResponse()->setHeader('Content-type', 'application/json');
			$resource = Mage::getResourceModel('combine/cron_queue');
			$resource->removeHarvestRows(null, false);
			$this->getResponse()->setBody(json_encode(array('success' => true)));
		}
	}

	private function unlockJobsCaller()
	{
		if ($this->hasSbAuthToken()) {
			$this->getResponse()->setHeader('Content-type', 'application/json');
			$resource = Mage::getResourceModel('combine/cron_queue');
			$resource->unlockOrphanedRows();
			$this->getResponse()->setBody(json_encode(array('success' => true)));
		}
	}

	private function resetRetries()
	{
		if ($this->hasSbAuthToken()) {
			$this->getResponse()->setHeader('Content-type', 'application/json');
			$resource = Mage::getResourceModel('combine/cron_queue');
			$resource->resetRetries();
			$this->getResponse()->setBody(json_encode(array('success' => true)));
		}
	}

	private function unlockActionsCaller()
	{
		if ($this->hasSbAuthToken()) {
			$this->getResponse()->setHeader('Content-type', 'application/json');
			$resource = Mage::getResourceModel('combine/action');
			$resource->unlockActionRows();
			$this->getResponse()->setBody(json_encode(array('success' => true)));
		}
	}


}
