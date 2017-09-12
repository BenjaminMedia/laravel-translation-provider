# Laravel Translation Provider
Laravel package that retrieves translations strings from Translation Manager and extends the laravel `trans()` localization function.

This only extends the `trans()` method, not the `__()` method.

## Usage
`trans(string $key, array $replace, string $locale)`
- $key - The translation key e.g `button-text`
- $replace - Array with replace values e.g. `[:orderid => $order_id]`
- $locale - String with locale, `null` for default. E.g. `da`, `en`, `sv`

## Default value
Because of limitations in extending `trans()`, to define a default value to a non-existing translation key, use a pipe to parse the default value.
```php
echo trans('trans-key|Translation value');
```

## Setup
- `composer require bonnier/laravel-translation-provider`
- Remove Laravel TranslationServiceProvider by commenting out this line:
```php
    ...
    'providers' => [
    ...
    Illuminate\Session\SessionServiceProvider::class,
    // Illuminate\Translation\TranslationServiceProvider::class,
    Illuminate\Validation\ValidationServiceProvider::class,
    ...
    ],
```
- Register the provider in config/app.php`
```php
    ...
    'providers' => [
        ...
       Bonnier\TranslationProvider\TranslationServiceProvider::class, 
    ],
```
- Setup configuration in `.env`
```
SERVICE_ID=1
TRANSLATIONMANAGER_URL=http://url-to-translationmanager.com
```
- Set up in `config/services.php`
```php
    'translation_manager' => [
        'url' => env('TRANSLATIONMANAGER_URL'),
        'service_id' => env('SERVICE_ID'),
    ],
```