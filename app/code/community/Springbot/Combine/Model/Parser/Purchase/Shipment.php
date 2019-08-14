<?php

class Springbot_Combine_Model_Parser_Purchase_Shipment extends Springbot_Combine_Model_Parser
{
	protected $_accessor = '_shipment';
	protected $_shipment;
	protected $_track;

	public function __construct(Mage_Sales_Model_Order_Shipment_Track $track)
	{
		$this->_track = $track;
		$this->_shipment = $track->getShipment();
		$this->_parse();
	}

	protected function _parse()
	{
			$this->_data = array(
				'tracking_number' => $this->_track->getTrackNumber(),
				'carrier_code' => $this->_track->getCarrierCode(),
				'title' => $this->_track->getTitle(),
				'ship_to' => $this->_getShippingName(),
				'shipment_status' => $this->_shipment->getShipmentStatus(),
				'items' => $this->_getShippedItems(),
			);
	}

	protected function _getShippingName()
	{
		return $this->_shipment->getShippingAddress()->getName();
	}

	protected function _getShippedItems()
	{
		$data = array();

		foreach($this->_shipment->getItemsCollection() as $item) {
			$data[] = array(
				'sku' => $item->getSku(),
				'name' => $item->getName(),
				'product_id' => $item->getProductId(),
				'qty' => $item->getQty(),
			);
		}

		return $data;
	}
}
