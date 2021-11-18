<?php

namespace OCA\ReserveRoom\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class validUser extends Entity implements JsonSerializable {
	public $count;


	public function jsonSerialize(): array {
		return [
			'count' => $this->count
		];
	}
}
