<?php

namespace OCA\ReserveRoom\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class ValidUserMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'reservation', validUser::class);
	}

	public function validUser($id, $u_id): validUser{
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('u_id')
              		->from('reservation')
			->where('id = '.$id);
		return $this->findEntity($qb);
	}

}
