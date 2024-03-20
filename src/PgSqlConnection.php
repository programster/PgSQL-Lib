<?php

/*
 * A class to wrap around a postgresql connection, primarily because we
 * can't type-hint the postgresql resource like we can do with a \mysqli connection
 *
 * This should become redundant as we move onto PHP 8.1 which will make the
 * PgSql\Connection object a return type of pg_connect:
 * https://www.php.net/manual/en/class.pgsql-connection.php
 * https://www.php.net/manual/en/function.pg-connect.php
 */

declare(strict_types = 1);

namespace Programster\PgsqlLib;


use PgSql\Result;
use Programster\PgsqlLib\Exceptions\ExceptionNoData;
use Programster\PgsqlLib\Exceptions\ExceptionQueryError;
use Programster\PgsqlLib\Exceptions\ExceptionUnexpectedValueType;


class PgSqlConnection
{
    private \PgSql\Connection $m_resource; # the underlying pgsql connection resource.


    /**
     * Create a PostgreSql connection resource wrapper.
     * @throws Exceptions\ExceptionConnectionError
     */
    public function __construct(\PgSql\Connection $pgsqlResource)
    {
        $status = pg_connection_status($pgsqlResource);

        if ($status !== PGSQL_CONNECTION_OK)
        {
            throw new Exceptions\ExceptionConnectionError("Resource provided is not connected to the PostgreSql database.");
        }

        $this->m_resource = $pgsqlResource;
    }


    /**
     * Create a new PostgreSQL database connection
     * @param string $host - the IP or FQDN of where the postgreSQL database is hosted.
     * @param string $dbName - the name of the database.
     * @param string $user - the user to connect with
     * @param string $password - the password for that user.
     * @param int $port - the port to connect on (defaults to postgresql default port)
     * @param bool $use_utf8 - whether to use UTF8 encoding (defaults to true)
     * @param bool $forceNew - whether to set connection type to PGSQL_CONNECT_FORCE_NEW, which if passed, then a new
     * connection is created, even if the connection_string is identical to an existing connection.
     * @param bool $useAsync - Whether to set PGSQL_CONNECT_ASYNC. If set then the connection is established
     * asynchronously. The state of the connection can then be checked via pg_connect_poll() or pg_connection_status().
     * @return PgSqlConnection - the connection to the PostgreSQL database
     * @throws Exceptions\ExceptionConnectionError - if there was an issue connecting to the database.
     */
    public static function create(
        string $host,
        string $dbName,
        string $user,
        string $password,
        int $port = 5432,
        bool $use_utf8 = true,
        bool $forceNew = false,
        bool $useAsync = false
    ) : PgSqlConnection
    {
        $connString =
            "host=" . $host
            . " dbname=" . $dbName
            . " user=" . $user
            . " password=" . $password
            . " port=" . $port;

        if ($use_utf8)
        {
            $connString .= " options='--client_encoding=UTF8'";
        }

        $connectionOptions = 0;

        if ($forceNew)
        {
            $connectionOptions = $connectionOptions | PGSQL_CONNECT_FORCE_NEW;
        }

        if ($useAsync)
        {
            $connectionOptions = $connectionOptions | PGSQL_CONNECT_ASYNC;
        }

        $connection = pg_connect($connString, $connectionOptions);

        if ($connection == false)
        {
            throw new Exceptions\ExceptionConnectionError("Failed to initialize database connection.");
        }

        return new PgSqlConnection($connection);
    }


    /**
     * An alias for PgsqlLib::generateQueryPairs
     * @param array $pairs - the name/value pairs to escape.
     * @param bool $escapeValues
     * @return string
     */
    public function generateQueryPairs(array $pairs, bool $escapeValues = true) : string
    {
        return PgsqlLib::generateQueryPairs($this->getResource(), $pairs, $escapeValues);
    }


    /**
     * Executes a pg_query call on the underlying connection resource.
     * @param string $query - the query to execute.
     * @param bool $throwExceptionOnError - whether to raise an exception if there was an issue executing the query.
     * @return false|Result - the result of the query, or false if there was an issue and $throwExceptionOnError is set
     * to false.
     * @throws ExceptionQueryError - if $throwExceptionOnError is set to true, and there was an issue
     */
    public function query(string $query, bool $throwExceptionOnError = true) : false|Result
    {
        $result = pg_query($this->getResource(), $query);

        if ($result === false && $throwExceptionOnError)
        {
            throw new ExceptionQueryError(pg_last_error($this->getResource()), $query);
        }

        return $result;
    }


    public function escapeIdentifier(string $nameOfTableOrColumn) : string
    {
        return PgsqlLib::escapeidentifier($this->getResource(), $nameOfTableOrColumn);
    }


    public function escapeIdentifiers(array $identifiers) : array
    {
        return PgsqlLib::escapeidentifiers($this->getResource(), $identifiers);
    }


    public function escapeValues(array $inputs) : array
    {
        return PgsqlLib::escapeValues($this->getResource(), $inputs);
    }


    /**
     * Escape a value.
     * @throws Exceptions\ExceptionUnexpectedValueType - if the input variable is a type the package
     * does not recognize, and thus cannot handle.
     */
    public function escapeValue(mixed $input) : mixed
    {
        return PgsqlLib::escapeValue($this->getResource(), $input);
    }


    /**
     * Creates a batch insert query for inserting lots of data in one go.
     * @param string $tableName - the name of the table to insert into.
     * @param array $rows - the rows of data to insert in name/value pairs. Every row must contain the same set of keys,
     * but those keys don't need to be in the same order.
     * @return string - the query to execute to batch insert the data.
     * @throws ExceptionUnexpectedValueType - if there was an issue performing the escaping.
     * @throws ExceptionNoData - if no data was provided for insertion.
     */
    public function generateBatchInsertQuery(string $tableName, array $rows) : string
    {
        return PgsqlLib::generateBatchInsertQuery($this->getResource(), $tableName, $rows);
    }


    /**
     * Helper function that generates the raw SQL string to send to the database in order to
     * load objects that have any/all (depending on $conjunction) of the specified attributes.
     *
     * @param string $tableName - the name of the table to select from.
     * @param array $wherePairs - column-name/value pairs of attributes the objects must have to
     *                           be loaded.
     * @param Conjunction $conjunction - 'AND' or 'OR' which changes whether the object needs all or
     *                                   any of the specified attributes in order to be loaded.
     * @return string - the raw sql string to send to the database.
     * @throws ExceptionUnexpectedValueType
     */
    public function generateSelectWhereQuery(string $tableName, array $wherePairs, Conjunction $conjunction) : string
    {
        return PgsqlLib::generateSelectWhereQuery($this->getResource(), $tableName, $wherePairs, $conjunction);
    }


    # Accessors
    public function getResource() : \PgSql\Connection { return $this->m_resource; }
}

