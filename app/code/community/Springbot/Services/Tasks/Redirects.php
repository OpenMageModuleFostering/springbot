<?php

class Springbot_Services_Tasks_Redirects extends Springbot_Services
{
    /**
     * View redirects in a paginated collection
     * @return array
     */
    public function run()
    {
        $request = Mage::app()->getRequest();

        switch ($request->getParam('action')) {
            case null:
                return $this->view();
                break;
            case 'view':
                return $this->view();
                break;
            case 'create':
                return $this->create($request);
                break;
            case 'delete':
                return $this->delete($request->getParam('id'));
                break;
            default:
                return $this->getMessage('error', 'Invalid action.');
                break;
        }
    }

    /**
     * View a paginated array of redirects
     * @return array
     */
    public function view()
    {
        $combineRewriteModel = Mage::getModel('combine/rewrite');
        $pageSize = ($this->getData('pageSize')) ? $this->getData('pageSize') : 10;

        if (!$page = $this->getData('page')) {
            $page = 1;
        }

        if ($combineRewriteModel->isMageCommunity()) {
            $model = Mage::getModel('core/url_rewrite');
            return $this->getPaginatedArray($model, $page, $pageSize);
        } elseif ($combineRewriteModel->isMageEnterprise()) {
            $model = Mage::getModel('enterprise_urlrewrite/redirect');
            return $this->getPaginatedArray($model, $page, $pageSize);
        } else {
            return $this->getMessage('error', 'Unable to determine Magento version');
        }
    }

    /**
     * Create a new redirect
     * @param  Mage_Api2_Model_Request $request
     * @return array
     */
    public function create($request)
    {
        $store = Mage::getModel('core/store')->load($request->getParam('store_id'));
        $createRewrite = Mage::getModel('combine/rewrite')->createRewrite(
            $store,
            $request->getParam('id_path'),
            $request->getParam('source'),
            $request->getParam('target')
        );

        if ($createRewrite) {
            return $this->getMessage('success', 'Redirect added successfully.');
        } else {
            return $this->getMessage('error', 'Unable to create redirect.');
        }
    }

    /**
     * Delete a redirect from the database
     * @param  integer $rewriteUrlId
     * @return array
     */
    public function delete($rewriteUrlId)
    {
        $combineRewriteModel = Mage::getModel('combine/rewrite');
        if ($combineRewriteModel->deleteRewrite($rewriteUrlId)) {
            return $this->getMessage('success', 'Redirect deleted for id: ' . $rewriteUrlId);
        } else {
            return $this->getMessage('error', 'Unable to delete redirect for id: ' . $rewriteUrlId);
        }
    }

    /**
     * Create a paginated area based on the model, the page to view, and the number of items on eah page
     *
     * @param  Mage_Core_Model_Abstract  $model    The model to paginate items from.
     * @param  integer $page     The current page number.
     * @param  integer $pageSize The number of items on each page.
     * @return array             An array with the count of items and a nested array containing those items paginated.
     */
    public function getPaginatedArray($model, $page = 1, $pageSize = 10)
    {
        $collection = $model->getCollection();
        $totalItems = $collection->count();
        $pages = ceil($totalItems / $pageSize);
        $page = ($page > $pages) ? $pages : $page;
        $offset = ($page - 1)  * $pageSize;
        $itemsArray = array('totalRecords' => (int) $totalItems, 'items' => array());

        if ($model->getResourceName() == 'core/url_rewrite') {
            foreach ($collection as $urlRewrite) {
                $item = array(
                    'id'            => (int) $urlRewrite->getUrlRewriteId(),
                    'store_id'      => (int) $urlRewrite->getStoreId(),
                    'identifier'    => $urlRewrite->getIdPath(),
                    'request_path'  => $urlRewrite->getRequestPath(),
                    'target_path'   => $urlRewrite->getTargetPath(),
                    'options'       => $urlRewrite->getOptions(),
                );
                $itemsArray['items'][] = $item;
            }
        } elseif ($model->getResourceName() == 'enterprise_urlrewrite/redirect') {
            foreach ($collection as $redirect) {
                $rewrite = Mage::getModel('enterprise_urlrewrite/url_rewrite')
                    ->loadByRequestPath($redirect->getIdentifier())
                    ->getCollection()
                    ->addFieldToFilter('store_id', $redirect->getStoreId())
                    ->getFirstItem();

                $item = array(
                    'id'            => (int) $redirect->getRedirectId(),
                    'store_id'      => (int) $redirect->getStoreId(),
                    'identifier'    => $redirect->getIdentifier(),
                    'request_path'  => $rewrite->getRequestPath(),
                    'target_path'   => $rewrite->getTargetPath(),
                    'options'       => $redirect->getOptions(),
                );
                
                $itemsArray['items'][] = $item;
            }
        }
        $itemsArray['items'] = array_slice($itemsArray['items'], $offset, $pageSize);
        return $itemsArray;
    }

    /**
     * Return a status message
     * @param  string $status
     * @param  string $msg
     * @return array
     */
    public static function getMessage($status, $msg)
    {
        return array(
            'status' => $status,
            'message' => $msg
        );
    }
}
