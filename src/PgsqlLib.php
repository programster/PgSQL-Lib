<?php

/*
 * A library to help with using the pgsql connection object:
 * https://www.php.net/manual/en/book.pgsql.php
 */

declare(strict_types = 1);

namespace Programster\PgsqlLib;

class PgsqlLib
{
    /**
     * Generates the SET part of a mysql query with the provided name/value
     * pairs provided
     * @param $conn - the postgresql connection resource
     *
     * @param array pairs - assoc array of name/value pairs to go in mysql
     *
     * @param bool $escapeValues - (optional) set to false to disable escaping of values if you have already taken
     * care of this
     *
     * @param bool $escapeIdentifiers - (optional) set to false to disable escaping of identifiers if you have already
     * taken care of this.
     * @return string - the generated query string that can be appended.
     */
    public static function generateQueryPairs(
        $conn,
        array $pairs,
        bool $escapeValues = true,
        bool $escapeIdentifiers = true
    ) : string
    {
        $query = '';

        if ($escapeValues)
        {
            $pairs = PgsqlLib::escapeValues($conn, $pairs);
        }

        foreach ($pairs as $name => $value)
        {
            if ($escapeIdentifiers)
            {
                $escapedIdentifier = PgsqlLib::escapeIdentifier($conn, $name);
            }
            else
            {
                $escapedIdentifier = $name;
            }

            if ($value === null)
            {
                $query .= "{$escapedIdentifier} = NULL, ";
            }
            else
            {
                $query .= "{$escapedIdentifier} = {$value}, ";
            }
        }

        $query = substr($query, 0, -2); # remove the last comma.
        return $query;
    }


    /**
     * Escape the provided values, using the provided connection.
     * @param
     * @param $conn - the PostgreSQL connection resource
     * @param array $inputs - the input values to escape.
     * @return array - the escaped values.
     */
    public static function escapeValues($conn, array $inputs) : array
    {
        $escapedValues = array();

        foreach ($inputs as $key => $value)
        {
            $escapedValues[$key] = PgsqlLib::escapeValue($conn, $value);
        }

        return $escapedValues;
    }


    /**
     * Escape an identifier, such as the name of a table, or a column.
     * @param $conn - the PostgreSQL connection resource
     * @param string $nameOfTableOrColumn
     * @return type
     */
    public static function escapeIdentifier($conn, string $nameOfTableOrColumn)
    {
        return pg_escape_identifier($conn, $nameOfTableOrColumn);
    }


    /**
     * Escape the array of string identifiers provided.
     * @param $conn - the PostgreSQL connection resource
     * @param array $namesOfTablesOrColumns - the list of table/column names to escape.
     * @return array - the escaped identifiers.
     */
    public static function escapeIdentifiers($conn, array $namesOfTablesOrColumns) : array
    {
        $escapedIdentifiers = [];

        foreach ($namesOfTablesOrColumns as $identifier)
        {
            $escapedIdentifiers[] = pg_escape_identifier($conn, $identifier);
        }

        return $escapedIdentifiers;
    }


    /**
     * Escape a single value,
     * @param $conn - the PostgreSQL connection resource
     * @param type $value
     * @throws ExceptionUnexpectedValueType
     * @return mixed - the escaped value.
     */
    public static function escapeValue($conn, $value) : mixed
    {
        if (is_string($value))
        {
            $escapedValue = pg_escape_literal($conn, $value);
        }
        elseif (is_numeric($value))
        {
            $escapedValue = $value;
        }
        elseif (is_bool($value))
        {
            $escapedValue = ($value) ? "TRUE" : "FALSE";
        }
        elseif ($value === null)
        {
            // dont need to do anything for a null value
            $escapedValue = null;
        }
        else
        {
            $msg =
                "Unexpected type when escaping values. Please consider reporting this  " .
                "issue if appropriate. In the meantime, try escaping values manually and set " .
                "escapeValues to false.";

            throw new Exceptions\ExceptionUnexpectedValueType($value, $msg);
        }

        return $escapedValue;
    }


    /**
     * Generate the "where" part of a query based on name/value pairs and the provided conjunction
     * @param $conn - the postgresql connection resource
     * @param array $wherePairs - column/value pairs for where clause. Value may or may not be an
     *                            array list of values for WHERE IN().
     * @param Conjunction $conjunction - the conjunction to use in the where clause.
     * @return string - the where clause of a query such as "WHERE `id`='3'"
     */
    public static function generateWhereClause(
        $conn,
        array $wherePairs,
        Conjunction $conjunction
    ) : string
    {
        $whereClause = "";
        $whereStrings = array();

        foreach ($wherePairs as $attribute => $searchValue)
        {
            $whereString = PgsqlLib::escapeIdentifier($conn, $attribute);

            if (is_array($searchValue))
            {
                if (count($searchValue) === 0)
                {
                    $whereString = " FALSE";
                }
                else
                {
                    $escapedSearchValues = PgsqlLib::escapeValues($conn, $searchValue);
                    $whereString .= " IN(" . implode(",", $escapedSearchValues)  . ")";
                }
            }
            else
            {
                $whereString .= " = " . PgsqlLib::escapeValue($conn, $searchValue);
            }

            $whereStrings[] = $whereString;
        }

        if (count($whereStrings) > 0)
        {
            $whereClause = "WHERE " . implode(" {$conjunction} ", $whereStrings);
        }

        return $whereClause;
    }


    /**
     * Helper function that generates the raw SQL string to send to the database in order to
     * load objects that have any/all (depending on $conjunction) of the specified attributes.
     * @param $conn - the postgresql connection resource
     * @param string $tableName - the name of the table to select from.
     * @param array $wherePairs - column-name/value pairs of attributes the objects must have to
     *                           be loaded.
     * @param Conjunction $conjunction - 'AND' or 'OR' which changes whether the object needs all or
     *                                   any of the specified attributes in order to be loaded.
     * @return string - the raw sql string to send to the database.
     * @throws \Exception - invalid $conjunction specified that was not 'OR' or 'AND'
     */
    public static function generateSelectWhereQuery(
        $conn,
        string $tableName,
        array $wherePairs,
        Conjunction $conjunction
    ) : string
    {
        $escapedTableName = PgsqlLib::escapeIdentifier($conn, $tableName);
        $query = "SELECT * FROM {$escapedTableName} " . PgsqlLib::generateWhereClause($conn, $wherePairs, $conjunction);
        return $query;
    }


    /**
     * Helper function that generates the raw SQL string to send to the database in order to
     * delete objects that have any/all (depending on $conjunction) of the specified attributes.
     * @param $conn - the postgresql connection resource
     * @param array $wherePairs - column-name/value pairs of attributes the objects must have to
     *                           be deleted.
     * @param Conjunction $conjunction - 'AND' or 'OR' which changes whether the object needs all or
     *                              any of the specified attributes in order to be loaded.
     * @return string - the raw sql string to send to the database.
     * @throws \Exception - invalid $conjunction specified that was not 'OR' or 'AND'
     */
    public static function generateDeleteWhereQuery(
        $conn,
        string $tableName,
        array $wherePairs,
        Conjunction $conjunction
    ) : string
    {
        $escapedTableName = PgsqlLib::escapeIdentifier($conn, $tableName);

        $query =
            "DELETE FROM {$escapedTableName} " .
            PgsqlLib::generateWhereClause($conn, $tableName, $wherePairs, $conjunction);

        return $query;
    }


    /**
     * Creates a batch insert query for inserting lots of data in one go.
     * @param $conn - the postgresql connection resource
     * @param string $tableName - the name of the table to insert into.
     * @param array $rows - the rows of data to insert in name/value pairs. Every row must contain the same set of keys,
     * but those keys don't need to be in the same order.
     * @return string - the query to execute to batch insert the data.
     * @throws \Exception
     */
    public static function generateBatchInsertQuery($conn, string $tableName, array $rows) : string
    {
        $valueSets = array();
        $firstRow = true;
        $escapedColumnNames = array();

        if (count($rows) === 0)
        {
            throw new \Exception("No data to insert.");
        }

        foreach ($rows as $row)
        {
            ksort($row); // always sort by key so all rows are consisten on insert.

            if ($firstRow)
            {
                $columnNames = array_keys($row);
                $escapedColumnNames = PgsqlLib::escapeidentifiers($conn, $columnNames);
                $firstRow = false;
            }

            $rowValues = array_values($row);
            $escapedRowValues = PgsqlLib::escapeValues($conn, $rowValues);
            $rowValuesString = "";

            foreach ($escapedRowValues as $escapedValue)
            {
                if ($escapedValue === null)
                {
                    $rowValuesString .= "NULL, ";
                }
                else
                {
                    $rowValuesString .= $escapedValue . ", ";
                }
            }

            $valueSets[] = "(" . \Safe\substr($rowValuesString, 0, strlen($rowValuesString) - 2) . ")";
        }

        $escapedTableName = PgsqlLib::escapeIdentifier($conn, $tableName);

        $query =
            "INSERT INTO {$escapedTableName}"
            . " (" . implode(",", $escapedColumnNames) . ")"
            . " VALUES " . implode(",", $valueSets)
            . ";";

        return $query;
    }
}