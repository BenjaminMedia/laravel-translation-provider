<?php

namespace Bonnier\TranslationProvider;

use Illuminate\Support\ServiceProvider;

class SystemTextProvider extends ServiceProvider
{
    private static $translationPath;

    protected $commands = [
        'Bonnier\TranslationProvider\Console\Commands\Translation\AddCommand',
        'Bonnier\TranslationProvider\Console\Commands\Translation\UpdateCommand',
        'Bonnier\TranslationProvider\Console\Commands\Translation\DeleteCommand',
    ];

    public function boot()
    {
    }

    public function register()
    {
        $this->commands($this->commands);
    }
}