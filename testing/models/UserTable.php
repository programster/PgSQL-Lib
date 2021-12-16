<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class UserTable extends Programster\PgsqlObjects\AbstractTable
{
    public function getDb() : \Programster\PgsqlObjects\PgSqlConnection
    {
        return ConnectionHandler::getDb();
    }

    public function getFieldsThatAllowNull(): array
    {
        return array();
    }


    public function getFieldsThatHaveDefaults(): array
    {
        return array();
    }


    public function getObjectClassName() : string
    {
        return 'UserRecord';
    }

    public function getTableName() : string
    {
        return 'user';
    }

    public function validateInputs(array $data): array
    {
        return $data;
    }
}

