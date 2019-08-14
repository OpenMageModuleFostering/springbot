<?php

/**
 * Class: Springbot_Util_Cache
 *
 * @author Springbot Magento Integration Team <magento@springbot.com>
 * @version 1.4.0.0
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Springbot_Util_Cache
{
	const CACHE_TAG = 'springbot_cache';

	public function __construct($appCache = null)
	{
		$this->_appCache = $appCache;
	}

	public static function set($key, $value)
	{
		$instance = new Springbot_Util_Cache();
		$instance->_save($key, $value);
	}

	public static function get($key)
	{
		$instance = new Springbot_Util_Cache();
		return $instance->_load($key);
	}

	public static function clean()
	{
		$instance = new Springbot_Util_Cache();
		$instance->_clean();
	}

	protected function _save($key, $value)
	{
		$this->_getAppCache()->save($value, $key, array(self::CACHE_TAG));
	}

	protected function _load($key)
	{
		return $this->_getAppCache()->load($key);
	}

	protected function _clean()
	{
		return $this->_getAppCache()->clean(
			Zend_Cache::CLEANING_MODE_MATCHING_TAG,
			array(self::CACHE_TAG)
		);
	}

	private function _getAppCache()
	{
		if(!isset($this->_appCache)) {
			$this->_appCache = Mage::app()->getCache();
		}
		return $this->_appCache;
	}

}
