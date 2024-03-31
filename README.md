# Open API Laravel

This package is aimed at generating OpenAPI specification schema by parsing the api routes and form request classes.

## Usage

### Installation

To install this package locally, run the following command

```bash
composer require chinmay/open-api-laravel --dev
```

### Configuration

The routes to be included/excluded, schema path, etc. are configured via [openapi.php](config/openapi.php) config file.

To publish the file run the following command.

```bash
php artisan vendor:publish --tag=openapi
```

### Generate Schema

To generate schema run the following command

```bash
php artisan openapi:generate
```

## License

This project is licensed under the [MIT License](LICENSE).

## Status

**Work in Progress:** This project is actively being developed
