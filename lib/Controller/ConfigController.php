<?php
/**
 * Nextcloud - welcome
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Julien Veyssier <eneiluj@posteo.net>
 * @copyright Julien Veyssier 2020
 */

namespace OCA\Welcome\Controller;

use OC\Files\Node\File;
use OCP\Files\Folder;
use OCP\Files\IAppData;
use OCP\AppFramework\Http\DataDisplayResponse;

use OCP\IConfig;
use OCP\IServerContainer;
use OCP\IL10N;
use Psr\Log\LoggerInterface;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\RedirectResponse;

use OCP\Files\IRootFolder;
use OCP\IUserManager;
use OCP\Files\FileInfo;

use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;

use OCA\Welcome\AppInfo\Application;

function startsWith(string $haystack, string $needle): bool {
	$length = strlen($needle);
	return (substr($haystack, 0, $length) === $needle);
}

class ConfigController extends Controller {

	private $userId;
	private $config;

	public function __construct($AppName,
								IRequest $request,
								IServerContainer $serverContainer,
								IConfig $config,
								IAppData $appData,
								IL10N $l,
								IRootFolder $root,
								IUserManager $userManager,
								LoggerInterface $logger,
								?string $userId) {
		parent::__construct($AppName, $request);
		$this->l = $l;
		$this->userId = $userId;
		$this->appData = $appData;
		$this->serverContainer = $serverContainer;
		$this->config = $config;
		$this->logger = $logger;
		$this->root = $root;
		$this->userManager = $userManager;
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
		$filePath = $this->config->getAppValue(Application::APP_ID, 'filePath', '');
		$userName = $this->config->getAppValue(Application::APP_ID, 'userName', '');
		$userId = $this->config->getAppValue(Application::APP_ID, 'userId', '');
		$supportUserName = $this->config->getAppValue(Application::APP_ID, 'supportUserName', '');
		$supportUserId = $this->config->getAppValue(Application::APP_ID, 'supportUserId', '');
		$supportText = $this->config->getAppValue(Application::APP_ID, 'supportText', '');

		if ($filePath && $userName && $userId && $this->userManager->userExists($userId)) {
			$userFolder = $this->root->getUserFolder($userId);
			if ($userFolder->nodeExists($filePath)) {
				$file = $userFolder->get($filePath);
				if ($file->getType() === FileInfo::TYPE_FILE) {
					$content = $file->getContent();
					$content = $this->replaceImagePaths($content, $file->getParent());
					// prepend a new line to avoid having the first line interpreted as code...
					return new DataResponse([
						'content' => "\n" . trim($content),
						'userId' => $userId,
						'userName' => $userName,
						'supportUserId' => $supportUserId,
						'supportUserName' => $supportUserName,
						'supportText' => $supportText,
					]);
				}
			}
		}
		return new DataResponse('not found', 400);
	}

	private function replaceImagePaths(string $content, Folder $folder): string {
		preg_match_all(
			'/\!\[(?>[^\[\]]+|\[(?>[^\[\]]+|\[(?>[^\[\]]+|\[(?>[^\[\]]+|\[(?>[^\[\]]+|\[(?>[^\[\]]+|\[\])*\])*\])*\])*\])*\])*\]\(([^)&]+)\)/',
			$content,
			$matches,
			PREG_SET_ORDER
		);
		$paths = array_map(static function (array $match) {
			return urldecode($match[1]);
		}, $matches);

		foreach ($matches as $match) {
			$path = $match[1];
			if (!startsWith($path, 'http://') && !startsWith($path, 'https://') && $folder->nodeExists($path)) {
				$file = $folder->get($path);
				if ($file instanceof File) {
					$fullMatch = $match[0];
					$newLink = str_replace($path, 'a?fileId=' . $file->getId(), $fullMatch);
					$content = str_replace($fullMatch, $newLink, $content);
				}
			}
		}
		return $content;
	}
}
