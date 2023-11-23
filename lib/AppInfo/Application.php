<?php
/**
 * Nextcloud - Welcome
 *
 *
 * @author Julien Veyssier <julien-nc@posteo.net>
 * @copyright Julien Veyssier 2021
 */

namespace OCA\Welcome\AppInfo;

use OCA\Welcome\Listener\CSPListener;
use OCP\IConfig;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;

use OCA\Welcome\Dashboard\WelcomeWidget;
use OCP\Security\CSP\AddContentSecurityPolicyEvent;

class Application extends App implements IBootstrap {
	public const APP_ID = 'welcome';
	private IConfig $config;

	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);

		$container = $this->getContainer();
		$this->config = $container->get(IConfig::class);
	}

	public function register(IRegistrationContext $context): void {
		$filePath = $this->config->getAppValue(self::APP_ID, 'filePath', '');
		if ($filePath) {
			$context->registerDashboardWidget(WelcomeWidget::class);
			$context->registerEventListener(AddContentSecurityPolicyEvent::class, CSPListener::class);
		}
	}

	public function boot(IBootContext $context): void {
	}
}

