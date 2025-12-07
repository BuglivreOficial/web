<?php
namespace Core\Database;

use Appwrite\Client;

class Database
{

    public Client $client;

    public function __construct()
    {
        $client = new Client();

        $client
            ->setEndpoint($_ENV['API_ENDPOINT']) // Your API Endpoint
            ->setProject($_ENV['PROJECT_ID']) // Your project ID
            ->setKey($_ENV['KEY_SECRET']) // Your secret API key
            ->setSelfSigned() // Use only on dev mode with a self-signed SSL cert
        ;

        $this->client = $client;
    }

    public function getConnection()
    {
        return $this->client;
    }
}