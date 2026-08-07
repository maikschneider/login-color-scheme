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

namespace MaikSchneider\LoginColorScheme\Middleware;

use MaikSchneider\LoginColorScheme\Service\LoginBackgroundImageStyles;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Backend\Routing\Route;
use TYPO3\CMS\Core\Page\PageRenderer;

/**
 * Adds the dark mode background image styles to every backend screen that
 * renders the login layout. The block is registered before the controller
 * runs, the shared PageRenderer emits it next to the core "loginBackgroundImage"
 * block.
 */
final class LoginColorSchemeStyles implements MiddlewareInterface
{
    /**
     * Backend routes rendering the ".typo3-login" layout.
     */
    private const AUTHENTICATION_ROUTES = [
        'login',
        'login_frameset',
        'password_forget',
        'password_reset_validate',
        'auth_mfa',
        'setup_mfa',
    ];

    public function __construct(
        private readonly LoginBackgroundImageStyles $loginBackgroundImageStyles,
        private readonly PageRenderer $pageRenderer,
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->isAuthenticationScreen($request)) {
            $styles = $this->loginBackgroundImageStyles->build($request);
            if ($styles !== '') {
                $this->pageRenderer->addCssInlineBlock('loginColorSchemeBackgroundImage', $styles, false, false, true);
            }
        }

        return $handler->handle($request);
    }

    private function isAuthenticationScreen(ServerRequestInterface $request): bool
    {
        $route = $request->getAttribute('route');
        if (!$route instanceof Route) {
            return false;
        }

        return in_array((string)$route->getOption('_identifier'), self::AUTHENTICATION_ROUTES, true);
    }
}
