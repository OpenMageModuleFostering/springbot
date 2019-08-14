<?php
class Springbot_Bmbleb_Adminhtml_Bmbleb_LoginController extends Mage_Adminhtml_Controller_Action
{

    public function loginAction()
    {
        $email = $this->getRequest()->getParam('email');
        $pass = $this->getRequest()->getParam('password');

        $bmblebAccount = Mage::helper('bmbleb/Account');
        $bmblebAccount->setIsLoggedIn(false);
        if (!($url = Mage::getStoreConfig('springbot/config/api_url'))) {
            $url = 'https://api.springbot.com/';
        }
        $url .= 'api/registration/login';

        try {
            $client = new Varien_Http_Client($url);
            $client->setRawData('{"user_id":"'.$email.'", "password":"'.$pass.'"}');
            $client->setHeaders('Content-type: application/json');
            $response = $client->request('POST');
            $result   = json_decode($response->getBody(),true);
        }
        catch (Exception $e) {
            Springbot_Log::error($e);
            Mage::getSingleton('adminhtml/session')->addError('Service unavailable from ' . $url . ' please contact support@springbot.com.');
            $this->_redirect('*/bmbleb_index/auth');
            return;
        }

        if ($result['status']=='error') {
            Mage::getSingleton('adminhtml/session')->addError($result['message'].' or service unavailable from '.$url);
            $this->_redirect('*/bmbleb_index/auth');
        }
        else {
            if ($result['token'] == '') {
                Mage::getSingleton('adminhtml/session')->addError('Login denied by Springbot');
                $this->_redirect('*/bmbleb_index/auth');
            }
            else {
                $bmblebAccount->setSavedAccountInformation($email,$pass,$result['token']);
                $this->_redirect('*/bmbleb_index/harvest');
            }
        }
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('springbot_bmbleb/dashboard');
    }


}
