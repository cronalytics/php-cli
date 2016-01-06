<?php
namespace Cronalytics;

use Cronalytics\Utils\CronalyticsAPI;
use DateTime;

class ScriptRunner {
    private $cronHash = null;
    private $triggerHash = null;

    private $options = array();

    private $api = null;

    private $startResult = null;

    private $scriptResult = null;

    public function __construct($cronHash, $options = array())
    {
        $this->cronHash = $cronHash;

        $defaultOptions =  array(
            'x' => false,
            'vvv' => false
        );

        $this->options = array_merge($defaultOptions, $options);

        $this->api = new CronalyticsAPI($this->options);

        $this->vvv('Script Runner Started');
        $this->vvv('options:', $this->options);
        $this->vvv('cron hash:', $cronHash);
    }

    protected function vvv($message, $vars = 'random string that will never happen-29y3iuqhgifassf') {
        if ($this->options['vvv']) {
            echo "-- {$message}" . PHP_EOL;

            //to lazy to figure out a way to account for null, false vars that i will still want to show.
            if ($vars !== 'random string that will never happen-29y3iuqhgifassf') {
                var_dump($vars);
            }
        }
    }

    public function startTrigger() {
        $startTime = new DateTime();
        $this->vvv('Sending start trigger, start time:', $startTime->format(DateTime::ISO8601));
        $this->startResult = $this->api->startTrigger($this->cronHash, $startTime);

        if  ($this->startResult->success) {
            $this->vvv('start trigger success?', 'true');
            $this->triggerHash = $this->startResult->data->trigger->_id;
        }

        $this->vvv('result', $this->startResult);
    }

    public function endTrigger($sendResult = true, $sendSuccess = true) {
        $endTime = new DateTime();
        $this->vvv('sending End trigger, end time', $endTime->format(DateTime::ISO8601));

        $result = $sendResult ? $this->scriptResult['stdout'] : null;
        $isSuccess = null;
        if ($sendSuccess) {
            $isSuccess = $this->scriptResult['status']['exitcode'] === 0
                && empty($this->scriptResult['stderr']);
        }

        $this->vvv('calling end trigger', array($this->triggerHash, $endTime, $result, $isSuccess));
        $this->endResult = $this->api->endTrigger($this->triggerHash, $endTime, $result, $isSuccess);
        $this->vvv('end result', $this->endResult);
    }


    /**
     * Execute a command and return the output from stdout and std err.
     *
     * @param $script
     * @return array ['stdout', 'stderr', 'status']
     */
    public function runScript($script) {
        $descriptorspec = array(
            0 => array("pipe", "r"),  // stdin
            1 => array("pipe", "w"),  // stdout
            2 => array("pipe", "w"),  // stderr
        );
        $pipes = array();

        $process = proc_open($script, $descriptorspec, $pipes, dirname(__FILE__), null);

        $output = array();
        $output['stdout'] = trim(stream_get_contents($pipes[1]));
        fclose($pipes[1]);

        $output['stderr'] = trim(stream_get_contents($pipes[2]));
        fclose($pipes[2]);

        $output['status'] = proc_get_status($process);

        $this->scriptResult = $output;

        $this->vvv('Script result', $output);
        return $output;
    }

}

