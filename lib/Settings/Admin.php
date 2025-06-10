<?php

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Welcome\Settings;

use OCA\Welcome\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IConfig;
use OCP\Settings\ISettings;

class Admin implements ISettings {

	public function __construct(
		string $appName,
		private IConfig $config,
		private IInitialState $initialStateService,
	) {
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm(): TemplateResponse {
		$filePath = $this->config->getAppValue(Application::APP_ID, 'filePath', '');
		$userName = $this->config->getAppValue(Application::APP_ID, 'userName', '');
		$userId = $this->config->getAppValue(Application::APP_ID, 'userId', '');
		$supportUserId = $this->config->getAppValue(Application::APP_ID, 'supportUserId', '');
		$supportUserName = $this->config->getAppValue(Application::APP_ID, 'supportUserName', '');
		$supportText = $this->config->getAppValue(Application::APP_ID, 'supportText', '');
		$widgetTitle = $this->config->getAppValue(Application::APP_ID, 'widgetTitle', '');

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
