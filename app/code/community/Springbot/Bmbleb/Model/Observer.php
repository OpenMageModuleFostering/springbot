<?php
class Springbot_Bmbleb_Model_Observer
{
	public function getSalesOrderViewInfo(Varien_Event_Observer $observer)
	{
		$block = $observer->getBlock();
		if (
			($block->getNameInLayout() == 'order_info')
				&& ($child = $block->getChild('bmbleb.order.marketplaces'))
		) {
			if($child->isMarketplaces($block->getOrder())) {
				$transport = $observer->getTransport();
				if ($transport) {
					$html = $transport->getHtml();
					$html .= $child->toHtml();
					$transport->setHtml($html);
				}
			}
		}
	}
}
