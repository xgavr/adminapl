<?php

declare(strict_types=1);

namespace Admin\Service;

use Laminas\Http\Client;
use Laminas\Http\Request;
use RuntimeException;

class MaxNotifierService
{
    private string $apiUrl;
    private string $token;
    private int $defaultChatId;
    private Client $httpClient;

    public function __construct(string $apiUrl, string $token, int $defaultChatId, int $adminId, ?Client $httpClient = null)
    {
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->token = $token;
        $this->defaultChatId = $defaultChatId;
        $this->adminId = $adminId;
        $this->httpClient = $httpClient ?? new Client();
    }

    /**
     * Отправляет текстовое сообщение в корпоративную группу MAX.
     *
     * @param string   $text   Текст сообщения (до 4000 символов)
     * @param string   $format Формат разметки: 'markdown' или 'html'
     * @param int|null $chatId Опциональный ID другого чата
     */
    public function sendToGroup(string $text, string $format = 'html', ?int $chatId = null): bool
    {
        $targetChatId = $chatId ?? $this->defaultChatId;
        $endpoint = sprintf('%s/messages?chat_id=%d', $this->apiUrl, $targetChatId);

        $payload = json_encode([
            'text'   => $text,
            'format' => $format,
            'notify' => true,
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $request->setUri($endpoint);
        $request->getHeaders()->addHeaders([
            'Authorization' => $this->token,
            'Content-Type'  => 'application/json',
        ]);
        $request->setContent($payload);

        $response = $this->httpClient->send($request);

        if (!$response->isSuccess()) {
            throw new RuntimeException(sprintf(
                'Ошибка отправки в MAX API (HTTP %d): %s',
                $response->getStatusCode(),
                $response->getBody()
            ));
        }

        return true;
    }
    
    public function sendToAdmin(string $text, string $format = 'html')
    {
        $targetChatId = $this->adminId;
        if ($targetChatId){
            return $this->sendToGroup($text, $format, $targetChatId);
        }
        
        return;
    }
}
