<?php

namespace Arts\DeviceMockups\Base;

use ArtsDeviceMockups\Arts\Base\Plugins\BasePlugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Plugin extends BasePlugin {

	/** @var ManagersContainer */
	protected $managers;
}
