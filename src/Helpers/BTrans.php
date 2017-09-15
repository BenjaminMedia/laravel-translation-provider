<?php


namespace Bonnier\TranslationProvider\Helpers;


use Bonnier\TranslationProvider\TranslationServiceProvider;
use Bonnier\ContextService\Context\Context;

class BTrans
{
    public static function translate($key, $replace = [], $locale = null)
    {
    	$brand = 'default';
    	if(app(Context::class)->getBrand()) {
    		$brand = app(Context::class)->getBrand()->getId();
    	}
        return trans(TranslationServiceProvider::TRANSLATION_NAMESPACE.'::'.$brand.DIRECTORY_SEPARATOR.$key, $replace, $locale);
    }
}