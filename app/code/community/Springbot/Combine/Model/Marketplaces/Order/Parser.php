<?php

class Springbot_Combine_Model_Marketplaces_Order_Parser extends Varien_Object
{
	protected $_data;

	public function __construct($data)
	{
		$this->_data = $data;
	}

	public function loadProducts()
	{
		$products = array();

		foreach($this->fetch('order_items') as $item) {
			$id = $this->fetch('product_id', $item);
			$product = Mage::getModel('catalog/product')->load($id);

			if(is_null($product->getId())) {
				throw new Exception("Could not find product where id = {$id}", 409);
			}

			$products[] = array(
				'data' => $item,
				'product' => $product
			);
		}

		return $products;
	}

	private function fetch($keys, $obj = null, $origKey = null)
	{
		if(is_null($obj)) {
			$obj = $this->_data;
		}

		return Mage::helper('combine/marketplaces')->fetch($keys, $obj);
	}
}
