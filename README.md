# Laravel Translation Provider
Laravel package that retrieves translations strings from Translation Manager and makes them available for Laravel translation functions. 


## Usage
To use the translator call

`Btrans::translate($key, $replace, $locale)`

instead of 

`trans($key, $replace, $locale)`

## Add translation
To add a translation, run:

`php artisan bonnier:translation:add "translation-key" "Translation value"`

## Update translation
To update an exisiting translations, run:

`php artisan bonnier:translation:update "translation-key" "Translation value"`

## Delete translation
To delete an existing translation, run: *VERY DESTRUCTIVE*

`php artisan bonnier:translation:delete "translation-key"`

## Getting translations
To get all translations from Translation Manager, run:

`php artisan bonnier:translation:get`

## Setup
- `composer require bonnier/laravel-translation-provider`
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