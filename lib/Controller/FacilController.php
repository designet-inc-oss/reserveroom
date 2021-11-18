<?php

namespace OCA\ReserveRoom\Controller;

use OCA\ReserveRoom\AppInfo\Application;
use OCA\ReserveRoom\Service\FacilService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCA\ReserveRoom\Service\CheckService;
class FacilController extends Controller {
	/** @var ReserveService */
	private $service;

	use Errors;

	public function __construct(IRequest $request,
								FacilService $service) {
		parent::__construct(Application::APP_ID, $request);
		$this->service = $service;
	}

	/**
	 * @NoAdminRequired
	 */		
	public function index(): DataResponse {
		return $this->handleNotFound(function (){
			return $this->service->findAll();
		});
	}

	/**
	 * @AdminRequired
	 */
	public function create($facil_name): DataResponse {
		return new DataResponse($this->service->create($facil_name));
	}

	/**
	 * @AdminRequired
	 */
	public function update($id, $sort_num, $facil_name): DataResponse {
		return $this->handleNotFound(function () use ($id, $sort_num, $facil_name) {
			return $this->service->update($id, $sort_num, $facil_name);
		});
	}

	/**
	 * @AdminRequired
	 */
	public function destroy($id): DataResponse {
		return $this->handleNotFound(function () use ($id) {
			return $this->service->delete($id);
		});
	}
}
