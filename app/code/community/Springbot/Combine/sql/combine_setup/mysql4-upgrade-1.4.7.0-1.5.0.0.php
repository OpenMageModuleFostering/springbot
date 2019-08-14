<?php

$installer = $this;
/* @var $installer Springbot_Combine_Model_Resource_Setup */

$installer->startSetup();

$installer->getConnection()->beginTransaction();

$session = Mage::getSingleton('core/session');

$table = $installer->getTable('combine/marketplaces_remote_order');

try {
	$installStr = "
		CREATE TABLE IF NOT EXISTS `{$table}`
		(
			`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			`order_id` INT(11) NULL,
			`increment_id` VARCHAR(50) NOT NULL,
			`remote_order_id` VARCHAR(50) NULL,
			`marketplace_type` VARCHAR(50) NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `UNQ_REMOTE_ORDER_ID` (`remote_order_id`),
			UNIQUE KEY `UNQ_INCREMENT_ID` (`increment_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;
	";

	Springbot_Log::debug($installStr);

	$installer->run($installStr);

} catch (Exception $e) {
	Springbot_Log::error('Springbot 1.4.7.0-1.5.0.0 update failed!');
	Springbot_Log::error(new Exception('Install failed clear and retry. ' . $e->getMessage()));
	if (!$session->getSbReinstall()) {
		$session->setSbReinstall(true);
		$installer->reinstallSetupScript('1.4.7.0', '1.5.0.0');
	}
}

$installer->endSetup();
