<?php

class Springbot_Combine_Model_Harvest_Coupons extends Springbot_Combine_Model_Harvest_Abstract implements Springbot_Combine_Model_Harvester
{
	protected $_mageModel = 'salesrule/coupon';
	protected $_parserModel = 'combine/parser_coupon';
	protected $_apiController = 'coupons';
	protected $_apiModel = 'coupons';
	protected $_rowId = 'coupon_id';

	public function loadMageModel($entityId)
	{
		$this->_model = Mage::getModel($this->_getMageModel());
		$this->_model->setStoreId($this->_storeId);
		$this->_model->load($entityId);
		return $this->_model;
	}

	public function parse($model)
	{
		$model->setStoreId($this->_storeId);
		$parser = $this->_getParser($model)->parse($model);
		$parser->setDataSource($this->getDataSource());
		return $parser->getData();
	}

}
