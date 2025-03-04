<?php

/**
 * Nextcloud - Welcome
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Julien Veyssier
 * @copyright Julien Veyssier 2022
 */

namespace OCA\Welcome\Service;

use Exception;
use OC\User\NoUserException;
use OCA\Welcome\AppInfo\Application;
use OCP\App\IAppManager;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\InvalidPathException;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\IConfig;
use OCP\IURLGenerator;
use OCP\IUserManager;
use Psr\Log\LoggerInterface;
use Throwable;

class FileService {

	public function __construct(private IRootFolder $root,
		private IConfig $config,
		private IURLGenerator $urlGenerator,
		private LoggerInterface $logger,
		private IUserManager $userManager,
		private IAppManager $appManager,
	) {
	}

	/**
	 * @return File|null
	 * @throws NoUserException
	 * @throws NotFoundException
	 * @throws NotPermittedException
	 */
	private function getWidgetFile(): ?File {
		$filePath = $this->config->getAppValue(Application::APP_ID, 'filePath');
		$userName = $this->config->getAppValue(Application::APP_ID, 'userName');
		$userId = $this->config->getAppValue(Application::APP_ID, 'userId');

		if ($filePath && $userName && $userId && $this->userManager->userExists($userId)) {
			$userFolder = $this->root->getUserFolder($userId);
			if ($userFolder->nodeExists($filePath)) {
				$file = $userFolder->get($filePath);
				if ($file instanceof File) {
					return $file;
				}
			}
		}
		$this->logger->debug('Failed to get file "' . $filePath . '" of user "' . $userId . '"', ['app' => Application::APP_ID]);
		return null;
	}

	/**
	 * @return array|null
	 * @throws NotFoundException
	 * @throws NotPermittedException
	 * @throws NoUserException
	 */
	public function getWidgetContent(): ?array {
		$userName = $this->config->getAppValue(Application::APP_ID, 'userName');
		$userId = $this->config->getAppValue(Application::APP_ID, 'userId');
		if ($this->appManager->isEnabledForUser('spreed')) {
			$supportUserName = $this->config->getAppValue(Application::APP_ID, 'supportUserName');
			$supportUserId = $this->config->getAppValue(Application::APP_ID, 'supportUserId');
			$supportText = $this->config->getAppValue(Application::APP_ID, 'supportText');
		} else {
			$supportUserName = null;
			$supportUserId = null;
			$supportText = null;
		}
		

		$file = $this->getWidgetFile();
		if ($file !== null) {
			$content = $file->getContent();
			$content = $this->replaceImagePaths($content, $file->getParent());
			// prepend a new line to avoid having the first line interpreted as code...
			return [
				'content' => "\n" . trim($content),
				'userId' => $userId,
				'userName' => $userName,
				'supportUserId' => $supportUserId,
				'supportUserName' => $supportUserName,
				'supportText' => $supportText,
			];
		}
		return null;
	}

	/**
	 * @param int $fileId
	 * @return File|null
	 * @throws InvalidPathException
	 * @throws NoUserException
	 * @throws NotFoundException
	 * @throws NotPermittedException
	 */
	public function getImage(int $fileId): ?File {
		$widgetFile = $this->getWidgetFile();
		$parent = $widgetFile->getParent();
		$attachmentFolderName = '.attachments.' . $widgetFile->getId();
		if ($parent->nodeExists($attachmentFolderName)) {
			$attachmentFolder = $parent->get($attachmentFolderName);
			if ($attachmentFolder instanceof Folder) {
				$attachment = $attachmentFolder->getById($fileId);
				if (is_array($attachment) && !empty($attachment)) {
					$candidate = $attachment[0];
					if ($candidate instanceof File) {
						return $candidate;
					}
				}
			}
		}
		return null;
	}

	/**
	 * @param string $content
	 * @param Folder $folder
	 * @return string
	 * @throws NotFoundException
	 * @throws InvalidPathException
	 */
	private function replaceImagePaths(string $content, Folder $folder): string {
		preg_match_all(
			'/\!\[(?>[^\[\]]+|\[(?>[^\[\]]+|\[(?>[^\[\]]+|\[(?>[^\[\]]+|\[(?>[^\[\]]+|\[(?>[^\[\]]+|\[\])*\])*\])*\])*\])*\])*\]\(([^)&]+)\)/',
			$content,
			$matches,
			PREG_SET_ORDER,
		);

		foreach ($matches as $match) {
			$path = $match[1];
			$decodedPath = urldecode($path);
			if (!str_starts_with($path, 'http://') && !str_starts_with($path, 'https://') && $folder->nodeExists($decodedPath)) {
				$file = $folder->get($decodedPath);
				if ($file instanceof File) {
					$fullMatch = $match[0];
					$welcomeImageUrl = $this->urlGenerator->linkToRoute(Application::APP_ID . '.config.getWidgetImage', ['fileId' => $file->getId()]);
					$newLink = str_replace($path, $welcomeImageUrl, $fullMatch);
					$content = str_replace($fullMatch, $newLink, $content);
				}
			}
		}
		return $content;
	}

	public function getWidgetHttpImageUrls(): ?array {
		try {
			$file = $this->getWidgetFile();
			if ($file !== null) {
				$content = $file->getContent();

				$matchCount = preg_match_all(
					'/\!\[(?>[^\[\]]+|\[(?>[^\[\]]+|\[(?>[^\[\]]+|\[(?>[^\[\]]+|\[(?>[^\[\]]+|\[(?>[^\[\]]+|\[\])*\])*\])*\])*\])*\])*\]\((https?:\/\/[^)&]+)\)/',
					$content,
					$matches,
					PREG_SET_ORDER,
				);

				if ($matchCount === 0 || $matchCount === false) {
					return null;
				}

				return array_map(static function (array $match) {
					return urldecode($match[1]);
				}, $matches);
			}
		} catch (Exception | Throwable $e) {
			$this->logger->warning('Failed to get widget http image URLs', ['app' => Application::APP_ID, 'exception' => $e]);
		}
		return null;
	}
}
