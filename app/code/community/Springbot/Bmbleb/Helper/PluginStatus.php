<?php

class Springbot_Bmbleb_Helper_PluginStatus extends Mage_Core_Helper_Abstract
{

    public function needsToLogin()
    {
        if ($this->_emailPasswordSet()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Check to make sure user has logged in to avoid showing a problem notification before they even login
     */
    private function _emailPasswordSet()
    {
        if (
            Mage::getStoreConfig('springbot/config/account_email') &&
            Mage::getStoreConfig('springbot/config/account_password')
        ) {
            return true;
        } else {
            return false;
        }
    }

}
