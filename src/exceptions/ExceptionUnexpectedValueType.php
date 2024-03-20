<?php

/*
 * Exception to throw if we encounter an unexpected value type that we aren't sure how to process.
 */


namespace Programster\PgsqlLib\Exceptions;


use Throwable;


class ExceptionUnexpectedValueType extends \Exception
{
    private mixed $m_unexpectedValue;


    /**
     * Create a new unexpected value type exception.
     * @param $unexpectedValue - the value of a type that was unexpected
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($unexpectedValue, string $message = "", int $code = 0, Throwable $previous = null)
    {
        $this->m_unexpectedValue = $unexpectedValue;

        if ($message === "")
        {
            $message = "Unexpected value type: " . print_r($unexpectedValue, true);
        }

        parent::__construct($message, $code, $previous);
    }


    # Get the unexpected value which caused the exception.
    public function getUnexpectedValue() : mixed { return $this->m_unexpectedValue; }
}

