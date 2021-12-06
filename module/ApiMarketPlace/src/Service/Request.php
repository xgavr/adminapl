<?php
/**
 * This file is part of the ApiMarketPlace.
 *
 */

namespace ApiMarketPlace\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use ApiMarketPlace\Exception\ApiMarketPlaceException;

class Request
{

    /**
     * Guzzle Client object
     *
     * @var \GuzzleHttp\Client
     */
    private static $client;

    /**
     * Input value of the request
     *
     * @var string
     */
    private static $input;


    /**
     * Set a custom Guzzle HTTP Client object
     *
     * @param Client $client
     *
     * @throws TelegramException
     */
    public static function setClient(Client $client)
    {
        if (!($client instanceof Client)) {
            throw new ApiMarketPlaceException('Invalid GuzzleHttp\Client pointer!');
        }

        self::$client = $client;
    }

    /**
     * Set input from custom input or stdin and return it
     *
     * @return string
     * @throws ApiMarketPlaceException
     */
    public static function getInput()
    {
        $input = file_get_contents('php://input');

        // Make sure we have a string to work with.
        if (!is_string($input)) {
            throw new ApiMarketPlaceException('Input must be a string!');
        }

        self::$input = $input;

        return self::$input;
    }


    /**
     * Execute HTTP Request
     *
     * @param string $action Action to execute
     * @param array  $data   Data to attach to the execution
     *
     * @return string Result of the HTTP Request
     * @throws ApiMarketPlaceException
     */
    public static function execute($action, array $data = [])
    {

        $result                  = null;

        try {
            $response = self::$client->post(
                '/bot' . self::$telegram->getApiKey() . '/' . $action,
                $request_params
            );
            $result   = (string) $response->getBody();

        } catch (RequestException $e) {
            $result = ($e->getResponse()) ? (string) $e->getResponse()->getBody() : '';
        }

        return $result;
    }

}
