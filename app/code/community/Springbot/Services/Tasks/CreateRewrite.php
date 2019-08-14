<?php

class Springbot_Services_Tasks_CreateRewrite extends Springbot_Services
{
    private $_requiredParams = ['store_id', 'id_path', 'request_path', 'target_path'];

    public function run() {
        try {
            if (
                ($storeId = $this->getData('store_id')) &&
                ($idPath = $this->getData('id_path')) &&
                ($requestPath = $this->getData('request_path')) &&
                ($targetPath = $this->getData('target_path'))
            ) {
                if ($store = Mage::getModel('core/store')->load($storeId)) {
                    Mage::getModel('combine/rewrite')->createRewrite($store, $idPath, $requestPath, $targetPath);
                    return [
                        'success' => true,
                        'message' => "Created rewrite"
                    ];
                }
                else {
                    return $this->_showError("Could not load store with id {$storeId}");
                }
            }
            else {
                return $this->_showError("Required params: " . implode(', ', $this->_requiredParams));
            }
        }
        catch (Exception $e) {
            return $this->_showError("Unable to create URL rewrite for store id: " . $e->getMessage());
        }
    }

    private function _showError($errorMessage) {
        Springbot_Log::error($errorMessage);
        return [
            'success' => false,
            'message' => $errorMessage
        ];
    }

}




