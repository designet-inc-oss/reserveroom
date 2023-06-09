<?php

namespace OCA\ReserveRoom\Controller;

use OCA\ReserveRoom\AppInfo\Application;
use OCA\ReserveRoom\Service\ReserveService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\IUserSession;
use OCP\IGroupManager;

class ReserveController extends Controller {
	/** @var ReserveService */
	private $service;

        /** @var IUserSession */
	private $userSession;

        /** @var IGroupManager */
	private $groupManager;

	//ユーザー情報
	private $u_id;

	private $is_admin;
	use Errors;

	public function __construct(IRequest $request, ReserveService $service, IUserSession $userSession, IGroupManager $groupManager){
		parent::__construct(Application::APP_ID, $request);
		$this->service = $service;
		$this->userSession = $userSession;
		$this->u_id = $userSession->getUser()->getUID();
		$this->dispname = $userSession->getUser()->getDisplayName();
		$this->groupManager = $groupManager;
		$this->is_admin = $groupManager->isAdmin($this->u_id);
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
		return $this->handleNotFound(function () use ($id, $u_id, $facil_id, $start_date_time, $end_date_time, $memo) {
			return $this->service->update($id, $this->u_id, $this->is_admin, $facil_id, $start_date_time, $end_date_time, $memo);
		});
	}

	/**
	 * @NoAdminRequired
	 */
	public function destroy($id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			return $this->service->delete($id, $this->u_id, $this->is_admin);
		});
	}
}
