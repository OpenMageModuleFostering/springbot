<?php

class Springbot_Combine_Model_Marketplaces_Payment extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'sbPayment';

    protected $_canUseCheckout = false;
    protected $_canUseInternal = false;
    protected $_canUseForMultishipping = false;
}
