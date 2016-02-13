<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

/**
 * Defines application features from the specific context.
 */
class OfferContext extends RestContext implements Context, SnippetAcceptingContext
{
    public function __construct($parameters)
    {
        parent::__construct($parameters);
    }
    
    /**
     * @Then I should see :arg1
     */
    public function iShouldSee($value)
    {
        $flag = false;
        foreach ($this->data['data'] as $record) {
            if (isset($record['name']) && (string) trim($record['name']) == $value) {
                $flag = true;
                break;
            }
        }
        
        if(!$flag) {
            throw new \Exception("The name: `$value` could not be found in record");
        }
    }
    

}
