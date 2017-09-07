<?php

namespace Bonnier\TranslationProvider\Console\Commands\Translation;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Console\Command;

class AddCommand extends Command
{
    protected $name = 'bonnier:translation:add';

    protected $signature = 'bonnier:translation:add {key?} {value?}';

    protected $description = 'Add new translation to the Translation Manager';

    private $service_id;
    private $client;

    public function __construct()
    {
        parent::__construct();

        $this->service_id = getenv('SERVICE_ID');
        $this->client = new Client([
            'base_uri' => getenv('TRANSLATIONMANAGER_URL'),
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
                $this->info('Translation was added!');
                return;
            }
        }

        $this->error('Translation could not be added.');
    }
}