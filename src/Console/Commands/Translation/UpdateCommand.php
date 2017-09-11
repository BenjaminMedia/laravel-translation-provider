<?php

namespace Bonnier\TranslationProvider\Console\Commands\Translation;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Console\Command;

class UpdateCommand extends Command
{
    protected $name = 'bonnier:translation:update';

    protected $signature = 'bonnier:translation:update {key?} {value?}';

    protected $description = 'Update existing translation in the Translation Manager';

    private $service_id;
    private $client;

    public function __construct()
    {
        parent::__construct();

        $this->service_id = config('services.translation_manager.service_id');
        $this->client = new Client([
            'base_uri' => config('services.translation_manager.url'),
        ]);
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

        $this->line('Updating translation...');
        try {
            $response = $this->client->post('/api/v1/translations/update', [
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
                    $this->error('Could not update translation: '.$result->message);
                    if(isset($result->errors->key)) {
                        $this->error('Error: '.$result->errors->key[0]);
                    }
                    return;
                }
            }
            $this->error('Could not update translation: '.$e->getMessage());
            return;
        }

        if($response->getStatusCode() === 200) {
            $result = json_decode($response->getBody()->getContents());
            if(isset($result->status) && $result->status === 'success') {
                $this->info('Translation was updated!');
                return;
            }
        }

        $this->error('Translation could not be updated.');
    }
}