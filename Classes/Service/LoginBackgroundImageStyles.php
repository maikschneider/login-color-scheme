<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS extension "login_color_scheme".
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

namespace MaikSchneider\LoginColorScheme\Service;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Builds the CSS that swaps the backend login background image in dark mode.
 *
 * The rules deliberately mirror the three states the backend can be in: the
 * authentication screens carry no "data-color-scheme" attribute and therefore
 * follow the operating system preference, while a logged-in user (login refresh)
 * may have pinned light or dark explicitly.
 */
class LoginBackgroundImageStyles
{
    public function __construct(
        private readonly ExtensionConfiguration $extensionConfiguration,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function build(ServerRequestInterface $request): string
    {
        $backgroundImage = $this->getConfiguredBackgroundImage();
        if ($backgroundImage === '') {
            return '';
        }

        $uri = $this->resolveUri($backgroundImage, $request);
        if ($uri === '') {
            $this->logger->warning('The configured dark mode login background image "{image_url}" can\'t be resolved. Please check if the file exists and the extension is activated.', [
                'image_url' => $backgroundImage,
            ]);
            return '';
        }

        $declaration = '.typo3-login { background-image: url("' . GeneralUtility::sanitizeCssVariableValue($uri) . '"); }';

        return '
            @media (prefers-color-scheme: dark) {
                :root:not([data-color-scheme="light"]) ' . $declaration . '
            }
            :root[data-color-scheme="dark"] ' . $declaration . '
        ';
    }

    private function getConfiguredBackgroundImage(): string
    {
        try {
            $configuration = (array)$this->extensionConfiguration->get('login_color_scheme');
        } catch (\Throwable) {
            return '';
        }

        return trim((string)($configuration['backgroundImageDark'] ?? ''));
    }

    /**
     * Resolves an "EXT:" path, a project relative path or an absolute URL to a
     * web path, using whichever core API the current TYPO3 version provides.
     */
    private function resolveUri(string $filename, ServerRequestInterface $request): string
    {
        if (preg_match('/^(https?:)?\/\//', $filename)) {
            return $filename;
        }

        // TYPO3 v14 publishes extension resources through the system resource API.
        if (method_exists(PathUtility::class, 'getSystemResourceUri')) {
            try {
                return (string)PathUtility::getSystemResourceUri($filename, $request);
            } catch (\Throwable) {
                return '';
            }
        }

        $absoluteFilename = GeneralUtility::getFileAbsFileName(ltrim($filename, '/'));
        if ($absoluteFilename === '' || !@is_file($absoluteFilename)) {
            return '';
        }

        return PathUtility::getAbsoluteWebPath($absoluteFilename);
    }
}
