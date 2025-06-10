<?php

/**
 * SPDX-FileCopyrightText: 2020 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Welcome\Controller;

use OC\User\NoUserException;
use OCA\Welcome\AppInfo\Application;
use OCA\Welcome\Service\FileService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\Files\InvalidPathException;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\IConfig;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserManager;
use OCP\Lock\LockedException;

class ConfigController extends Controller {

	public function __construct(
		string $appName,
		IRequest $request,
		private IConfig $config,
		private FileService $fileService,
		private IUserManager $userManager,
	) {
		parent::__construct($appName, $request);
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

	public function enableWidgetForAllUsers(): DataResponse {
		try {
			$this->userManager->callForAllUsers(function (IUser $user) {
				$this->enableUserWidget($user->getUID());
			});
			return new DataResponse([]);
		} catch (\Exception|\Throwable $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
		}
	}

	private function enableUserWidget(string $userId): void {
		$rawValue = $this->config->getUserValue($userId, 'dashboard', 'layout', 'recommendations,spreed,mail,calendar');
		$layout = explode(',', $rawValue);
		if (!in_array('welcome', $layout, true)) {
			$layout[] = 'welcome';
			$this->config->setUserValue($userId, 'dashboard', 'layout', implode(',', $layout));
		}
	}
}
