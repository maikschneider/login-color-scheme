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

namespace MaikSchneider\LoginColorScheme\Tests\Unit\Middleware;

use MaikSchneider\LoginColorScheme\Middleware\LoginColorSchemeStyles;
use MaikSchneider\LoginColorScheme\Service\LoginBackgroundImageStyles;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Backend\Routing\Route;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LoginColorSchemeStylesTest extends UnitTestCase
{
    #[Test]
    #[DataProvider('authenticationRoutesDataProvider')]
    public function stylesAreAddedOnAuthenticationScreens(string $routeIdentifier): void
    {
        $pageRenderer = $this->createMock(PageRenderer::class);
        $pageRenderer->expects(self::once())
            ->method('addCssInlineBlock')
            ->with('loginColorSchemeBackgroundImage', '.typo3-login {}', false, false, true);

        $this->process($routeIdentifier, '.typo3-login {}', $pageRenderer);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function authenticationRoutesDataProvider(): array
    {
        return [
            'login' => ['login'],
            'login refresh frame' => ['login_frameset'],
            'password forget' => ['password_forget'],
            'password reset' => ['password_reset_validate'],
            'mfa authentication' => ['auth_mfa'],
            'mfa setup' => ['setup_mfa'],
        ];
    }

    #[Test]
    public function stylesAreNotAddedOnOtherBackendRoutes(): void
    {
        $pageRenderer = $this->createMock(PageRenderer::class);
        $pageRenderer->expects(self::never())->method('addCssInlineBlock');

        $this->process('main', '.typo3-login {}', $pageRenderer);
    }

    #[Test]
    public function nothingIsAddedIfNoDarkImageIsConfigured(): void
    {
        $pageRenderer = $this->createMock(PageRenderer::class);
        $pageRenderer->expects(self::never())->method('addCssInlineBlock');

        $this->process('login', '', $pageRenderer);
    }

    #[Test]
    public function requestsWithoutARouteAreIgnored(): void
    {
        $pageRenderer = $this->createMock(PageRenderer::class);
        $pageRenderer->expects(self::never())->method('addCssInlineBlock');

        $this->process(null, '.typo3-login {}', $pageRenderer);
    }

    private function process(?string $routeIdentifier, string $styles, PageRenderer $pageRenderer): void
    {
        $backgroundImageStyles = self::createStub(LoginBackgroundImageStyles::class);
        $backgroundImageStyles->method('build')->willReturn($styles);

        $request = self::createStub(ServerRequestInterface::class);
        $request->method('getAttribute')->willReturn(
            $routeIdentifier === null ? null : new Route('/some/path', ['_identifier' => $routeIdentifier])
        );

        $handler = self::createStub(RequestHandlerInterface::class);
        $handler->method('handle')->willReturn(self::createStub(ResponseInterface::class));

        $subject = new LoginColorSchemeStyles($backgroundImageStyles, $pageRenderer);
        $subject->process($request, $handler);
    }
}
