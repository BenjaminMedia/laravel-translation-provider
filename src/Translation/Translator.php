<?php


namespace Bonnier\TranslationProvider\Translation;


use Illuminate\Support\Facades\Artisan;
use Illuminate\Translation\Translator as LaravelTranslator;

class Translator extends LaravelTranslator
{
    const TRANS_NAMESPACE = 'bonnier';
    protected $brandId = 'default';

    /**
     * Overriding Laravel Translation method
     *
     * @see \Illuminate\Translation\Translator
     *
     * @param string $key
     * @param array $replace
     * @param string|null $locale
     *
     * @return array|null|string
     */
    public function trans($key, array $replace = [], $locale = null)
    {
        $value = null;
        if(str_contains($key, '|')) {
            list($key, $value) = array_map('trim', explode('|', $key));
        }

        if(str_contains($key, 'messages')) {
            $bonnierKey = self::TRANS_NAMESPACE.'::'.$this->brandId.'/'.$key;
        } else {
            $bonnierKey = self::TRANS_NAMESPACE.'::'.$this->brandId.'/messages.'.$key;
        }

        $translation = parent::trans($bonnierKey, $replace, $locale);
        if($translation !== $bonnierKey) {
            return $translation;
        }
        $translation = parent::trans($key, $replace, $locale);
        if($translation !== $key) {
            return $translation;
        }

        if(is_null($value)) {
            $value = $key;
        }

        Artisan::call('bonnier:translation:add', ['key' => $key, 'value' => $value]);

        return parent::trans($value, $replace, $locale);
    }

    public function setBrandId($brandId)
    {
        $this->brandId = $brandId;
    }
}