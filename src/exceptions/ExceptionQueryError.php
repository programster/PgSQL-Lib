<?php

namespace Programster\PgsqlLib\Exceptions;


class ExceptionQueryError extends \Exception
{
    /**
     * Create an exception for a query not working.
     * @param string $errorMessage - the error message returned from pg_last_error
     * @param string $query - the query that was executed that had an issue.
     */
    public function __construct(private readonly string $errorMessage, private readonly string $query)
    {
        parent::__construct("There was an issue with your query: " . $errorMessage);
    }

    public function getQuery() : string { return $this->query; }
    public function getErrorMessage() : string { return $this->errorMessage; }
}