<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Doctrine\Common\Util\Inflector;

class RestContext
{
    protected $domain;
    protected $response;
    protected $restClient;

    public function __construct($parameters)
    {
        $this->domain = $parameters['domain'];
        $this->restClient = new RestClient([
            'base_uri' => $this->domain
        ]);
    }
    
    /**
     * @When /^I request "([^"]*)" with method "([^"]*)" and content type is "(json|form)"$/
     */
    public function request($endPointUri, $method, $contentType, $string = null)
    {
        $this->restClient = new RestClient([
            'base_uri' => $this->domain
        ]);

        if ($string !== null) {
            $this->formData = json_decode($string, true);
            if (!is_array($this->formData) && empty($this->formData)) {
                throw new \Exception("Your string data must be in JSON format");
            }
        }

        $this->send($method, $endPointUri, $contentType);

    }

    /**
     * @Then /^HTTP status code should be (\d+)$/
     */
    public function httpCodeIs($number)
    {
        $this->httpStatus = (string) $this->response->getStatusCode();
        if (substr($this->httpStatus, 0, 1) == '4') {
            print_r($this->data);
        }
        if ($number !== $this->httpStatus) {
            throw new \Exception("HTTP status code is {$this->httpStatus}");
        }
    }


    /**
     * @Then /^The response should be JSON$/
     */
    public function isJson()
    {
        if (empty($this->data)) {
            throw new \Exception("Response was not 'JSON'");
        }
    }

    /**
     * @When /^Found records$/
     */
    public function foundRecords()
    {
        if (empty($this->data['data'])) {
            throw new \Exception("Empty data");
        }

        return true;
    }

    /**
     * @Then /^Record (\d+) should have property "([^"]*)" equal to "([^"]*)"$/
     */
    public function recordPropEqualTo($recordIndex, $propName, $value)
    {
        if (!isset($this->data['data'][$recordIndex])) {
            throw new \Exception("Record with index $recordIndex could not be found");
        }

        $record = $this->data['data'][$recordIndex];
        if (!isset($record[$propName]) || (string) $record[$propName] !== $value) {
            throw new \Exception("The `$propName` property with value `$value` could not be found in record");
        }
    }

    /**
     * @Then /^I should have location of new object$/
     */
    public function location()
    {
        if (!isset($this->data['data']['location'])) {
            throw new \Exception("Location could not be found");
        }

        $this->location = $this->data['data']['location'];
        print_r($this->location);
    }

    /**
     * @When /^Not found records$/
     */
    public function notFoundRecords()
    {
        if (!empty($this->data['data'])) {
            throw new \Exception("Records are not empty");
        }
    }

    /**
     * @Given /^Set of properties:$/
     */
    public function properties(TableNode $table)
    {
        $this->properties = array();
        foreach ($table->getHash() as $hash) {
            $this->properties[] = lcfirst(Inflector::classify($hash['property']));
        }
        $this->properties = $this->classifyProperties($this->properties);
    }

    /**
     * @Then /^Each record should have given properties$/
     */
    public function eachHasProperties()
    {
        foreach ($this->data['data'] as $record) {
            $keys = array_keys($record);
            $keys = $this->classifyProperties($keys);
            if ($keys !== $this->properties) {
                throw new \Exception("Properties in record are not equal with sample properties");
            }
        }
    }

    /**
     * @Then /^Property "([^"]*)" should equal to "([^"]*)"$/
     */
    public function propEqualTo($propName, $value)
    {
        $record = $this->data['data'];
        if (!isset($record[$propName]) || (string) $record[$propName] !== $value) {
            throw new \Exception("The `$propName` property with value `$value` could not be found in record");
        }
    }

    /**
     * @Then /^Each record should have property "([^"]*)" and equal to "([^"]*)"$/
     */
    public function eachPropEqualTo($propName, $value)
    {
        foreach ($this->data['data'] as $record) {
            if (!isset($record[$propName]) || (string) $record[$propName] !== $value) {
                throw new \Exception("The `$propName` property with value `$value` could not be found in record");
            }
        }
    }

    /**
     * @Given /^I have a set of data:$/
     */
    public function records(TableNode $table)
    {
        $this->records = array();
        foreach ($table->getHash() as $record) {
            if (!isset($record['id'])) {
                throw new \Exception("Missing column id from set of records");
            }
            $tmpRecord = $record;
            unset($tmpRecord['id']);
            $this->records[$record['id']] = $tmpRecord;
        }
    }

    /**
     * @Then /^I set form data from record (\d+)$/
     */
    public function formData($recordIndex)
    {
        if (!isset($this->records[$recordIndex])) {
            throw new \Exception("$recordIndex can not be found from set of records");
        }
        $this->formData = $this->records[$recordIndex];
    }

    public function classifyProperties(array $properties)
    {
        $result = array();
        foreach ($properties as $property) {
            $result[] = lcfirst(Inflector::classify($property));
        }

        asort($result);
        $result = array_values($result);
        return $result;
    }

    public function mapContentType($contentType)
    {
        $types = [
            'json' => 'json',
            'form' => 'form_params'
        ];

        return $types[$contentType];
    }

    public function send($method, $endPointUri, $contentType)
    {
        $method = strtolower($method);
        switch ($method) {
            case 'get':
                $this->response = $this->restClient->run($method, $endPointUri);
                break;

            case 'post':
            case 'put':
            case 'patch':
            case 'delete':
                if (!empty($this->formData)) {
                    $formData = [$this->mapContentType($contentType) => $this->formData];
                    $this->response = $this->restClient->run($method, $endPointUri, $formData);
                } else {
                    $this->response = $this->restClient->run($method, $endPointUri);
                }
                break;

            default:
                throw new \Exception("Method $method is now allow");

        }

        $this->data = json_decode($this->response->getBody(), true);
    }
}
