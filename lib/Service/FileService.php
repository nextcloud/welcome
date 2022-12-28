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

use OC\Files\Node\File;
use OC\User\NoUserException;
use OCA\Welcome\AppInfo\Application;
use OCP\Files\Folder;
use OCP\Files\InvalidPathException;
use OCP\Files\IRootFolder;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\IConfig;
use OCP\IURLGenerator;
use OCP\IUserManager;

function startsWith(string $haystack, string $needle): bool {
	$length = strlen($needle);
	return (substr($haystack, 0, $length) === $needle);
}

class FileService {

	/**
	 * @var IRootFolder
	 */
	private $root;
	/**
	 * @var IConfig
	 */
	private $config;
	/**
	 * @var IUserManager
	 */
	private $userManager;
	/**
	 * @var IURLGenerator
	 */
	private $urlGenerator;

	public function __construct (IRootFolder $root,
								IConfig $config,
								IURLGenerator $urlGenerator,
								IUserManager $userManager) {
		$this->root = $root;
		$this->config = $config;
		$this->userManager = $userManager;
		$this->urlGenerator = $urlGenerator;
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
		return null;
	}

	/**
	 * @return array|null
	 * @throws NotFoundException
	 * @throws NotPermittedException
	 * @throws NoUserException
	 */
	public function getWidgetContent(): ?array {
		$this->getWidgetHttpImageUrls();

		$userName = $this->config->getAppValue(Application::APP_ID, 'userName', '');
		$userId = $this->config->getAppValue(Application::APP_ID, 'userId', '');
		$supportUserName = $this->config->getAppValue(Application::APP_ID, 'supportUserName', '');
		$supportUserId = $this->config->getAppValue(Application::APP_ID, 'supportUserId', '');
		$supportText = $this->config->getAppValue(Application::APP_ID, 'supportText', '');

		$file = $this->getWidgetFile();
		$content = $file->getContent();
		if ($content !== null) {
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
			PREG_SET_ORDER
		);

		foreach ($matches as $match) {
			$path = $match[1];
			$decodedPath = urldecode($path);
			if (!startsWith($path, 'http://') && !startsWith($path, 'https://') && $folder->nodeExists($decodedPath)) {
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
		$file = $this->getWidgetFile();
		if ($file !== null) {
			$content = $file->getContent();

			preg_match_all(
				'/\!\[(?>[^\[\]]+|\[(?>[^\[\]]+|\[(?>[^\[\]]+|\[(?>[^\[\]]+|\[(?>[^\[\]]+|\[(?>[^\[\]]+|\[\])*\])*\])*\])*\])*\])*\]\((https?:\/\/[^)&]+)\)/',
				$content,
				$matches,
				PREG_SET_ORDER
			);

			if ($matches === null) {
				return null;
			}

			return array_map(static function (array $match) {
				return urldecode($match[1]);
			}, $matches);
		}
		return null;
	}
}
