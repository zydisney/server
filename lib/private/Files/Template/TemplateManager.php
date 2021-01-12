<?php
/*
 * @copyright Copyright (c) 2021 Julius Härtl <jus@bitgrid.net>
 *
 * @author Julius Härtl <jus@bitgrid.net>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

declare(strict_types=1);


namespace OC\Files\Template;

use OCP\Files\Folder;
use OCP\Files\File;
use OCP\Files\GenericFileException;
use OCP\Files\IRootFolder;
use OCP\Files\Node;
use OCP\Files\NotFoundException;
use OCP\Files\Template\ITemplateManager;
use OCP\IPreview;
use OCP\IUserSession;
use Psr\Log\LoggerInterface;

class TemplateManager implements ITemplateManager {

	private $mimetypes = [];

	private $rootFolder;
	private $previewManager;
	private $logger;
	private $userId;

	public function __construct(
		IRootFolder $rootFolder,
		IUserSession $userSession,
		IPreview $previewManager,
		LoggerInterface $logger
	) {
		$this->rootFolder = $rootFolder;
		$this->previewManager = $previewManager;
		$this->logger = $logger;
		$user = $userSession->getUser();
		$this->userId = $user ? $user->getUID() : null;
	}
	/**
	 * @inheritDoc
	 */
	public function registerTemplateSupport(string $appId, array $mimetypes, string $actionName, string $fileExtension): void {
		$this->mimetypes[] = [
			'app' => $appId,
			'label' => $actionName,
			'extension' => $fileExtension,
			'mimetypes' => $mimetypes
		];
	}

	public function listMimetypes(): array {
		return array_map(function (array $entry) {
			return array_merge($entry, [
				'templates' => array_map(function (File $file) {
					return $this->formatFile($file);
				}, $this->getTemplateFiles($entry['mimetypes']))
			]);
		}, $this->mimetypes);
	}

	/**
	 * @param string $filePath
	 * @param string $templatePath
	 * @return array
	 * @throws GenericFileException
	 */
	public function createFromTemplate(string $filePath, string $templatePath = ''): array {
		$userFolder = $this->rootFolder->getUserFolder($this->userId);
		try {
			$userFolder->get($filePath);
			throw new GenericFileException('File already exists');
		} catch (NotFoundException $e) {}
		try {
			$targetFile = $userFolder->newFile($filePath);
			if ($templatePath !== '') {
				$template = $userFolder->get($templatePath);
				$template->copy($targetFile->getPath());
			}
			return $this->formatFile($userFolder->get($filePath));
		} catch (\Exception $e) {
			$this->logger->error($e->getMessage(), ['exception' => $e]);
			throw new GenericFileException('Failed to create file from template');
		}

	}

	/**
	 * @return Folder
	 * @throws \OCP\Files\NotFoundException
	 * @throws \OCP\Files\NotPermittedException
	 * @throws \OC\User\NoUserException
	 */
	private function getTemplateFolder(): Node {
		return $this->rootFolder->getUserFolder($this->userId)->get('Templates/');
	}

	private function getTemplateFiles(array $mimetypes): array {
		try {
			$userTemplateFolder = $this->getTemplateFolder();
		} catch (\Exception $e) {
			return [];
		}
		return array_filter($userTemplateFolder->getDirectoryListing(), function (File $file) use ($mimetypes) {
			return in_array($file->getMimeType(), $mimetypes, true);
		});
	}

	/**
	 * @param Node|File $file
	 * @return array
	 * @throws NotFoundException
	 * @throws \OCP\Files\InvalidPathException
	 */
	private function formatFile(Node $file): array {
		return [
			'basename' => $file->getName(),
			'etag' => $file->getEtag(),
			'fileid' => $file->getId(),
			'filename' => $this->rootFolder->getUserFolder($this->userId)->getRelativePath($file->getPath()),
			'lastmod' => $file->getMTime(),
			'mime' => $file->getMimetype(),
			'size' => $file->getSize(),
			'type' => $file->getType(),
			'hasPreview' => $this->previewManager->isAvailable($file)
		];
	}
}
