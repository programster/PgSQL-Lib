<?php

/*
 * Exception to throw if we encounter an unexpected value type that we aren't sure how to process.
 */


namespace Programster\PgsqlLib\Exceptions;


class ExceptionUnexpectedValueType extends \Exception
{
    private $m_unexpectedValue;


    /**
     * Create a new unexpected value type exception.
     * @param type $unexpectedValue - the value of a type that was unexpected
     * @param string $message
     * @param int $code
     * @param \Throwable $previous
     */
    public function __construct($unexpectedValue, string $message = "", int $code = 0, \Throwable $previous = null)
    {
        $this->m_unexpectedValue;
    }


    # Get the unexpected value which caused the exception.
    public function getUnexpectedValue() { return $this->m_unexpectedValue; }
}

