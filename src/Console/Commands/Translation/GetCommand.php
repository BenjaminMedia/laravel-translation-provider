<?php

namespace Bonnier\TranslationProvider\Console\Commands\Translation;

use Bonnier\TranslationProvider\TranslationServiceProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Helper\ProgressBar;

class GetCommand extends Command
{
    protected $name = 'bonnier:translation:get';

    protected $signature = 'bonnier:translation:get {--brand= : The ID of the brand} {--locale= : What locale to get}';

    protected $description = 'Get translations from Translation Manager';

    /** @var int */
    private $service_id;

    /** @var Client */
    private $client;

    /** @var string */
    private $translationPath;

    /** @var ProgressBar */
    private $saveBar;

    public function __construct()
    {
        parent::__construct();

        $this->service_id = config('services.translation_manager.service_id');
        $this->client = new Client([
            'base_uri' => config('services.translation_manager.url'),
        ]);
        $this->translationPath = TranslationServiceProvider::getTranslationPath();
    }

    public function handle()
    {
        $brandId = $this->option('brand');
        $locale = $this->option('locale');

        $uri = '/api/v1/translations/service/'.$this->service_id;
        if($brandId && is_null($locale)) {
            $uri .= '/brand/'.$brandId;
        } elseif($brandId && $locale) {
            $uri .= '/brand/'.$brandId.'/locale/'.$locale;
        } elseif(is_null($brandId) && $locale) {
            $uri .= '/locale/'.$locale;
        }

        $this->info('Retrieving translations');
        try {
            $response = $this->client->get($uri);
        } catch(ClientException $e) {
            $this->error($e->getMessage());
            return;
        }

        $this->info('Parsing translations');
        $result = json_decode($response->getBody()->getContents());
        if($result) {
            $count = 10;
            $this->info(sprintf('Fetched %s translations', $count));
            $translations = $this->parseResult($result);
            if($translations) {
                $this->info('Saving translations');
                $this->saveBar = $this->output->createProgressBar($count);
                $this->saveTranslations($translations);
                $this->saveBar->finish();
                echo PHP_EOL;
            } else {
                $this->error('No translations to save');
            }
        } else {
            $this->error('Nothing found');
        }
    }

    private function parseResult($result)
    {
        $translations = [];
        foreach($result as $brandId => $brandTranslations) {
            foreach($brandTranslations as $translation) {
                foreach($translation->value as $locale => $value) {
                    $translations[$locale][$brandId][$translation->key] = $value;
                }
            }
        }
        return $translations;
    }

    /**
     * @param array $translations
     */
    private function saveTranslations(array $translations)
    {
        foreach($translations as $lang => $brands) {
            foreach($brands as $brandId => $translations) {
                if(!$this->writeTranslations($lang, $brandId, $translations)) {
                    $this->error('Failed to write translations');
                }
            }
        }
        echo PHP_EOL;
    }

    private function assignArrayByPath(&$arr, $path, $value, $separator='.') {
        $keys = explode($separator, $path);
        foreach ($keys as $key) {
            if(isset($arr[$key]) && !is_array($arr[$key])) {
                $arr[$key] = [];
            }
            $arr = &$arr[$key];
        }
        $arr = $value;
    }

    private function writeTranslations($lang, $brand, $translations)
    {
        $structure = $lang.DIRECTORY_SEPARATOR.$brand;
        $path = $this->translationPath.DIRECTORY_SEPARATOR.$structure;
        $translationArrays = [
            'messages' => [],
        ];

        if(!File::exists($path))
        {
            if(!File::makeDirectory($path, 0770, true))
            {
                $this->error(sprintf('Could not make directory \'%s\'', $structure));
            }
        }

        foreach ($translations as $key => $value) {
            if (!str_contains($key, '.')) {
                $key = 'messages.' . $key;
            }
            $this->assignArrayByPath($translationArrays, $key, $value, '.');
            $this->saveBar->advance();
        }

        foreach($translationArrays as $file => $translations) {
            $fileContent = "<?php".PHP_EOL;
            $fileContent .= PHP_EOL;
            $fileContent .= '// Translations fetched at '.date('Y-m-d H:i:s').PHP_EOL;
            $fileContent .= PHP_EOL;
            $fileContent .= "return ";

            $filePath = $path.DIRECTORY_SEPARATOR.$file.'.php';
            if(File::exists($filePath)) {
                File::delete($filePath);
            }
            File::put($filePath, $fileContent.var_export($translations, true).";".PHP_EOL);
        }

        return true;
    }
}
