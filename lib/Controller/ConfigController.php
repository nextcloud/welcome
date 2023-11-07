<?php
/**
 * Nextcloud - welcome2
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Julien Veyssier <eneiluj@posteo.net>
 * @copyright Julien Veyssier 2020
 */

namespace OCA\Welcome2\Controller;

use OC\User\NoUserException;
use OCA\Welcome2\Service\FileService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\Files\InvalidPathException;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\IConfig;
use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;

use OCA\Welcome2\AppInfo\Application;
use OCP\Lock\LockedException;

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

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param int $fileId
	 * @return DataDisplayResponse The image content
	 * @throws InvalidPathException
	 * @throws NotFoundException
	 * @throws NotPermittedException
	 * @throws LockedException
	 * @throws NoUserException
	 */
	public function getWidgetImage(int $fileId): DataDisplayResponse {
		$image = $this->fileService->getImage($fileId);
		if ($image !== null) {
			$response = new DataDisplayResponse(
				$image->getContent(),
				Http::STATUS_OK,
				['Content-Type' => $image->getMimeType()]
			);
			$response->cacheFor(60 * 60 * 24, false, true);
			return $response;
		}
		return new DataDisplayResponse('', Http::STATUS_NOT_FOUND);
	}
}
