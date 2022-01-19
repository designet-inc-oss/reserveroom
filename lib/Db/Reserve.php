<?php

namespace OCA\ReserveRoom\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class Reserve extends Entity implements JsonSerializable {
	protected $uid;
	protected $facilId;
	protected $startDateTime;
	protected $endDateTime;
	protected $memo;
	protected $data;
	protected $displayname;
	public $count;

	public function jsonSerialize(): array{
		return [
			'id' => $this->id,
			'uid' => $this->uid,
			'facil_id' => $this->facilId,
			'start_date_time' => $this->startDateTime,
			'end_date_time' => $this->endDateTime,
			'memo' => $this->memo,
			'data' => $this->data,
			'displayname' => $this->displayname,
			'count' => $this->count
		];
	}
}

