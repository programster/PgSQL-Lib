<?php

/*
 * An "enum" to represent a conjunction. I can't wait until PHP gets native enum support in 8.1
 */

namespace Programster\PgsqlLib;


class Conjunction
{
    private string $m_conjunction;


    private function __construct(string $conjunction)
    {
        $this->m_conjunction = $conjunction;
    }


    public static function createAnd() : Conjunction
    {
        return new Conjunction("AND");
    }


    public static function createOr() : Conjunction
    {
        return new Conjunction("OR");
    }


    public function __toString() : string
    {
        return $this->m_conjunction;
    }
}
