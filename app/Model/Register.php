<?php
namespace App\Model;

use Appwrite\AppwriteException;
use Appwrite\Client;
use Appwrite\ID;
use Appwrite\Services\Users;

class Register
{

    public $name;

    public $email;

    public $password;

    private $client;
    public function __construct(string $name, string $email, string $password)
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;

        $client = new Client();

        $client
            ->setEndpoint($_ENV['API_ENDPOINT']) // Your API Endpoint
            ->setProject($_ENV['PROJECT_ID']) // Your project ID
            ->setKey($_ENV['KEY_SECRET']) // Your secret API key
            ->setSelfSigned() // Use only on dev mode with a self-signed SSL cert
        ;

        $this->client = $client;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        $options = [
            // Aumenta o custo bcrypt de 12 para 13.
            'cost' => 13,
        ];
        return password_hash($this->password, PASSWORD_BCRYPT, $options);
    }

    public function create()
    {
        try {
            $users = new Users($this->client);

            $result = $users->create(
                userId: ID::unique(),
                email: $this->getEmail(), // optional
                password: $this->getPassword(), // optional
                name: $this->getName() // optional
            );

            return $result;
        } catch (AppwriteException $error) {
            //throw $th;
            var_dump($error->getResponse());
        }
    }
}