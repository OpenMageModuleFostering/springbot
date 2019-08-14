<?php

class Springbot_Combine_Model_Harvest_CustomerAttributeSets extends Springbot_Combine_Model_Harvest
{
	protected $_helper;

	public function getMageModel()
	{
		return 'eav/entity_attribute_set';
	}

	public function getParserModel()
	{
		return 'combine/parser_customerAttributeSet';
	}

	public function getApiController()
	{
		return 'attribute_sets';
	}

	public function getApiModel()
	{
		return 'attribute_sets';
	}

	public function getRowId()
	{
		return 'attribute_set_id';
	}


	public function loadMageModel($id)
	{
		return $this->_getHelper()->getAttributeSetById($id);
	}

	protected function _getHelper()
	{
		if(!isset($this->_helper)) {
			$this->_helper = Mage::helper('combine/attributes');
		}
		return $this->_helper;
	}

}
