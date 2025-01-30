<?php

require_once './helpers/cacheMakerHelper.php';
require_once './slow_process.php';

$process = new SlowProcess();

$cache = new CacheMakerHelper('relatorio_teste', 'cache/relatorio_teste.db', 1);
// $data = json_encode($process->getData('123', 'Teste', 23, ['test' => 'test']));
$data = json_encode($cache->execute([$process, 'getData'], ['123', 'Teste', 2113, ['test' => 'te2st']]));
echo $data;
