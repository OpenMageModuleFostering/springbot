<?php

class Springbot_Combine_Model_Marketplaces_Order_Item
{
	private $_product;
	private $_data;

	public function makeOrderItem($product)
	{
		$this->_product = $product['product'];
		$this->_data = $product['data'];

		$this->setOptions();

		return $this->buildItem();
	}

	private function buildItem()
	{
		$product = $this->_product;
		$options = $product->getTypeInstance(true)->getOrderOptions($product);

		$qty = $this->fetch('quantity_ordered');
		$tax = $this->fetch('item_tax->Amount');
		$itemPrice = floatval($this->fetch('item_price->Amount'));

		if ($qty == 0) {
			throw new Exception("No quantity provided for product with sku: {$this->_product()->getSku()}");
		}

		$price = $itemPrice / $qty;
		$rowTotal = $itemPrice + $tax;

		$orderItem = Mage::getModel('sales/order_item')
			->setStoreId(0)
			->setQuoteItemId(0)
			->setQuoteParentItemId(NULL)
			->setProductId($product->getId())
			->setProductType($product->getTypeId())
			->setQtyBackordered(NULL)
			->setTotalQtyOrdered($qty)
			->setQtyOrdered($qty)
			->setName($product->getName())
			->setSku($product->getSku())
			->setPrice($price)
			->setBasePrice($price)
			->setTax($tax)
			->setBaseTax($tax)
			->setOriginalPrice($rowTotal)
			->setRowTotal($rowTotal)
			->setBaseRowTotal($rowTotal)

			->setWeeeTaxApplied(serialize(array()))
			->setBaseWeeeTaxDisposition(0)
			->setWeeeTaxDisposition(0)
			->setBaseWeeeTaxRowDisposition(0)
			->setWeeeTaxRowDisposition(0)
			->setBaseWeeeTaxAppliedAmount(0)
			->setBaseWeeeTaxAppliedRowAmount(0)
			->setWeeeTaxAppliedAmount(0)
			->setWeeeTaxAppliedRowAmount(0)

			->setProductOptions($options);

		return $orderItem;
	}

	private function setOptions()
	{
		$options = $this->_product->getCustomOptions();

		$optionsByCode = array();

		foreach ($options as $option)
		{
			$quoteOption = Mage::getModel('sales/quote_item_option')->setData($option->getData())
				->setProduct($option->getProduct());

			$optionsByCode[$quoteOption->getCode()] = $quoteOption;
		}

		$this->_product->setCustomOptions($optionsByCode);

		return $this;
	}

	private function fetch($keys)
	{
		return Mage::helper('combine/marketplaces')->fetch($keys, $this->_data);
	}
}

