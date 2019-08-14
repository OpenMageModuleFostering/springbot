<?php

class Springbot_Services_Tasks_PluginVersion extends Springbot_Services
{
	/**
	 * Return the Plugin Version Number. Echo out and throw an Exception if the value is empty.
	 * 
	 * @return array
	 */
	public function run()
	{
		$version = array('plugin_version' => (string) Mage::getConfig()->getModuleConfig("Springbot_Combine")->version);
		if (empty($version)) {
			throw new Exception('Plugin version is empty.');
		}
		return $version;
	}
}
