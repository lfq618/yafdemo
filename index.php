<?php
define('APPLICATION_PATH', dirname(__FILE__));

xhprof_enable(XHPROF_FLAGS_NO_BUILTINS | XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY, array(
       'ignored_functions' => array(
	        'call_user_func',
            'call_user_func_array',
        )
));

$application = new Yaf_Application( APPLICATION_PATH . "/conf/application.ini");

$application->bootstrap()->run();

$xhprof_data = xhprof_disable();

include_once '/home/wwwroot/effect/xhprof_lib/utils/xhprof_lib.php';
include_once '/home/wwwroot/effect/xhprof_lib/utils/xhprof_runs.php';

$xhprof_runs = new XHProfRuns_Default();
$run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_testing");
echo "http://effect.demo//xhprof_html/index.php?run={$run_id}&source=xhprof_testing\n";
?>
