<?php

namespace OCA\ReserveRoom\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\DB\QueryBuilder\IFunctionBuilder;
class FacilMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'facility', Facil::class);
	}
	
	/**
         * @param int $facil_id
         * @return Entity|Facil
         * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
         * @throws DoesNotExistException
         */
        public function find($facil_id){
                /* @var $qb IQueryBuilder */
                $qb = $this->db->getQueryBuilder();
                $qb->select('id')
                        ->from('facility')
			->where($qb->expr()->eq('id', $qb->createNamedParameter($facil_id, IQueryBuilder::PARAM_INT)));
                return $this->findEntity($qb);
        }



	/**
	 * @param  none
	 * @return Entity|Facil
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 * @throws DoesNotExistException
	 */
	public function findAll(){
		/* @var $qb IQueryBuilder */
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
		     ->from('facility');
		return $this->findEntities($qb);
	}



	public function deplicateFacil($facil_name){
                /* @var $qb IQueryBuilder */

		$qb = $this->db->getQueryBuilder(); 
		$qb->selectAlias($qb->createFunction('COUNT(*)'), 'count')
		        ->from('facility')
			->where($qb->expr()->eq('facil_name', $qb->createNamedParameter($facil_name, IQueryBuilder::PARAM_STR)));

                return $this->findEntity($qb);
	}

	public function deplicateFacilUpdate($facil_id, $facil_name){
                /* @var $qb IQueryBuilder */
                $qb = $this->db->getQueryBuilder();
                $qb->selectAlias($qb->createFunction('COUNT(*)'), 'count')
                        ->from('facility')
			->where($qb->expr()->eq('facil_name', $qb->createNamedParameter($facil_name, IQueryBuilder::PARAM_STR)))
			->andWhere('id != '.$facil_id);

                return $this->findEntity($qb);
        }
}
