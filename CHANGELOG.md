# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - Unreleased

### Added

- `backgroundImageDark` extension configuration setting for the TYPO3 backend login
  background image in dark mode, solving [forge #106760](https://forge.typo3.org/issues/106760)
- Support for both color scheme mechanisms: the `prefers-color-scheme` media query on the
  authentication screens and the explicit `data-color-scheme` attribute set for logged-in
  users on the login refresh dialog
- Coverage for all screens rendering the login layout: login, login refresh, password
  reset and MFA
