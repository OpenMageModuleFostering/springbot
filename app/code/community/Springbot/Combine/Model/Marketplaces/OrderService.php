<?php

class Springbot_Combine_Model_Marketplaces_OrderService
{
	const AMAZON = 'amz';

	protected $_data;

	public function __construct($data)
	{
		$this->_data = $data;
	}

	public function createOrder()
	{
		$products = $this->loadProducts();

		$customer = $this->createCustomer();

		$builder = $this->getOrderBuilder();

		$order = $builder->buildOrder($products, $customer);

		return Mage::getModel('combine/parser_purchase', $order)->parse();
	}

	public function loadProducts()
	{
		return Mage::getModel('combine/marketplaces_order_parser', $this->_data)->loadProducts();
	}

	public function createCustomer()
	{
		return Mage::getModel('combine/marketplaces_order_customer', $this->_data)->createCustomer();
	}

	public function getOrderBuilder()
	{
		return Mage::getModel('combine/marketplaces_order_builder', $this->_data);
	}
}


