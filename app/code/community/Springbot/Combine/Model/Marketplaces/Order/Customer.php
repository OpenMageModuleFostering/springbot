<?php

class Springbot_Combine_Model_Marketplaces_Order_Customer extends Varien_Object
{
	protected $_data;
	protected $_region;

	public function __construct($data)
	{
		$this->_data = $data;
	}

	public function createCustomer()
	{
		if ($customer = $this->existingCustomer()) {
			// nop
		} else {
			$password = Mage::helper('core')->getRandomString(6);

			$customer = Mage::getModel('customer/customer')
				->setData('firstname', $this->getFirstname())
				->setData('lastname', $this->getLastname())
				->setData('website_id', 0)
				->setData('group_id', 0)
				->setData('email', $this->fetch('buyer_email'))
				->setData('confirmation', $password);

			$customer->setPassword($password);
			$customer->save();
		}

		$customerAddress = Mage::getModel('customer/address')
			->setFirstname($this->getFirstname())
			->setLastname($this->getLastname())
			->setCompany($this->getCompany())
			->setRegion($this->getRegion())
			->setRegionId($this->getRegionId())
			->setCountryId($this->safeFetch('shipping_address->CountryCode'))
			->setCity($this->safeFetch('shipping_address->City'))
			->setPostcode($this->safeFetch('shipping_address->PostalCode'))
			->setPhone($this->safeFetch('shipping_address->Phone'))
			->setStreet($this->getStreet())
			->setCustomer($customer)
			->setIsDefaultBilling(true)
			->setIsDefaultShipping(true);

		$customerAddress->save();

		$customer->setDefaultBilling($customerAddress->getEntityId())
			->setDefaultShipping($customerAddress->getEntityId());

		$customer->save();

		Springbot_Log::debug($customer->debug());

		return $customer;
	}

	private function getCompany()
	{
		if($this->fetch('buyer_name') != $this->fetch('shipping_address->Name')) {
			return $this->fetch('shipping_address->Name');
		}
	}

	public function getFirstname()
	{
		return preg_replace('/\s.*$/', '', $this->fetch('buyer_name'));
	}

	public function getLastname()
	{
		return str_replace($this->getFirstname(), '', $this->fetch('buyer_name'));
	}

	private function getStreet()
	{
		return $this->fetch('shipping_address->AddressLine1')
			. PHP_EOL . $this->safeFetch('shipping_address->AddressLine2')
			. PHP_EOL . $this->safeFetch('shipping_address->AddressLine3');
	}

	private function getRegion()
	{
		if(!isset($this->_region)) {
			$this->_region = Mage::getModel('directory/region')->loadByName(
				$this->fetch('shipping_address->StateOrRegion'),
				$this->fetch('shipping_address->CountryCode')
			);

			// If we can't load by region, fail over and try to load by code
			if($this->_region->getId() == null) {
				$this->_region = Mage::getModel('directory/region')->loadByCode(
					$this->fetch('shipping_address->StateOrRegion'),
					$this->fetch('shipping_address->CountryCode')
				);
			}
		}
		return $this->_region;
	}

	private function getRegionId()
	{
		if($region = $this->getRegion()) {
			return $region->getId();
		}
		return null;
	}

	private function safeFetch($keys)
	{
		return Mage::helper('combine/marketplaces')->safeFetch($keys, $this->_data);
	}

	private function fetch($keys, $obj = null, $origKey = null)
	{
		if(is_null($obj)) {
			$obj = $this->_data;
		}
		return Mage::helper('combine/marketplaces')->fetch($keys, $this->_data);
	}

	private function existingCustomer()
	{
		$customer = Mage::getModel('customer/customer');
		$customer->setWebsiteId(0);
		$customer->loadByEmail($this->fetch('buyer_email'));

		if ($customer->getId()) {
			return $customer;
		} else {
			return false;
		}
	}
}
