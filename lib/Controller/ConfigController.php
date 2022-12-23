<?php
/**
 * Nextcloud - welcome
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Julien Veyssier <eneiluj@posteo.net>
 * @copyright Julien Veyssier 2020
 */

namespace OCA\Welcome\Controller;

use OCA\Welcome\Service\FileService;
use OCP\IConfig;
use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;

use OCA\Welcome\AppInfo\Application;

class ConfigController extends Controller {

	/**
	 * @var IConfig
	 */
	private $config;
	/**
	 * @var FileService
	 */
	private $fileService;

	public function __construct($appName,
								IRequest $request,
								IConfig $config,
								FileService $fileService,
								?string $userId) {
		parent::__construct($appName, $request);
		$this->config = $config;
		$this->fileService = $fileService;
	}

	/**
	 * set admin config values
	 *
	 * @param array $values
	 * @return DataResponse
	 */
	public function setAdminConfig(array $values): DataResponse {
		foreach ($values as $key => $value) {
			$this->config->setAppValue(Application::APP_ID, $key, $value);
		}
		return new DataResponse(1);
	}

	/**
	 * @NoAdminRequired
	 */
	public function getWidgetContent(): DataResponse {
		$content = $this->fileService->getWidgetContent();
		if ($content !== null) {
			return new DataResponse($content);
		}
		return new DataResponse('not found', 404);
	}
}
