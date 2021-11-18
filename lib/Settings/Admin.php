<?php
namespace OCA\Welcome\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\IL10N;
use OCP\IConfig;
use OCP\Settings\ISettings;
use OCP\IURLGenerator;
use OCP\AppFramework\Services\IInitialState;

use OCA\Welcome\AppInfo\Application;

class Admin implements ISettings {


	public function __construct(string $appName,
								IL10N $l10n,
								IRequest $request,
								IConfig $config,
								IURLGenerator $urlGenerator,
								IInitialState $initialStateService,
								$userId) {
		$this->appName = $appName;
		$this->urlGenerator = $urlGenerator;
		$this->request = $request;
		$this->l10n = $l10n;
		$this->config = $config;
		$this->initialStateService = $initialStateService;
		$this->userId = $userId;
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
