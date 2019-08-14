<?php

class Springbot_Combine_Helper_Marketplaces extends Mage_Core_Helper_Abstract
{
	public function fetch($keys, $obj = null, $origKey = null)
	{
		if(is_null($obj)) {
			return null;
		}
		if(is_null($origKey)) {
			$origKey = $keys;
		}
		if(!is_array($keys)) {
			$keys = explode('->', $keys);
		}

		$key = array_shift($keys);

		if(!isset($obj[$key])) {
			throw new Exception("Missing required value for key {$origKey}", 422);
		}

		if(count($keys) > 0) {
			return $this->fetch($keys, $obj[$key], $origKey);
		} else {
			return $obj[$key];
		}
	}

	public function safeFetch($keys, $obj = null, $origKey = null)
	{
		try {
			return $this->fetch($keys, $obj, $origKey);
		} catch (Exception $e) {
			return null;
		}
	}
}
