<?php

namespace LaravelShipStation;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class ShipStation
{
    /**
     * @var string The current endpoint for the API. The default endpoint is /orders/
     */
    public $endpoint = '/orders/';

    /**
     * @var \GuzzleHttp\Client The http client used when calling the API.
     */
    public $client = null;

    /**
     * @var array Our list of valid ShipStation endpoints.
     */
    private $endpoints = [
        '/accounts/',
        '/carriers/',
        '/customers/',
        '/fulfillments/',
        '/orders/',
        '/products/',
        '/shipments/',
        '/stores/',
        '/users/',
        '/warehouses/',
        '/webhooks/',
    ];

    /**
     * @var string Base API URL for ShipStation
     */
    private $base_uri = 'https://ssapi.shipstation.com';

    /** @var int */
    private $maxAllowedRequests = 0;

    /** @var int|null */
    private $remainingRequests = null;

    /** @var int */
    private $secondsUntilReset = 0;

    /**
     * ShipStation constructor.
     *
     * @param  string  $apiKey
     * @param  string  $apiSecret
     * @param  string  $apiURL
     * @param  string|null  $partnerApiKey
     * @throws \Exception
     */
    public function __construct($apiKey, $apiSecret, $apiURL, $partnerApiKey = null)
    {
        if (! isset($apiKey, $apiSecret)) {
            throw new \Exception('Your API key and/or private key are not set. Did you run artisan vendor:publish?');
        }

        $this->base_uri = $apiURL;

        $headers = [
            'Authorization' => 'Basic '.base64_encode("{$apiKey}:{$apiSecret}"),
        ];

        if (! empty($partnerApiKey)) {
            $headers['x-partner'] = $partnerApiKey;
        }

        $this->client = new Client([
            'base_uri' => $this->base_uri,
            'headers'  => $headers,
        ]);

        require ("/var/www/order.monogramonline.com/library/LaravelShipStation/Helpers/Endpoint.php");
        require ("/var/www/order.monogramonline.com/library/LaravelShipStation/Helpers/Orders.php");
        require ("/var/www/order.monogramonline.com/library/LaravelShipStation/Helpers/Shipments.php");


        require ("/var/www/order.monogramonline.com/library/LaravelShipStation/Models/Address.php");
        require ("/var/www/order.monogramonline.com/library/LaravelShipStation/Models/AdvancedOptions.php");
        require ("/var/www/order.monogramonline.com/library/LaravelShipStation/Models/CustomsItem.php");
        require ("/var/www/order.monogramonline.com/library/LaravelShipStation/Models/Dimensions.php");
        require ("/var/www/order.monogramonline.com/library/LaravelShipStation/Models/InsuranceOptions.php");
        require ("/var/www/order.monogramonline.com/library/LaravelShipStation/Models/InternationalOptions.php");
        require ("/var/www/order.monogramonline.com/library/LaravelShipStation/Models/ItemOption.php");
        require ("/var/www/order.monogramonline.com/library/LaravelShipStation/Models/Order.php");
        require ("/var/www/order.monogramonline.com/library/LaravelShipStation/Models/OrderItem.php");
        require ("/var/www/order.monogramonline.com/library/LaravelShipStation/Models/Product.php");
        require ("/var/www/order.monogramonline.com/library/LaravelShipStation/Models/ProductCategory.php");
        require ("/var/www/order.monogramonline.com/library/LaravelShipStation/Models/ProductTag.php");
        require ("/var/www/order.monogramonline.com/library/LaravelShipStation/Models/Webhook.php");
        require ("/var/www/order.monogramonline.com/library/LaravelShipStation/Models/Weight.php");
    }

    /**
     * Get a resource using the assigned endpoint ($this->endpoint).
     *
     * @param  array  $options
     * @param  string  $endpoint
     * @return \stdClass
     */
    public function get($options = [], $endpoint = '')
    {
        $response = $this->client->request('GET', "{$this->endpoint}{$endpoint}", ['query' => $options]);

        $this->sleepIfRateLimited($response);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Post to a resource using the assigned endpoint ($this->endpoint).
     *
     * @param  array  $options
     * @param  string  $endpoint
     * @return \stdClass
     */
    public function post($options = [], $endpoint = '')
    {
        $response = $this->client->request('POST', "{$this->endpoint}{$endpoint}", ['json' => $options]);

        $this->sleepIfRateLimited($response);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Delete a resource using the assigned endpoint ($this->endpoint).
     *
     * @param  string  $endpoint
     * @return \stdClass
     */
    public function delete($endpoint = '')
    {
        $response = $this->client->request('DELETE', "{$this->endpoint}{$endpoint}");

        $this->sleepIfRateLimited($response);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Update a resource using the assigned endpoint ($this->endpoint).
     *
     * @param  array  $options
     * @param  string  $endpoint
     * @return \stdClass
     */
    public function update($options = [], $endpoint = '')
    {
        $response = $this->client->request('PUT', "{$this->endpoint}{$endpoint}", ['json' => $options]);

        $this->sleepIfRateLimited($response);

        return json_decode($response->getBody()->getContents());
    }

    /**
     * Get the maximum number of requests that can be sent per window.
     *
     * @return int
     */
    public function getMaxAllowedRequests()
    {
        return $this->maxAllowedRequests;
    }

    /**
     * Get the remaining number of requests that can be sent in the current window.
     *
     * @return int
     */
    public function getRemainingRequests()
    {
        return $this->remainingRequests;
    }

    /**
     * Get the number of seconds remaining until the next window begins.
     *
     * @return int
     */
    public function getSecondsUntilReset()
    {
        return $this->secondsUntilReset;
    }

    /**
     * Are we currently rate limited?
     * We are if there are no more requests allowed in the current window.
     *
     * @return bool
     */
    public function isRateLimited()
    {
        return $this->remainingRequests !== null && ! $this->remainingRequests;
    }

    /**
     * Check to see if we are about to rate limit and pause if necessary.
     *
     * @param Response $response
     */
    public function sleepIfRateLimited(Response $response)
    {
        $this->maxAllowedRequests = (int) $response->getHeader('X-Rate-Limit-Limit')[0];
        $this->remainingRequests = (int) $response->getHeader('X-Rate-Limit-Remaining')[0];
        $this->secondsUntilReset = (int) $response->getHeader('X-Rate-Limit-Reset')[0];

        if ($this->isRateLimited() || ($this->secondsUntilReset / $this->remainingRequests) > 1.5) {
            usleep(1500000);
        }
    }

    /**
     * Set our endpoint by accessing it via a property.
     *
     * @param  string $property
     * @return $this
     */
    public function __get($property)
    {
        if (in_array('/'.$property.'/', $this->endpoints)) {
            $this->endpoint = '/'.$property.'/';
        }

        $className = 'LaravelShipStation\\Helpers\\'.ucfirst($property);

        if (class_exists($className)) {
            return new $className($this);
        }

        return $this;
    }
}
