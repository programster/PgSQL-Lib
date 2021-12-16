<?php


class TestBatchInsert extends AbstractTest
{
    public function __construct()
    {
        $db = ConnectionHandler::getDb();
        $query = "TRUNCATE {$db->escapeIdentifier("user")}";
        $result = $db->query($query);

        if ($result === FALSE)
        {
            throw new \Exception("Failed to empty the test table.");
        }
    }


    public function getDescription(): string
    {
        return "Test that batch inserting data works..";
    }


    public function run()
    {
        $users = array(
            [
                'id' => '952036f3-3486-4630-9889-9f46b50a54e5',
                'email' => 'user1@gmail.com',
                'name' => 'user1',
            ],
            [
                'id' => '952036f3-3559-4396-9352-81f7dc5a978a',
                'email' => 'user2@gmail.com',
                'name' => 'user2',
            ]
        );

        $db = ConnectionHandler::getDb();
        $batchInsertQuery = $db->generateBatchInsertQuery("user", $users);
        $result = $db->query($batchInsertQuery);

        $wherePairs = [
            'email' => ['user1@gmail.com', 'user2@gmail.com'],
        ];

        $selectQuery = $db->generateSelectWhereQuery(
            "user",
            $wherePairs,
            Programster\PgsqlLib\Conjunction::createOr()
        );

        $result = $db->query($selectQuery);

        if (pg_num_rows($result) !== 2)
        {
            $this->m_errorMessages[] = "Did not have the expected number of user records";
            $this->m_passed = false;
        }
        else
        {
            $this->m_passed = true;
        }

        $db->query("DELETE FROM {$db->escapeIdentifier("user")}");
    }
}

