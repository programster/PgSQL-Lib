<?php

/*
 *
 */

class CreateTestTables implements \Programster\PgsqlMigrations\MigrationInterface
{
    public function up($dbConn) : void
    {
        $createUuidTableQuery =
            'CREATE TABLE "user" (
                "id" uuid NOT NULL,
                "name" varchar(255) NOT NULL,
                "email" varchar(255) NOT NULL,
                PRIMARY KEY (id)
            );';

        $createUuidTableResult = pg_query($dbConn, $createUuidTableQuery);

        if ($createUuidTableResult === FALSE)
        {
            throw new \Exception("Failed to create the user table.");
        }
    }


    public function down($mysqliConn) : void
    {

    }
}

