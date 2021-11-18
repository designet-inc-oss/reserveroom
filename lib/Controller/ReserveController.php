<?php

namespace OCA\ReserveRoom\Controller;

use OCA\ReserveRoom\AppInfo\Application;
use OCA\ReserveRoom\Service\ReserveService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\IUserSession;
class ReserveController extends Controller {
	/** @var ReserveService */
	private $service;

	//ユーザー情報
	private $u_id;
	use Errors;

	public function __construct(IRequest $request, ReserveService $service, IUserSession $userSession){
		parent::__construct(Application::APP_ID, $request);
		$this->service = $service;
		$this->u_id = $userSession->getUser()->getUID();
	}



	/**
	 * @NoAdminRequired
	 */
	public function index($date): DataResponse {
		return $this->handleNotFound(function () use($date){
			return $this->service->findAll($date);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function create($facil_id,$start_date_time,$end_date_time,$memo): DataResponse {

		return new DataResponse($this->service->create($this->u_id, $facil_id, $start_date_time, $end_date_time, $memo));	
	}

	/**
	 * @NoAdminRequired
	 */
	public function update($id, $facil_id, $start_date_time, $end_date_time, $memo): DataResponse {
		return $this->handleNotFound(function () use ($id, $u_id,$facil_id, $start_date_time, $end_date_time, $memo) {
			return $this->service->update($id, $this->u_id, $facil_id, $start_date_time, $end_date_time, $memo);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function destroy($id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			return $this->service->delete($id, $this->u_id);
		});
	}
}
