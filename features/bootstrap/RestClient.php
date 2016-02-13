<?php
use Symfony\Component\Yaml\Yaml as Yaml;
use GuzzleHttp\Exception as GuzzleException;

class RestClient
{
    private $configFile; // path to certificate configuration file
   
    private $client; // Guzzle Rest Client instance
    private $options; // Guzzle Rest Client options

    public function __construct($options = [])
    {
        $this->options = $options;
        $this->initRestClient();
    }

    public function setConfigFile($filePath)
    {
        $this->configFile = $filePath;
        $this->setOptions();
    }

   

    private function initRestClient()
    {
        $this->options['headers']['Accept'] = 'application/json';

        $this->client = new \GuzzleHttp\Client($this->options);
    }

    public function client()
    {
        return $this->client;
    }

    public function run($method, $uri, $options = [])
    {
        $method = strtolower($method);
        if (!in_array($method, ['get', 'post', 'put', 'delete', 'patch', 'head', 'options'])) {
            throw new \Exception('Method is not allow');
        }

        try {
            $response = $this->client->$method($uri, $options);
        } catch (GuzzleException\ClientException $e) {
            $response = $e->getResponse();
        } catch (GuzzleException\BadResponseException $e) {
            $response = $e->getResponse();
        } catch (GuzzleException\RequestException $e) {
            $response = $e->getResponse();
        }

        return $response;
    }
}