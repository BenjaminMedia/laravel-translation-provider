<?php


namespace Bonnier\TranslationProvider\Helpers;


use Bonnier\TranslationProvider\TranslationServiceProvider;

class BTrans
{
    public static function translate($key, $replace = [], $locale = null)
    {
        return trans(TranslationServiceProvider::TRANSLATION_NAMESPACE.'::'.TranslationServiceProvider::getBrandId().'/'.$key, $replace, $locale);
    }
}