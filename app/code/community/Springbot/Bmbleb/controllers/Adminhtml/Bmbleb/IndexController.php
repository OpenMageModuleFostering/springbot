<?php
class Springbot_Bmbleb_Adminhtml_Bmbleb_IndexController extends Mage_Adminhtml_Controller_Action
{

	protected function _init()
	{
		if(!Mage::helper('bmbleb/Account')->getIsAuthenticated()) {
			$this->_redirect('*/*/auth');
		} else if(
			$this->getRequest()->getParam('logout') &&
			Mage::helper('bmbleb/Account')->getIsAuthenticated()
		) {
			$this->_redirect('bmbleb/logout/logout');
			return;
		} elseif($this->getRequest()->getParam('harvest')) {
			$this->_redirect('*/*/index');
		} elseif($this->getRequest()->getParam('killharvest')) {
			Springbot_Boss::halt();
			$this->_redirect('*/*/status');
		} elseif ($problems = Mage::helper('bmbleb/PluginStatus')->getPluginProblems()) {
			$this->_redirect('*/adminhtml_problems/index');
		}
	}

	public function harvestAction()
	{
		try {
			Springbot_Cli::launchHarvestInline();
		}
		catch (Exception $e) {
			Springbot_Log::error($e);
		}
		$this->_redirect('*/*/status');
		return;
	}

	public function connectedtospringbotAction()
	{
		$this->_init();
		$this->loadLayout();
		$this->_setActiveMenu('springbot_bmbleb');
		$this->renderLayout();
		return;
	}

	public function loginAction()
	{
		$this->_init();
		$this->loadLayout();
		$this->_setActiveMenu('springbot_bmbleb');
		$this->renderLayout();
		return;
	}

	public function authAction()
	{
		$this->loadLayout();
		$this->_setActiveMenu('springbot_bmbleb');
		$this->renderLayout();
		return;
	}

	public function statusAction()
	{
		$this->_init();
		$this->loadLayout();
		$this->_setActiveMenu('springbot_bmbleb');
		$this->renderLayout();
		return;
	}

	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('springbot_bmbleb/dashboard');
	}

}
