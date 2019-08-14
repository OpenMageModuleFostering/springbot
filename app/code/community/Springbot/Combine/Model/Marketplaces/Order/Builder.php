<?php

class Springbot_Combine_Model_Marketplaces_Order_Builder extends Varien_Object
{
    protected $_data;
    protected $_products;
    protected $_customer;
    protected $_storeId;
    protected $_order;
    protected $_mpOrder;
    protected $_qtyArray = array();

    protected $_shippingMethod = 'sbShipping';
    protected $_paymentMethod = 'sbPayment';
    protected $_itemTotals;

    public function __construct($data)
    {
        $this->_data = $data;
        $this->_helper = Mage::helper('combine/marketplaces');
    }

    public function buildOrder($products, $customer)
    {
        $customer->cleanAllAddresses();

        $this->_customer = $customer;
        $this->_products = $products;

        $this->_storeId = $customer->getStoreId();

        $this->makeOrder()
            ->setAddresses()
            ->setPayment()
            ->addProducts()
            ->setTotals();

        $this->makeMarketplaceOrder();

        $stock = Mage::getSingleton('cataloginventory/stock');

        $stockUpdated = false;

        try {
            $transaction = Mage::getModel('core/resource_transaction');

            $stock->registerProductsSale($this->_qtyArray);

            $stockUpdated = true;

            $transaction->addObject($this->_order)
                ->addObject($this->_mpOrder)
                ->addCommitCallback(array($this->_order, 'place'))
                ->addCommitCallback(array($this->_order, 'save'))
                ->save();
        } catch (Zend_Db_Statement_Exception $e) {
            if($stockUpdated) {
                $stock->revertProductsSale($this->_qtyArray);
            }
            throw new Exception("Order already exists for order with id {$this->_mpOrder->getRemoteOrderId()}", 409);
        }

        $this->_mpOrder->setOrderId($this->_order->getId())->save();

        return $this->_order;
    }

    private function makeMarketplaceOrder()
    {
        Springbot_Log::debug("Making mp order for {$this->fetch('amazon_order_id')}");
        $this->_mpOrder = Mage::getModel('combine/marketplaces_remote_order');
        $this->_mpOrder->setData(array(
            'increment_id' => $this->_order->getIncrementId(),
            'remote_order_id' => $this->fetch('amazon_order_id'),
            'marketplace_type' => Springbot_Combine_Model_Marketplaces_OrderService::AMAZON
        ));

        Springbot_Log::debug($this->_mpOrder->getData());

        return $this;
    }

    private function makeOrder()
    {
        $reservedOrderId = $this->reserveOrderId();

        $currencyCode = $this->getCurrencyCode();

        $this->_order = Mage::getModel('sales/order')
            ->setIncrementId($reservedOrderId)
            ->setStoreId($this->_customer->getStoreId())
            ->setQuoteId(0)
            ->setGlobalCurrencyCode($currencyCode)
            ->setBaseCurrencyCode($currencyCode)
            ->setStoreCurrencyCode($currencyCode)
            ->setOrderCurrencyCode($currencyCode)
            ;

        $this->_order->setCustomerEmail($this->_customer->getEmail())
            ->setCustomerFirstname($this->_customer->getFirstname())
            ->setCustomerLastname($this->_customer->getLastname())
            ->setCustomerGroupId($this->_customer->getGroupId())
            ->setCustomerIsGuest(0)
            ->setCustomer($this->_customer);

        return $this;
    }

    private function setTotals()
    {
        $total = $this->fetch('order_total->Amount');
        $subtotal = $this->getSubtotal();

        Springbot_Log::debug($this->getItemTotals());

        $this->_order->setSubtotal($subtotal)
            ->setBaseSubtotal($subtotal)
            ->setGrandTotal($total)
            ->setBaseGrandTotal($total)
            ->setTotalPaid($total)
            ->setBaseTotalPaid($total)
            ->setShippingAmount($this->getShipping())
            ->setBaseShippingAmount($this->getShipping())
            ->setShippingTaxAmount($this->getShippingTax())
            ->setBaseShippingTaxAmount($this->getShippingTax())
            ->setTaxAmount($this->getTax())
            ->setBaseTaxAmount($this->getTax())
            ;

        return $this;
    }

    private function setAddresses()
    {
        $billing = $this->_customer->getDefaultBillingAddress();
        $billingAddress = Mage::getModel('sales/order_address')
            ->setStoreId($this->_storeId)
            ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_BILLING)
            ->setCustomerId($this->_customer->getId())
            ->setCustomerAddressId($this->_customer->getDefaultBilling())
            ->setCustomerAddress_id($billing->getEntityId())
            ->setPrefix($billing->getPrefix())
            ->setFirstname($billing->getFirstname())
            ->setMiddlename($billing->getMiddlename())
            ->setLastname($billing->getLastname())
            ->setSuffix($billing->getSuffix())
            ->setCompany($billing->getCompany())
            ->setStreet($billing->getStreet())
            ->setCity($billing->getCity())
            ->setCountry_id($billing->getCountryId())
            ->setRegion($billing->getRegion())
            ->setRegion_id($billing->getRegionId())
            ->setPostcode($billing->getPostcode())
            ->setTelephone($billing->getTelephone())
            ->setFax($billing->getFax())
            ->setVatId($billing->getVatId());
        $this->_order->setBillingAddress($billingAddress);

        $shipping = $this->_customer->getDefaultShippingAddress();
        $shippingAddress = Mage::getModel('sales/order_address')
            ->setStoreId($this->_storeId)
            ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_SHIPPING)
            ->setCustomerId($this->_customer->getId())
            ->setCustomerAddressId($this->_customer->getDefaultShipping())
            ->setCustomer_address_id($shipping->getEntityId())
            ->setPrefix($shipping->getPrefix())
            ->setFirstname($shipping->getFirstname())
            ->setMiddlename($shipping->getMiddlename())
            ->setLastname($shipping->getLastname())
            ->setSuffix($shipping->getSuffix())
            ->setCompany($shipping->getCompany())
            ->setStreet($shipping->getStreet())
            ->setCity($shipping->getCity())
            ->setCountry_id($shipping->getCountryId())
            ->setRegion($shipping->getRegion())
            ->setRegion_id($shipping->getRegionId())
            ->setPostcode($shipping->getPostcode())
            ->setTelephone($shipping->getTelephone())
            ->setFax($shipping->getFax())
            ->setVatId($billing->getVatId());

        $this->_order->setShippingAddress($shippingAddress)
            ->setShippingMethod($this->_shippingMethod)
            ->setShippingDescription($this->fetch('shipment_service_level_category'));

        return $this;
    }

    private function setPayment()
    {
        $orderPayment = Mage::getModel('sales/order_payment')
            ->setStoreId($this->_customer->getStoreId())
            ->setCustomerPaymentId(0)
            ->setMethod($this->_paymentMethod)
            ->setPoNumber($this->safeFetch('purchase_order_number'));

        $this->_order->setPayment($orderPayment);

        return $this;
    }

    private function addProducts()
    {
        foreach ($this->_products as $product) {
            $item = Mage::getModel('combine/marketplaces_order_item')->makeOrderItem($product, $this->_data);
            $this->_order->addItem($item);
            $this->_addToStockQtyArray($item);
        }
        return $this;
    }

    private function getCurrencyCode()
    {
        if($value = $this->fetch('order_total->CurrencyCode')) {
            return $value;
        } else {
            return Mage::app()->getBaseCurrencyCode();
        }
    }

    private function reserveOrderId()
    {
        $transaction = Mage::getModel('core/resource_transaction');
        return Mage::getSingleton('eav/config')
            ->getEntityType('order')
            ->fetchNewIncrementId($this->_storeId);
    }

    private function getSubtotal()
    {
        $itemTotals = $this->getItemTotals();
        return $itemTotals['item_price'] + $itemTotals['tax'];
    }

    private function getShipping()
    {
        $itemTotals = $this->getItemTotals();
        return $itemTotals['shipping'];
    }

    private function getShippingTax()
    {
        $itemTotals = $this->getItemTotals();
        return $itemTotals['shipping_tax'];
    }

    private function getTax()
    {
        $itemTotals = $this->getItemTotals();
        return $itemTotals['tax'];
    }

    private function getItemTotals()
    {
        if(!isset($this->_itemTotals)) {
            $shipping = 0; $shippingTax = 0; $tax = 0; $itemPrice = 0;
            foreach($this->fetch('order_items') as $item) {
                $shipping += $this->_helper->safeFetch('shipping_price->Amount', $item);
                $shippingTax += $this->_helper->safeFetch('shipping_tax->Amount', $item);
                $tax += $this->_helper->safeFetch('item_tax->Amount', $item);
                $itemPrice += $this->_helper->safeFetch('item_price->Amount', $item);
            }
            $this->_itemTotals = array(
                'shipping' => $shipping,
                'shipping_tax' => $shippingTax,
                'tax' => $tax,
                'item_price' => $itemPrice
            );
            Springbot_Log::debug($this->_itemTotals);
        }
        return $this->_itemTotals;
    }

    private function safeFetch($key)
    {
        return $this->_helper->safeFetch($key, $this->_data);
    }

    private function fetch($key)
    {
        return $this->_helper->fetch($key, $this->_data);
    }

    /**
     * Prepare array with information about used product qty and product stock item
     * result is:
     * array(
     *  $productId  => array(
     *      'qty'   => $qty,
     *      'item'  => $stockItems|null
     *  )
     * )
     * @return array
     */
    private function _addToStockQtyArray($item)
    {
        $productId = $item->getProductId();
        $qty = $item->getQtyOrdered();
        $stockItem = $item->getProduct()->getStockItem();

        Springbot_Log::debug("Adding {$qty} of product {$productId}");

        if($qty && $productId)  {
            if(isset($this->_qtyArray[$productId])) {
                $this->_qtyArray[$productId]['qty'] += $qty;
            } else {
                $qtyItem = array(
                    'qty' => $qty,
                    'item' => $stockItem
                );
                $this->_qtyArray[$productId] = $qtyItem;
            }
        }
        return $this;
    }
}
