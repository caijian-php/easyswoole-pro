<?php

date_default_timezone_set('Asia/Shanghai');
define('APP_COMMAND_DIR', EASYSWOOLE_ROOT.'/App/Command');

\App\Command\Loader::init();

require EASYSWOOLE_ROOT.'/WorkStartEvent.php';
