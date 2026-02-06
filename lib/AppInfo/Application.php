<?php

/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Welcome\AppInfo;

use OCA\Welcome\Dashboard\WelcomeWidget;
use OCA\Welcome\Listener\CSPListener;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Services\IAppConfig;
use OCP\IConfig;
use OCP\Security\CSP\AddContentSecurityPolicyEvent;

class Application extends App implements IBootstrap {
	public const APP_ID = 'welcome';
	private IConfig $config;
	private IAppConfig $appConfig;

	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);

		$container = $this->getContainer();
		$this->config = $container->get(IConfig::class);
		$this->appConfig = $container->get(IAppConfig::class);
	}

	public function register(IRegistrationContext $context): void {
		$filePath = $this->appConfig->getAppValueString('filePath', '');
		if ($filePath) {
			$context->registerDashboardWidget(WelcomeWidget::class);
			$context->registerEventListener(AddContentSecurityPolicyEvent::class, CSPListener::class);
		}
	}

	public function boot(IBootContext $context): void {
	}
}
