<?php

namespace App\Helper;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenAIHelper
{
    const ENDPOINT = 'https://api.openai.com/v1/chat/completions';

    public function __construct(
        protected string $openAIKey,
        protected string $prompt,
        protected HttpClientInterface $http,
        protected LoggerInterface $logger,
    ) {
    }

    public function ask(string $document, string $message) {
        $response = $this->http->request('POST', self::ENDPOINT, [
            'headers' => [
                'Authorization' => "Bearer $this->openAIKey",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $this->generatePrompt($document),
                    ],
                    [
                        'role' => 'user',
                        'content' => $message,
                    ],
                ],
            ],
        ]);

        if ($response->getStatusCode() !== 200) {
            $this->logger->error('Failed to get response from OpenAI', [
                'status' => $response->getStatusCode(),
                'content' => $response->getContent(),
            ]);
            throw new \Exception('Failed to get response from OpenAI');
        }

        return $response->toArray()['choices'][0]['message']['content'];
    }

    protected function generatePrompt(string $document) {
        return str_replace('[[ DOCUMENT ]]', $document, $this->prompt);
    }
}
