<?php

namespace Arts\DeviceMockups\Base;

use ArtsDeviceMockups\Arts\Base\Containers\ManagersContainer as BaseManagersContainer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @property \Arts\DeviceMockups\Managers\Elementor $elementor
 * @property \Arts\DeviceMockups\Managers\Assets    $assets
 */
class ManagersContainer extends BaseManagersContainer {
}
