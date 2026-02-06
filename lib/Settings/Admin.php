<?php

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Welcome\Settings;

use OCA\Welcome\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IAppConfig;
use OCP\AppFramework\Services\IInitialState;
use OCP\Settings\ISettings;

class Admin implements ISettings {

	public function __construct(
		string $appName,
		private IAppConfig $appConfig,
		private IInitialState $initialStateService,
	) {
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm(): TemplateResponse {
		$filePath = $this->appConfig->getAppValueString('filePath', '', lazy: true);
		$userName = $this->appConfig->getAppValueString('userName', '', lazy: true);
		$userId = $this->appConfig->getAppValueString('userId', '', lazy: true);
		$supportUserId = $this->appConfig->getAppValueString('supportUserId', '', lazy: true);
		$supportUserName = $this->appConfig->getAppValueString('supportUserName', '', lazy: true);
		$supportText = $this->appConfig->getAppValueString('supportText', '', lazy: true);
		$widgetTitle = $this->appConfig->getAppValueString('widgetTitle', '', lazy: true);

		$adminConfig = [
			'filePath' => $filePath,
			'userName' => $userName,
			'userId' => $userId,
			'supportUserId' => $supportUserId,
			'supportUserName' => $supportUserName,
			'supportText' => $supportText,
			'widgetTitle' => $widgetTitle,
		];
		$this->initialStateService->provideInitialState('admin-config', $adminConfig);
		return new TemplateResponse(Application::APP_ID, 'adminSettings');
	}

	public function getSection(): string {
		return 'theming';
	}

	public function getPriority(): int {
		return 10;
	}
}
