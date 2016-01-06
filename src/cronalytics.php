<?php
/**
 * Cronalytics PHP CLI intergration.
 *
 * a command line interface to cronalytics.io
 */


require_once __DIR__ . '/../vendor/autoload.php';
use Cronalytics\ScriptRunner;

/**
 * @return \Commando\Command
 */
function buildCommandLineOptions()
{
    $cmd = new Commando\Command();

    $cmd->option()
        ->require()
        ->description('The Public Hash for the cron that is being run. ');

    $cmd->option()
        ->require()
        ->description('The command to run');

    $cmd->option('r')
        ->aka('send-result')
        ->boolean()
        ->description('Send the std out of the passed in command to Cronalytics');

    $cmd->option('x')
        ->aka('debug')
        ->boolean();

    $cmd->option('v')
        ->aka('verbose')
        ->boolean()
        ->description('Verbose output');

    $cmd->option('vvv')
        ->aka('veryveryverbose')
        ->boolean()
        ->description('Very Very Verbose output');

    $cmd->option('s')
        ->aka('silent')
        ->boolean()
        ->description('No output to std out or std err. even the script result is not printed.');

    return $cmd;
}

/**
 * Do the work
 */
$cmd = buildCommandLineOptions();

// make the passed in values easier to use
$hash = $cmd[0];
$script = $cmd[1];

$sr = new ScriptRunner($hash, $cmd->getFlagValues());
$sr->startTrigger();
$output = $sr->runScript($script);
$sr->endTrigger();

// Decide to output the result or not
if (!$cmd['silent']) {
    echo $output['stdout'];

    $fe = fopen('php://stderr', 'w');
    fwrite($fe, $output['stderr']);
    fclose($fe);
}

// Exit with same return code as user script.
exit($output['status']['exitcode']);
