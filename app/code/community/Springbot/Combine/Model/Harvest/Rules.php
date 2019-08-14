<?php

class Springbot_Combine_Model_Harvest_Rules extends Springbot_Combine_Model_Harvest_Abstract implements Springbot_Combine_Model_Harvester
{
	protected $_mageModel = 'salesrule/rule';
	protected $_parserModel = 'combine/parser_rule';
	protected $_apiController = 'promotions';
	protected $_apiModel = 'promotions';
	protected $_rowId = 'rule_id';

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
