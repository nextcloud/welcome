<?php
/**
 * Nextcloud - Welcome
 *
 *
 * @author Julien Veyssier <eneiluj@posteo.net>
 * @copyright Julien Veyssier 2021
 */

namespace OCA\Welcome\AppInfo;

use OCP\IContainer;
use OCP\IUserSession;

use OCP\AppFramework\App;
use OCP\AppFramework\IAppContainer;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;

use OCA\Welcome\Dashboard\WelcomeWidget;

/**
 * Class Application
 *
 * @package OCA\Welcome\AppInfo
 */
class Application extends App implements IBootstrap {
	public const APP_ID = 'welcome';

	/**
	 * Constructor
	 *
	 * @param array $urlParams
	 */
	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);

		$container = $this->getContainer();
		$this->container = $container;
		$this->config = $container->query(\OCP\IConfig::class);
	}

	public function register(IRegistrationContext $context): void {
		$filePath = $this->config->getAppValue(self::APP_ID, 'filePath', '');
		if ($filePath) {
			$context->registerDashboardWidget(WelcomeWidget::class);
		}
	}

	public function boot(IBootContext $context): void {
	}
}

