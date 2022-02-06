<?php
set_include_path(__DIR__);

class playbook
{

    private $conf = array();

    function __construct()
    {
        $args = $_SERVER['argv'];

        $filepath = isset($args[1]) ? $args[1] : __DIR__ . "/config.props";

        if (! file_exists($filepath)) {
            echo "config props file " . $filepath . " not exist" . PHP_EOL;
            exit();
        }

        $file = file_get_contents($filepath);

        $lines = explode("\n", $file);

        $group = null;
        foreach ($lines as $line) {

            $line = trim($line);

            $arr = explode("=", $line);

            if (count($arr) == 2) {
                $key = trim($arr[0]);
                $val = trim($arr[1]);

                if ($key == "group") {
                    $group = $val;
                    $this->conf['group'][$group] = array();
                } else {
                    if ($group) {

                        if ($key == "host") {
                            $this->conf['group'][$group]['host'] = explode(",", $val);
                        } else if ($key == "script") {
                            $this->conf['group'][$group]["script"] = $val;
                        }
                    }
                }
            }
        }
    }

    function execute()
    {
        $groups = isset($this->conf['group']) ? $this->conf['group'] : false;

        if ($groups) {
            foreach ($groups as $key => $group) {

                $hosts = isset($group['host']) ? $group['host'] : false;
                $script = isset($group['script']) ? $group['script'] : false;
                if ($hosts && $script && file_exists($script)) {
                    foreach ($hosts as $host) {
                        $cmd = "ssh " . $host . " 'bash -s' < " . $script;
                        echo $cmd . PHP_EOL;
                        echo shell_exec($cmd);
                    }
                } else {
                    echo "host/script not found for group " . $key . PHP_EOL;
                }
            }
        } else {
            echo "groups not found" . PHP_EOL;
        }
    }
}

$playbook = new playbook();
$playbook->execute();
?>