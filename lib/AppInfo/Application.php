<?php

namespace OCA\ReserveRoom\AppInfo;

use OCP\AppFramework\App;

class Application extends App {
	public const APP_ID = 'reserveroom';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}
}
