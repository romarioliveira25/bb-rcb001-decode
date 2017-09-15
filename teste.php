<?php

require_once('RetornoRCB001.php');

//header('Content-Type: application/json');

$retorno = new RetornoRCB001('retorno.ret');

$retorno->process();
