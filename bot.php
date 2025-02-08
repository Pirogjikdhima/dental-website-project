<?php
require './vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header("Location: 404.html");
}
use GuzzleHttp\Client;

class GroqConnection {
    private $client;
    private $apiKey;

    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
        $this->client = new Client([
            'base_uri' => $_ENV['GROQ_BASE_URI'],
            'verify' => true,
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    public function generateText($model, $prompt) {
        try {
            $response = $this->client->post('chat/completions', [
                'json' => [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a helpful dental clinic assistant. Provide clear, concise answers about dental procedures, appointments, and general dental health questions.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'max_tokens' => 300
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            return [
                'success' => true,
                'message' => $body['choices'][0]['message']['content']
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
    $userMessage = $data['message'] ?? '';

    $apiKey = $_ENV['GROQ_API_KEY'];
    $groq = new GroqConnection($apiKey);
    $response = $groq->generateText($_ENV['GROQ_MODEL'], $userMessage);

    echo json_encode($response);
}