# Contributing

Thanks for taking the time to contribute.

## Getting started

```bash
composer install
```

## Before opening a pull request

```bash
composer sca      # composer normalize, editorconfig, php lint, php-cs-fixer, phpstan, xliff, yaml
composer tests    # unit tests
```

Both commands run in CI on every push and pull request against TYPO3 13.4 and 14.3 on
PHP 8.2 to 8.4. Please make sure they pass locally first.

## Guidelines

- Follow the [TYPO3 Coding Guidelines](https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/CodingGuidelines/Index.html);
  `composer php:fixer` fixes most of it automatically.
- Keep the extension small. It solves one problem — if a change grows beyond that, open an
  issue first so we can discuss the scope.
- Add a unit test for every behavioral change.
- Add a `CHANGELOG.md` entry for anything user facing.

## Reporting bugs

Open an issue with your TYPO3 version, PHP version, the configured value of
`backgroundImageDark`, and the rendered `<style>` block from the login page source.
