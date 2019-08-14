<?php

class Springbot_Util_Timer
{
	const DEFAULT_POLLING_INTERVAL = 5; // minutes

	protected $_task;
	protected $_storeId;
	protected $_interval;

	public function __construct($task, $storeId, $interval = null)
	{
		$this->_task = $task;
		$this->_storeId = $storeId;
		$this->_interval = is_null($interval) ? $this->_getQueryInterval() : $interval;
	}

	public static function fire($task, $storeId, $interval = null)
	{
		$ins = new Springbot_Util_Timer($task, $storeId, $interval);
		if($ins->doRunTask()) {
			Springbot_Log::debug("Firing $task for store_id: $storeId");
			$ins->_setLastRunAt();
			return true;
		} else {
			return false;
		}
	}

	public static function lastRun($task, $storeId)
	{
		$ins = new Springbot_Util_Timer($task, $storeId);
		return $ins->_getLastRunAt();
	}

	public function doRunTask()
	{
		$intervalDiff = (time() - $this->_getLastRunAt()) / 60;
		return $intervalDiff > $this->_interval;
	}

	public function _getKey()
	{
		return $this->_task . '_' . $this->_storeId;
	}

	protected function _getLastRunAt()
	{
		Springbot_Log::debug('Getting: ' . $this->_getKey());
		return Springbot_Util_Cache::get($this->_getKey());
	}

	protected function _setLastRunAt()
	{
		$time = (string) time();
		Springbot_Log::debug('Setting: ' . $this->_getKey() . ' => ' . $time);
		Springbot_Util_Cache::set($this->_getKey(), $time);
	}

	protected function _getQueryInterval()
	{
		$interval = Mage::getStoreConfig('springbot/config/query_interval');
		if(empty($interval) || !isset($interval)) {
			$interval = self::DEFAULT_POLLING_INTERVAL;
		}
		return $interval;
	}
}
