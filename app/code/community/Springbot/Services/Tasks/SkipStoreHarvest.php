<?php

class Springbot_Services_Tasks_SkipStoreHarvest extends Springbot_Services
{
	public function run()
	{
		Springbot_Boss::haltStore($this->getStoreId());
	}
}
