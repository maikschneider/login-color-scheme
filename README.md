# Login Color Scheme

[![TYPO3 13](https://img.shields.io/badge/TYPO3-13.4-orange.svg)](https://get.typo3.org/version/13)
[![TYPO3 14](https://img.shields.io/badge/TYPO3-14-orange.svg)](https://get.typo3.org/version/14)
[![License](https://img.shields.io/badge/license-GPL--2.0--or--later-blue.svg)](LICENSE.md)

The TYPO3 backend login screen already follows the visitor's preferred color scheme, but
`$GLOBALS['TYPO3_CONF_VARS']['BE']['loginBackgroundImage']` only accepts a single image. A
photo that looks right on the light login card is usually far too bright once the screen
switches to dark mode.

This extension adds the missing counterpart: **one extra setting for the dark mode
background image**. Nothing else changes — the core setting keeps handling light mode.

Solves [TYPO3 forge issue #106760](https://forge.typo3.org/issues/106760).

## Installation

```bash
composer require maikschneider/login-color-scheme
```

## Configuration

Set the dark image in *Admin Tools → Settings → Extension Configuration → login_color_scheme*,
or directly in `config/system/settings.php`:

```php
'EXTENSIONS' => [
    'backend' => [
        // light mode (TYPO3 core)
        'loginBackgroundImage' => 'EXT:my_sitepackage/Resources/Public/Images/login.jpg',
    ],
    'login_color_scheme' => [
        // dark mode (this extension)
        'backgroundImageDark' => 'EXT:my_sitepackage/Resources/Public/Images/login-dark.jpg',
    ],
],
```

`backgroundImageDark` accepts the same values as the core setting: an `EXT:` path, a path
relative to the project root (`fileadmin/…`), or an absolute URL.

Setting only the dark image is valid too — light mode then falls back to the plain
background color, exactly as an unconfigured TYPO3 does.

## How it works

A backend middleware adds one inline CSS block to every screen rendering the login layout
(login, login refresh, password reset, MFA):

```css
@media (prefers-color-scheme: dark) {
    :root:not([data-color-scheme="light"]) .typo3-login { background-image: url("…"); }
}
:root[data-color-scheme="dark"] .typo3-login { background-image: url("…"); }
```

Two rules, because the backend has three color scheme states. The authentication screens
carry no `data-color-scheme` attribute and therefore follow the operating system, while a
logged-in user hitting the login refresh dialog may have pinned light or dark in their user
settings. Both are covered, and the higher specificity means the block wins over the core
`loginBackgroundImage` rule regardless of render order.

The URL is passed through `GeneralUtility::sanitizeCssVariableValue()`, the same escaping
core uses, and the block is emitted with a CSP nonce.

Two plain image files, and the browser only downloads the one it needs.

## Scope

Only the background image. The login logo (`loginLogo`) and highlight color
(`loginHighlightColor`) have the same problem, but the logo is inlined by a ViewHelper and
needs a different seam — see [issue #107558](https://forge.typo3.org/issues/107558).
Contributions welcome.

## Development

```bash
composer install
composer sca      # normalize, editorconfig, lint, php-cs-fixer, phpstan, xliff, yaml
composer tests    # unit tests
```

## License

GPL-2.0-or-later
