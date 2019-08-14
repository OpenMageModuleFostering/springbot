<?php

class Springbot_Bmbleb_Adminhtml_Bmbleb_LoginController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Attempt to login a user using the requested information
     */
    public function loginAction()
    {
        // Pull request params
        $email = $this->getRequest()->getParam('email');
        $pass = $this->getRequest()->getParam('password');

        // Set helper data and configure API URL
        $bmblebAccount = Mage::helper('bmbleb/Account');
        $bmblebAccount->setIsLoggedIn(false);
        if (!($url = Mage::getStoreConfig('springbot/config/api_url'))) {
            $url = 'https://api.springbot.com/';
        }
        $url .= 'api/registration/login';

        try {
            // Initialize cURL target URL
            $c = curl_init('https://api.springbot.com/api/registration/login');

            // Build cURL query
            curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($c, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($c, CURLOPT_POSTFIELDS, "{\"user_id\":\"$email\", \"password\":\"$pass\"}");

            // Save the response/result
            $response = curl_exec($c);
            $result = json_decode($response, true);
        } catch (Exception $e) {
            // Notify user the API service is unavailable
            Springbot_Log::error($e->getMessage());
            Mage::getSingleton('adminhtml/session')
                ->addError('Service unavailable from ' . $url . ' please contact support@springbot.com.');
            $this->_redirect('*/bmbleb_index/auth');

            return;
        }

        // Notify user of any error
        if ($result['status'] == 'error') {
            Mage::getSingleton('adminhtml/session')
                ->addError($result['message'] . ' or service unavailable from ' . $url);
            $this->_redirect('*/bmbleb_index/auth');
        } else {
            // Notify user of denied login attempt
            if ($result['token'] == '') {
                Mage::getSingleton('adminhtml/session')
                    ->addError('Login denied by Springbot');
                $this->_redirect('*/bmbleb_index/auth');
            } // Redirect successful login to Springbot Dashboard
            else {
                $bmblebAccount->setSavedAccountInformation($email, $pass, $result['token']);
                $this->_redirect('*/bmbleb_index/harvest');
            }
        }
    }

    // Make sure user is authorized to access this page
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
                   ->isAllowed('springbot_bmbleb/dashboard');
    }

}
