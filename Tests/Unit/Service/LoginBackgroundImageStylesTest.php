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

namespace MaikSchneider\LoginColorScheme\Tests\Unit\Service;

use MaikSchneider\LoginColorScheme\Service\LoginBackgroundImageStyles;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LoginBackgroundImageStylesTest extends UnitTestCase
{
    #[Test]
    public function buildReturnsEmptyStringIfNoDarkImageIsConfigured(): void
    {
        self::assertSame('', $this->subject([])->build($this->request()));
    }

    #[Test]
    public function buildReturnsEmptyStringIfExtensionIsNotConfigured(): void
    {
        $extensionConfiguration = self::createStub(ExtensionConfiguration::class);
        $extensionConfiguration->method('get')->willThrowException(
            new ExtensionConfigurationExtensionNotConfiguredException('not configured', 1_754_500_000)
        );

        $subject = new LoginBackgroundImageStyles($extensionConfiguration, new NullLogger());

        self::assertSame('', $subject->build($this->request()));
    }

    #[Test]
    #[DataProvider('blankConfigurationValuesDataProvider')]
    public function buildIgnoresBlankConfigurationValues(mixed $value): void
    {
        self::assertSame('', $this->subject(['backgroundImageDark' => $value])->build($this->request()));
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function blankConfigurationValuesDataProvider(): array
    {
        return [
            'empty string' => [''],
            'whitespace only' => ['   '],
            'null' => [null],
        ];
    }

    #[Test]
    public function buildKeepsAbsoluteUrlsUntouched(): void
    {
        $styles = $this->subject(['backgroundImageDark' => 'https://example.org/login-dark.jpg'])->build($this->request());

        self::assertStringContainsString('url("https://example.org/login-dark.jpg")', $styles);
    }

    #[Test]
    public function buildCoversBothTheMediaQueryAndTheExplicitDarkColorScheme(): void
    {
        $styles = $this->subject(['backgroundImageDark' => '//example.org/login-dark.jpg'])->build($this->request());

        self::assertStringContainsString('@media (prefers-color-scheme: dark)', $styles);
        self::assertStringContainsString(':root:not([data-color-scheme="light"]) .typo3-login', $styles);
        self::assertStringContainsString(':root[data-color-scheme="dark"] .typo3-login', $styles);
    }

    #[Test]
    public function buildReturnsEmptyStringForAnUnresolvableFile(): void
    {
        $styles = $this->subject(['backgroundImageDark' => 'fileadmin/does-not-exist.jpg'])->build($this->request());

        self::assertSame('', $styles);
    }

    #[Test]
    public function buildEscapesValuesBreakingOutOfTheCssDeclaration(): void
    {
        $styles = $this->subject(['backgroundImageDark' => 'https://example.org/a.jpg"); } body { display: none; }'])->build($this->request());

        self::assertStringNotContainsString('body { display: none; }', $styles);
    }

    /**
     * @param array<string, mixed> $configuration
     */
    private function subject(array $configuration): LoginBackgroundImageStyles
    {
        $extensionConfiguration = self::createStub(ExtensionConfiguration::class);
        $extensionConfiguration->method('get')->willReturn($configuration);

        return new LoginBackgroundImageStyles($extensionConfiguration, new NullLogger());
    }

    private function request(): ServerRequestInterface
    {
        return self::createStub(ServerRequestInterface::class);
    }
}
