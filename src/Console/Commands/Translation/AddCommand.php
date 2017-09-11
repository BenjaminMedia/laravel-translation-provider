<?php

namespace Bonnier\TranslationProvider\Console\Commands\Translation;

use Bonnier\TranslationProvider\TranslationServiceProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AddCommand extends Command
{
    protected $name = 'bonnier:translation:add';

    protected $signature = 'bonnier:translation:add {key?} {value?}';

    protected $description = 'Add new translation to the Translation Manager';

    private $service_id;
    private $client;
    private $translationPath;

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
        $translationKey = $this->argument('key');
        $translationValue = $this->argument('value');

        if(!$translationKey) {
            $translationKey = $this->ask('Enter translation key');
        }
        if(!$translationValue) {
            $translationValue = $this->ask('Enter translation value');
        }

        while(!preg_match('/^[a-z_-]+$/', $translationKey)) {
            $this->error('Translation key must match \'/^[a-z_-]+$/\'');
            $translationKey = $this->ask('Enter translation key');
        }

        $this->line('Adding translation...');
        try {
            $response = $this->client->post('/api/v1/translations/add', [
                'json' => [
                    'service_id' => $this->service_id,
                    'key' => $translationKey,
                    'value' => $translationValue,
                ]
            ]);
        } catch(ClientException $e) {
            if($e->getResponse()->getStatusCode() === 400) {
                $result = json_decode($e->getResponse()->getBody()->getContents());
                if(isset($result->status) && $result->status === 'error') {
                    $this->error('Could not add new translation: '.$result->message);
                    if(isset($result->errors->key)) {
                        $this->error('Error: '.$result->errors->key[0]);
                    }
                    return;
                }
            }
            $this->error('Could not add new translation: '.$e->getMessage());
            return;
        }

        if($response->getStatusCode() === 201) {
            $result = json_decode($response->getBody()->getContents());
            if(isset($result->status) && $result->status === 'success') {
                $this->saveTranslation($translationKey, $translationValue);
                $this->info('Translation was added!');
                return;
            }
        }

        $this->error('Translation could not be added.');
    }

    public function saveTranslation($key, $value)
    {
        $insert = ['    \''.$key.'\' => \''.$value.'\','];
        foreach(File::directories($this->translationPath) as $transDir) {
            foreach(File::directories($transDir) as $brandDir) {
                $filename = $brandDir.DIRECTORY_SEPARATOR.'messages.php';
                if(File::exists($filename)) {
                    $fileContents = File::get($filename);
                    if(str_contains($fileContents, '\''.$key.'\' =>')) {
                        continue;
                    }
                    $parts = preg_split('/\n/', $fileContents);
                    if(is_array($parts)) {
                        $parts = array_reverse($parts);
                        foreach($parts as $line => $string) {
                            if('];' === $string) {
                                array_splice($parts, $line+1, 0, $insert);
                                $parts = array_reverse($parts);
                                break;
                            }
                        }
                        $fileContents = implode(PHP_EOL, $parts);
                        File::put($filename, $fileContents);
                    }
                }
            }
        }
    }
}