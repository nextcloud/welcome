<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Welcome\Migration;

use Closure;
use OCP\AppFramework\Services\IAppConfig;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;
use Override;

class Version020000Date20260206141127 extends SimpleMigrationStep {
	private static array $configKeys = [
		'widgetTitle',
		'userName',
		'userId',
		'supportUserName',
		'supportUserId',
		'supportText',
	]; // do not lazy store filePath as it is needed for dashboard registration
	public function __construct(
		private IAppConfig $appConfig,
	) {
	}

	/**
	 * @param IOutput $output
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 * @param array $options
	 */
	#[Override]
	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
		$allSetKeys = $this->appConfig->getAppKeys();

		foreach (self::$configKeys as $key) {
			// skip if not already set
			if (!in_array($key, $allSetKeys)) {
				continue;
			}
			$value = $this->appConfig->getAppValueString($key);
			$this->appConfig->setAppValueString($key, $value, lazy: true);
		}
	}
}