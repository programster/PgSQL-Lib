<?php

require_once(__DIR__ . '/settings.php');
require_once(__DIR__ . '/../vendor/autoload.php');

$autoloader = new \iRAP\Autoloader\Autoloader(array(
    __DIR__,
    __DIR__ . '/tests',
    __DIR__ . '/models',
));

$migrationManager = new Programster\PgsqlMigrations\MigrationManager(
    __DIR__ . '/migrations',
    ConnectionHandler::getDb()->getResource()
);

$migrationManager->migrate();

$testFiles = Programster\CoreLibs\Filesystem::getDirContents(__DIR__ . '/tests');
$testResults = array();
$testOutput = array();
$testExceptoins = array();

foreach ($testFiles as $testFile)
{
    $baseName = basename($testFile);
    $testName = str_replace(".php", "", $baseName);
    $boldGreen = "\033[1;32m";
    $boldRed = "\033[1;31m";
    $reset = "\033[0m";
    $boldCyan = "\033[1;36m";
    $coloredPassedMessage = "{$boldGreen}PASSED{$reset}";
    $coloredFailedMessage = "{$boldRed}FAILED{$reset}";

    ob_start();
    try
    {
        /* @var $test AbstractTest */
        $test = new $testName();
        $test->run();

        if ($test->getPassed())
        {
            $testResults[$testFile] = "passed";
            print "{$boldCyan}{$testName}{$reset}: {$coloredPassedMessage}" . PHP_EOL;
        }
        else
        {
            $testResults[$testFile] = "failed";
            print "{$boldCyan}{$testName}{$reset}: {$coloredFailedMessage} - " . implode(PHP_EOL, $test->getErrorMessages()) . PHP_EOL;
        }
    }
    catch (Exception $ex)
    {
        $testResults[$testFile] = "failed";
        $testExceptoins[$testFile] = $ex;
        print "{$boldCyan}{$testName}{$reset}: {$coloredFailedMessage} - {$ex->getMessage()}" . PHP_EOL;
    }

    $testOutput[$testFile] = ob_get_clean();
}

arsort($testResults);

foreach ($testResults as $testName => $passedOrFailed)
{
    if ($passedOrFailed === "passed")
    {
        print "{$boldCyan}{$testName}{$reset}: {$coloredPassedMessage}" . PHP_EOL;
    }
    else
    {
        print "{$boldCyan}{$testName}{$reset}: {$coloredFailedMessage} - {$testExceptoins[$testName]->getMessage()}" . PHP_EOL;
    }
}