<?php

namespace OCA\ReserveRoom\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCA\ReserveRoom\Db\Reserve;
use OCA\ReserveRoom\Db\Facil;
use OCA\AppFramework\Http\DataResponse;
use OCA\IUserSession;

trait CheckService{

	/** user information **/
	private $u_id;


	function checktime($hour, $min, $sec) {
     	if ($hour < 0 || $hour > 23 || !is_numeric($hour)) {
        	 return false;
     	}
     	if ($min < 0 || $min > 59 || !is_numeric($min)) {
         	return false;
     	}
     	if ($sec < 0 || $sec > 59 || !is_numeric($sec)) {
         	return false;
     	}
     	 	return true;
	}


	/**
         * 入力検証用関数
         * @日付入力用の検証を行う
         * @正規表現関数を使う
         *
         */
	public function validDate(&$start_date_time, &$end_date_time, &$message){

		//開始日付が不正な形式かを検出
		if (!preg_match('/\A[0-9]{4}[0-9]{1,2}[0-9]{1,2}[0-9]{2}[0-9]{2}[0-9]{2}\z/', $start_date_time)) {
			$message = array("message" => "Invalid date. (From)");
			return false;
		}

		//終了日付が不正な形式かを検出
		if (!preg_match('/\A[0-9]{4}[0-9]{1,2}[0-9]{1,2}[0-9]{2}[0-9]{2}[0-9]{2}\z/', $end_date_time)) {
			$message = array("message" => "Invalid date. (To)");
			return false;
		
		}
		
		$start_date_time = date('Y-m-d H:i:s',strtotime($start_date_time));
		$end_date_time = date('Y-m-d H:i:s',strtotime($end_date_time));



		//日付と時刻の妥当性を検証するために、まず日付と時間を分割
		list($startDate, $startTime)=explode(' ', $start_date_time);
		list($endDate,$endTime)=explode(' ', $end_date_time);
				
		//日付を-を区切り文字として各変数に代入	
		list($stYear, $stMonth, $stDay)=explode('-', $startDate);
		list($edYear, $edMonth, $edDay)=explode('-', $endDate);

		//時刻も:を区切り文字として各変数に代入
		list($stHour, $stMin, $stSec)=explode(':', $startTime);
		list($edHour, $edMin, $edSec)=explode(':', $endTime);

		//年月日を整数に変換し、矛盾を検証
		$intsYear = (int)$stYear;
		$intsMonth = (int)$stMonth;
		$intsDay = (int)$stDay;
		

		$inteYear = (int)$edYear;
		$inteMonth = (int)$edMonth;
		$inteDay = (int)$edDay;
		
		//時刻を整数に変換し、矛盾を検証
		$intsHour = (int) $stHour;
		$intsMin = (int) $stMin;
		$intsSec = (int) $stSec;

		$inteHour = (int) $edHour;
		$inteMin = (int) $edMin;
		$inteSec = (int) $edSec;
		if ($start_date_time >= $end_date_time) {
				
			$message = array("message" => "Invalid date. (From)");
			return false;
		}



		//開始年月日の妥当性を検証
		if (checkdate($stMonth, $stDay, $stYear) === false) {
			$message = array("message" => "Invalid date. (From)");
                        return false;
		}
			
		//終了年月日の妥当性を検証
		if (checkdate($edMonth, $edDay, $edYear) === false) {
			$message = array("message" => "Invalid date. (To)");
                        return false;
		}	

		//開始時刻の妥当性を検証
		if (self::checktime($stHour, $stMin, $stSec) === false) {
			$message = array("message" => "Invalid date. (From)");
                        return false;
		}


		if (self::checktime($edHour, $edMin, $edSec) === false) {
			$message = array("message" => "Invalid date. (To)");
                        return false;
		}

		return true;
        }


	/**
	 * 説明文(memo)のテキスト量が0文字以上256字以内になっているかを検証
	 *
	**/
	public function validText($memo, &$message){
	
		if (mb_strlen($memo) > 256) {
		
			$message = array("message" => "Invalid description. (max: 256 characters)");
                        return false;
		
		}

		return true;
	
	}

	
	/**
	 * 社用設備名が1文字以上１２８文字以内であるかを検証
	 *
	 *
	**/

	public function validFacil(&$sort_num, &$facil_name, &$message){
	
		if (mb_strlen($facil_name) < 1 || mb_strlen($facil_name) > 128) {

			$message = array("message" => "Invalid facility name.");
			return false;
			
		}


		if ($sort_num > 9999 || $sort_num < 0 || $sort_num === NULL) {
			$message = array("message" => "Invalid order. (0 - 9999)");
			return false;
		
		}
		
	return true;	

		
	}





}

