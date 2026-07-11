<?php

namespace Arts\DeviceMockups\Base;

use ArtsDeviceMockups\Arts\Base\Managers\BaseManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Manager extends BaseManager {

	/** @var ManagersContainer|null */
	protected $managers;
}
