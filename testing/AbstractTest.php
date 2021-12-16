<?php

/*
 * Abstract class all tests should extend.
 */


abstract class AbstractTest
{
    protected $m_passed = false;
    protected array $m_errorMessages = [];
    
    abstract public function getDescription() : string;
    abstract public function run();
    public function getPassed(): bool { return $this->m_passed; }

    public function getErrorMessages() : array { return $this->m_errorMessages; }


    public function runTest()
    {
        try
        {
            $this->run();
        }
        catch (Exception $ex)
        {
            $this->m_passed = false;
        }
    }
}