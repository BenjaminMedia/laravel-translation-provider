# Laravel Translation Provider
Laravel package that retrieves translations strings from Translation Manager and extends the laravel __() localization function.

This only extends the `trans()` method, not the `__()` method.

## Usage
`trans(string $key, array $replace, string $locale, string 1value)`
- $key - The translation key e.g `button-text`
- $replace - Array with replace values e.g. `[:orderid => $order_id]`
- $locale - String with locale, `null` for default. E.g. `da`, `en`, `sv`
- $value - Default value, if key is not defined. This value will also be used to send a 'add translation' request to the translation manager

## Setup
- ```composer require bonnier/laravel-translation-provider```
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
- Register the provider in ```config/app.php```
```php
    ...
    'providers' => [
        ...
       Bonnier\TranslationProvider\TranslationServiceProvider::class, 
    ],
```
- Setup configuration in ```.env```
```
SERVICE_ID=1
TRANSLATIONMANAGER_URL=http://url-to-translationmanager.com
```