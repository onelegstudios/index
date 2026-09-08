<div align="center">
    <h1>Index</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/onelegstudios/index"><img src="https://img.shields.io/packagist/v/onelegstudios/index.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/onelegstudios/index"><img src="https://img.shields.io/packagist/php-v/onelegstudios/index.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/onelegstudios/index"><img src="https://badge.laravel.cloud/badge/onelegstudios/index?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/onelegstudios/index/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/onelegstudios/index/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/onelegstudios/index"><img src="https://img.shields.io/packagist/dt/onelegstudios/index.svg?style=flat-square" alt="Total Downloads"></a>
</p>

Beautiful documentation for Livewire/Laravel.

## Installation

You can install the package via Composer:

```bash
composer require onelegstudios/index
```

You may publish all of the package's resources at once:

```bash
php artisan vendor:publish --tag="index"
```

Or, you may publish each resource individually:

### Publishing the Configuration File

```bash
php artisan vendor:publish --tag="index-config"
```

### Publishing and Running the Migrations

```bash
php artisan vendor:publish --tag="index-migrations"
php artisan migrate
```

### Publishing the Views

```bash
php artisan vendor:publish --tag="index-views"
```

### Publishing the Translations

```bash
php artisan vendor:publish --tag="index-lang"
```

### Publishing the Public Assets

```bash
php artisan vendor:publish --tag="index-assets"
```

## Usage

<!-- Add a basic usage example here. -->

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to Index! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Oneleggedswede](https://github.com/onelegstudios)
- [All Contributors](../../contributors)

## License

Index is open-sourced software licensed under the [MIT license](LICENSE.md).
