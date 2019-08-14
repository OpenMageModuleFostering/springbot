<?php

class Springbot_Services_Work_Restart extends Springbot_Services_Abstract
{
	public function run()
	{
		$status = Mage::getModel('combine/cron_manager_status');

		if($this->getForce()) {
			$this->_getStatus()->removeWorkBlocker();
		}

		if($status->isActive()) {
			$status->haltManager();
		}
		Springbot_Boss::startWorkManager();
	}

}
