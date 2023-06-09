<?php

namespace OCA\ReserveRoom\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCA\ReserveRoom\Db\Facil;
use OCA\ReserveRoom\Db\FacilMapper;
use OCP\AppFramework\Http\DataResponse;
class FacilService {
	
	
	/** @var ReserveMapper */
	private $mapper;

	use CheckService;

	public function __construct(FacilMapper $mapper) {
		$this->mapper = $mapper;
	}

	public function findAll(){
		return $this->mapper->findAll();
	}

	private function handleException(Exception $e): void {
		if ($e instanceof DoesNotExistException ||
			$e instanceof MultipleObjectsReturnedException) {
			throw new FacilNotFound($e->getMessage());
		} else {
			throw $e;
		}
	}

	
	public function create($facil_name){

		#表示順IDの初期化
		$sort_num = "9999";

		//入力された値の検証	
		$ret1 = $this->mapper->deplicateFacil($facil_name);
		
		$count = $ret1->{'count'};

		if ($count !== 0) {
                        $message=['message' => 'Duplicate facility name.'];
      			return $message;
                }
		
		$ret2 = $this->validFacil($sort_num, $facil_name, $message);
		if ($ret2 === false) {

			return $message;
		}

		
		$facil = new Facil();
		$facil->setSortNum($sort_num);
		$facil->setFacilName($facil_name);
		
		return $this->mapper->insert($facil);
		
	}

	public function update($facil_id, $sort_num, $facil_name) {
		try {
			$facil = $this->mapper->find($facil_id);
			$ret1 = $this->mapper->deplicateFacilUpdate($facil_id, $facil_name);
			$count = (int)$ret1->{'count'};
			if ($count !== 0) {
				$message=['message' => 'Duplicate facility name.'];
				return $message;
			}

			$ret2 = $this->validFacil($sort_num, $facil_name, $message);
			if ($ret2 === false) {

                        	return $message;
			}


			
			$facil->setId($facil_id);
			$facil->setSortNum($sort_num);
                	$facil->setFacilName($facil_name);
			return $this->mapper->update($facil);
				
			
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	public function delete($facil_id) {
		try {
			$facil = $this->mapper->find($facil_id);
			$this->mapper->delete($facil);
			return $facil;
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
}
