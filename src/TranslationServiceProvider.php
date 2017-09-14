<?php

namespace Bonnier\TranslationProvider;

use Bonnier\ContextService\Context\Context;
use Bonnier\ContextService\Models\BpBrand;
use Bonnier\TranslationProvider\Translation\Translator;

class TranslationServiceProvider extends \Illuminate\Translation\TranslationServiceProvider
{
    protected $commands = [
        'Bonnier\TranslationProvider\Console\Commands\Translation\AddCommand',
        'Bonnier\TranslationProvider\Console\Commands\Translation\UpdateCommand',
        'Bonnier\TranslationProvider\Console\Commands\Translation\DeleteCommand',
        'Bonnier\TranslationProvider\Console\Commands\Translation\GetCommand',
    ];

    private static $translationPath;

    public function boot()
    {
        $this->loadTranslationsFrom(self::getTranslationPath(), 'bonnier');
        /** @var BpBrand $brand */
        $brand = app(Context::class)->getBrand();
        if($brand) {
            app('translator')->setBrandId($brand->getId());
        }
    }

    public function register()
    {
        $this->commands($this->commands);

        parent::register();

        $this->app->singleton('translator', function ($app) {
            $loader = $app['translation.loader'];

            $locale = $app['config']['app.locale'];

            $trans = new Translator($loader, $locale);

            $trans->setFallback($app['config']['app.fallback_locale']);

            return $trans;
        });
    }

    public static function getTranslationPath()
    {
        if(!self::$translationPath) {
            self::$translationPath = __DIR__.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'lang';
        }
        return self::$translationPath;
    }
}