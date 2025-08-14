<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Welcome\Listener;

use OCA\Welcome\Service\FileService;
use OCP\AppFramework\Http\EmptyContentSecurityPolicy;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IRequest;
use OCP\Security\CSP\AddContentSecurityPolicyEvent;

/**
 * @template-implements IEventListener<Event>
 */
class CSPListener implements IEventListener {

	public function __construct(
		private IRequest $request,
		private FileService $fileService,
	) {
	}

	public function handle(Event $event): void {
		if (!$event instanceof AddContentSecurityPolicyEvent) {
			return;
		}

		if (!$this->isDashboardPageLoad()) {
			return;
		}

		$urls = $this->fileService->getWidgetHttpImageUrls();

		if ($urls !== null) {
			$policy = new EmptyContentSecurityPolicy();
			foreach ($urls as $url) {
				$policy->addAllowedImageDomain($url);
			}
			$event->addPolicy($policy);
		}
	}

	private function isDashboardPageLoad(): bool {
		$scriptNameParts = explode('/', $this->request->getScriptName());
		return end($scriptNameParts) === 'index.php'
			&& $this->request->getPathInfo() === '/apps/dashboard/';
	}
}
