<?php

class Springbot_Api_V1Controller extends Springbot_Shadow_Controller_Action
{
	public function ordersAction()
	{
		try {
			$this->_auth();

			switch ($this->getRequest()->getMethod()) {
				case 'POST':
					$this->_createOrder();
					break;
				case 'GET':
					$this->_viewOrder();
					break;
			}
		} catch (Exception $e) {

			$out = array('success' => "false", 'error' => $e->getMessage());

			$code = $e->getCode() == 0 ? 400 : $e->getCode();

			$this->_respondWith($out, $code);
		}
	}

	public function _viewOrder()
	{
		$id = $this->getRequest()->getParam('id');
		$out = '';

		if ($id) {
			$order = Mage::getModel('sales/order')->load($id);
			$out = Mage::getModel('combine/parser_purchase', $order)->parse();
		}

		$this->_respondWith($out);
	}

	protected function _createOrder()
	{
		$post = file_get_contents("php://input");
		$post = Mage::helper('core')->jsonDecode($post);

		$service = Mage::getModel('combine/marketplaces_orderService', $post);

		$out = $service->createOrder();

		$this->_respondWith($out);
	}

	protected function _respondWith($out, $code = 200)
	{
		$json = Mage::helper('core')->jsonEncode($out);

		$this->getResponse()->clearHeaders()
			->setHeader('Content-type', 'application/json')
			->setHeader('HTTP/1.0', $code, true)
			->setBody($json);
	}

	protected function _auth()
	{
		$helper = Mage::helper('shadow/prattler');
		$token = $helper->getPrattlerToken();
		$headerToken = $this->getRequest()->getHeader('Springbot-Security-Token');

		if (!$headerToken) {
			Springbot_Log::debug('Could not load security token to authenticated jobs endpoint');
			throw new Exception('null token', 401);
		} elseif ($token != $headerToken) {
			Springbot_Log::debug('Supplied security token hash does not match');
			throw new Exception('token mismatch', 401);
		}
	}
}
