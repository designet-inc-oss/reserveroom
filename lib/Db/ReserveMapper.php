<?php

namespace OCA\ReserveRoom\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class ReserveMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'reservation', Reserve::class);
	}

	/**
         * @param int $id
         * @return Entity|Reserve
         * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
         * @throws DoesNotExistException
         */
        public function find($id): Reserve {
		/* @var $qb IQueryBuilder */
                $qb = $this->db->getQueryBuilder();
                $qb->select('id')
                        ->from('reservation')
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
                return $this->findEntity($qb);
        }



	/**
	 * @return Entity|Reserve
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 * date変数は"YYYY-MM-DD"のみで送られてくると想定
	 * 
	 */
	public function findAll($date, $newDate){
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('r.*')
			->addSelect('u.data')
     			->from('reservation', 'r')
			->join('r', 'accounts', 'u', 'u.uid = r.uid')
			->where('NOT ('.'start_date_time >='.'"'.$newDate.'"'.' OR '.'end_date_time <='.'"'.$date.'")');

                $cursor = $qb->execute();
                $entities = [];
                while ($row = $cursor->fetch()) {
                        $json_data = json_decode($row["data"]);
                        $row["displayname"] = $json_data->{"displayname"}->{"value"};
                        unset($row["data"]);
                        $entities[] = $this->mapRowToEntity($row);
                }

                $cursor->closeCursor();
		return $entities;
	}

	public function deplicateReserve($start_date_time, $end_date_time, $facil_id){
                /* @var $qb IQueryBuilder */
                $qb = $this->db->getQueryBuilder();
                $qb->selectAlias($qb->createFunction('COUNT(*)'), 'count')
                        ->from('reservation')
			->where(" NOT (".'start_date_time >='."'".$end_date_time."'"." OR "."end_date_time <="."'".$start_date_time."')")
			->andWhere("facil_id ="."'".$facil_id."'");

                return $this->findEntity($qb);
	}


	public function deplicateReserveUpdate($id, $start_date_time, $end_date_time, $facil_id){
                /* @var $qb IQueryBuilder */
                $qb = $this->db->getQueryBuilder();
                $qb->selectAlias($qb->createFunction('COUNT(*)'), 'count')
                        ->from('reservation')
                        ->where(" NOT (".'start_date_time >='."'".$end_date_time."'"." OR "."end_date_time <="."'".$start_date_time."')")
			->andWhere("facil_id ="."'".$facil_id."'")
			->andWhere("id <>".$id);

                return $this->findEntity($qb);
        }



	public function validUser($id, $u_id){
                /* @var $qb IQueryBuilder */
                $qb = $this->db->getQueryBuilder();
                $qb->selectAlias($qb->createFunction('COUNT(*)'), 'count')
                        ->from('reservation')
			->where("uid ="."'".$u_id."'")
			->andWhere("id=".$id);

                return $this->findEntity($qb);
        }
}
