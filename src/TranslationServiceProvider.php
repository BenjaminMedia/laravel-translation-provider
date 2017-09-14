<?php

namespace Bonnier\TranslationProvider;

use Bonnier\ContextService\Context\Context;
use Bonnier\ContextService\Models\BpBrand;
use Illuminate\Support\ServiceProvider;

class TranslationServiceProvider extends ServiceProvider
{
    const TRANSLATION_NAMESPACE = 'bonnier';

    protected $commands = [
        'Bonnier\TranslationProvider\Console\Commands\Translation\AddCommand',
        'Bonnier\TranslationProvider\Console\Commands\Translation\UpdateCommand',
        'Bonnier\TranslationProvider\Console\Commands\Translation\DeleteCommand',
        'Bonnier\TranslationProvider\Console\Commands\Translation\GetCommand',
    ];

    private static $translationPath;

    private static $brandId;

    public function boot()
    {
        $this->loadTranslationsFrom(self::getTranslationPath(), self::TRANSLATION_NAMESPACE);

        /** @var BpBrand $brand */
        $brand = app(Context::class)->getBrand();
        if($brand) {
            self::$brandId = $brand->getId();
        } else {
            self::$brandId = 'default';
        }
    }

    public function register()
    {
        $this->commands($this->commands);
    }

    public static function getTranslationPath()
    {
        if(!self::$translationPath) {
            self::$translationPath = __DIR__.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'lang';
        }
        return self::$translationPath;
    }

    public static function getBrandId()
    {
        return self::$brandId;
    }
}