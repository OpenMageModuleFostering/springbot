<?php

class Springbot_Combine_Model_Rewrite extends Mage_Core_Model_Abstract
{
    /**
     * Create a new rewrite based on magento version
     *
     * @param  Mage_Core_Model_Store $store
     * @param  string $idPath
     * @param  string $requestPath
     * @param  string $targetPath
     * @return boolean
     */
    public function createRewrite($store, $idPath, $requestPath, $targetPath)
    {
        try {
            // check if community edition
            if ($this->isMageCommunity()) {
                // check if rewrites already exist
                $existingRewrite = Mage::getModel('core/url_rewrite')->loadByIdPath($idPath);

                // if they don't
                if ($existingRewrite->getUrlRewriteId() == null) {
                    Mage::getModel('core/url_rewrite')
                        ->setIsSystem(0)
                        ->setStoreId($store->getStoreId())
                        ->setOptions('RP')
                        ->setIdPath($idPath)
                        ->setRequestPath($requestPath)
                        ->setTargetPath($targetPath)
                        ->save();
                    return true;
                } else {
                    return false;
                }
            }

            // check if enterprise edition
            if ($this->isMageEnterprise()) {
                $existingRewrite = Mage::getModel('enterprise_urlrewrite/redirect')->getCollection()
                    ->addFieldToFilter('target_path')
                    ->getFirstItem();

                if (!$existingRewrite->getId()) {
                    Mage::getModel('enterprise_urlrewrite/redirect')
                        ->setStoreId($store->getStoreId())
                        ->setOptions('RP')
                        ->setIdentifier($idPath)
                        ->setRequestPath($requestPath)
                        ->setTargetPath($targetPath)
                        ->save();
                    return true;
                } else {
                    return false;
                }
            }
            return false;
        } catch (Exception $e) {
            Springbot_Log::error('Unable to create URL rewrite for store id: ' . 
                $store->getStoreId() . ' - ' . $requestPath . ' to ' . $targetPath . '');
            return false;
        }
    }

    /**
     * Delete a rewrite from the database based on magento version
     *
     * @param  int $urlRewriteId the rewrite id
     * @return boolean
     */
    public function deleteRewrite($urlRewriteId)
    {
        try {
            // check if community edition
            if ($this->isMageCommunity()) {
                // check if rewrite exists
                $existingRewrite = Mage::getModel('core/url_rewrite')->load($urlRewriteId);

                if ($existingRewrite->getStoreId() !== null) {
                    $existingRewrite->delete();
                    if (Mage::getModel('core/url_rewrite')->load($urlRewriteId)->getStoreId() == null) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }

            // check if enterprise edition
            if ($this->isMageEnterprise()) {
                // check if rewrite exists
                $existingRewrite = Mage::getModel('enterprise_urlrewrite/redirect')->load($urlRewriteId);

                if ($existingRewrite->exists()) {
                    $existingRewrite->delete();

                    if (!Mage::getModel('enterprise_urlrewrite/redirect')->load($urlRewriteId)->exists()) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }
            return false;
        } catch (Exception $e) {
            Springbot_Log::error("Unable to delete URL rewrite with id: " . $urlRewriteId . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * True if the version of Magento currently being run is Enterprise Edition
     *
     * @return boolean
     */
    public function isMageEnterprise()
    {
        return Mage::getConfig()->getModuleConfig('Enterprise_Enterprise')
            && Mage::getConfig()->getModuleConfig('Enterprise_AdminGws')
            && Mage::getConfig()->getModuleConfig('Enterprise_Checkout')
            && Mage::getConfig()->getModuleConfig('Enterprise_Customer');
    }

    /**
     * True if the version of Magento currently being run is Professional Edition
     *
     * @return boolean
     */
    public function isMageProfessional()
    {
        return Mage::getConfig()->getModuleConfig('Enterprise_Enterprise')
            && !Mage::getConfig()->getModuleConfig('Enterprise_AdminGws')
            && !Mage::getConfig()->getModuleConfig('Enterprise_Checkout')
            && !Mage::getConfig()->getModuleConfig('Enterprise_Customer');
    }

    /**
     * True if the version of Magento currently being run is Community Edition
     *
     * @return boolean
     */
    public function isMageCommunity()
    {
        return !$this->isMageEnterprise() && !$this->isMageProfessional();
    }
}
