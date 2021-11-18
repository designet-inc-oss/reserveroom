<?php

namespace OCA\ReserveRoom\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class Facil extends Entity implements JsonSerializable {
	protected $sortNum;
	protected $facilName;
	public $count;


	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'sort_num' => $this->sortNum,
			'facil_name' => $this->facilName,
			'count' => $this->count
		];
	}
}
