# Laravel Translation Provider
Laravel package that retrieves translations strings from Translation Manager and extends the laravel __() localization function.

## Setup
- ```composer require bonnier/laravel-translation-provider```
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