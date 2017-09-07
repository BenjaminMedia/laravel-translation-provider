<?php


namespace Bonnier\TranslationProvider\Console\Commands\Translation;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Console\Command;

class DeleteCommand extends Command
{
    protected $name = 'bonnier:translation:delete';

    protected $signature = 'bonnier:translation:delete {key?} {--F|force}';

    protected $description = 'This command will delete the translation - use with care!';

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

        if(!$translationKey) {
            $translationKey = $this->ask('Enter translation key');
        }

        while(!preg_match('/^[a-z_-]+$/', $translationKey)) {
            $this->error('Translation key must match \'/^[a-z_-]+$/\'');
            $translationKey = $this->ask('Enter translation key');
        }

        if($this->option('force') || $this->confirm('Are your sure, you want to delete this translation?'))
        {
            $this->line('Deleting translation...');
            try {
                $response = $this->client->post('/api/v1/translations/delete', [
                    'json' => [
                        'service_id' => $this->service_id,
                        'key' => $translationKey,
                    ]
                ]);
            } catch(ClientException $e) {
                if($e->getResponse()->getStatusCode() === 400) {
                    $result = json_decode($e->getResponse()->getBody()->getContents());
                    if(isset($result->status) && $result->status === 'error') {
                        $this->error('Could not delete translation: '.$result->message);
                        if(isset($result->errors->key)) {
                            $this->error('Error: '.$result->errors->key[0]);
                        }
                        return;
                    }
                }
                $this->error('Could not delete translation: '.$e->getMessage());
                return;
            }

            if($response->getStatusCode() === 200) {
                $result = json_decode($response->getBody()->getContents());
                if(isset($result->status) && $result->status === 'success') {
                    $this->info('Translation was deleted!');
                    return;
                }
            }

            $this->error('Translation could not be deleted.');
            return;
        }

        $this->error('The translation was NOT deleted');
    }
}