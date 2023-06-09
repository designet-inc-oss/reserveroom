<?php

namespace OCA\ReserveRoom\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCA\ReserveRoom\Db\Reserve;
use OCA\ReserveRoom\Db\ReserveMapper;
use OCA\ReserveRoom\Service\CheckService;
use OCP\AppFramework\Http\DataResponse;

class ReserveService{
	
	use CheckService;

	/** @var ReserveMapper */
	private $mapper;

	public function __construct(ReserveMapper $mapper) {
		$this->mapper = $mapper;

	}

	/* 引数として入っている日付の予約の抽出*/
	public function findAll($date){
		$date = date('Y-m-d H:i:s',strtotime($date));
		$newDate = date('Y-m-d H:i:s', strtotime($date."+ 1 day"));
		return $this->mapper->findAll($date, $newDate);
	}

	private function handleException(Exception $e): void {
		if ($e instanceof DoesNotExistException ||
			$e instanceof MultipleObjectsReturnedException) {
			throw new ReserveNotFound($e->getMessage());
		} else {
			throw $e;
		}
	}


	//予約の追加	
	public function create($u_id, $facil_id, $start_date_time, $end_date_time, $memo){
		//日付の検証
		$ret = $this->validDate($start_date_time, $end_date_time, $message);
		if ($ret === false) {
			return $message;
		}

		$ret = $this->validText($memo, $message);
		if ($ret === false) {
			return $message;
		}
			
		$ret = $this->mapper->deplicateReserve($start_date_time, $end_date_time, $facil_id);
		$count = $ret->{'count'};
		if ($count !== 0) {
			$message = ['message' => 'Duplicate reserve.'];
			return $message;
		}

		$reserve = new Reserve();
		$reserve->setUid($u_id);
		$reserve->setFacilId($facil_id);
		$reserve->setStartDateTime($start_date_time);
		$reserve->setEndDateTime($end_date_time);
		$reserve->setMemo($memo);
		
		return $this->mapper->insert($reserve);
		
	}

	//予約の更新
	public function update($id, $u_id, $is_admin, $facil_id, $start_date_time, $end_date_time, $memo) {
		try {
			$reserve = $this->mapper->find($id);
			//入力値の検証
			$ret = $this->mapper->validUser($id, $u_id);
			$count = $ret->{'count'};

			if ($count === 0 && $is_admin === false) {
				$message=["message" => "Cannot modify other's reserve."];
				return $message;	
			}

			$ret2 =  $this->validDate($start_date_time, $end_date_time, $message);

			if ($ret2 === false) {
			
				return $message;
			
			}

			$ret3 = $this->mapper->deplicateReserveUpdate($id, $start_date_time, $end_date_time, $facil_id);
			$count = $ret3->{'count'};


                	if ($count !== 0) {
                                $message=['message' => 'Duplicate reserve.'];
                                return $message;
			}


			$ret4 = $this->validText($memo, $message);

			if ($ret4 === false) {
			
				return $message;
			
			}


                	$reserve->setFacilId($facil_id);
                	$reserve->setStartDateTime($start_date_time);
                	$reserve->setEndDateTime($end_date_time);
                	$reserve->setMemo($memo);
			return $this->mapper->update($reserve);

			
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}

	//予約の削除
	public function delete($id, $u_id, $is_admin) {
		try {
			$reserve = $this->mapper->find($id);
			$ret = $this->mapper->validUser($id, $u_id);
			$count = $ret->{'count'};

                        if ($count === 0 && $is_admin === false) {
                                $message=["message" => "Cannot delete other's reserve."];
                                return $message;
			}

			$this->mapper->delete($reserve);
			return $reserve;
			
		} catch (Exception $e) {
			$this->handleException($e);
		}
	}
}
