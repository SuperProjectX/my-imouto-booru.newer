<?php
require dirname(__FILE__).'/config.php';

include_model('JobTask');
JobTask::execute_once();