#!/usr/bin/php
<?php

use QuizProMaker\ApiClient;
use QuizProMaker\Executor;

require('./vendor/autoload.php');

$optionsSetting = [
    'action:',
    'id::',
    'test'
];

$options = getopt("", $optionsSetting);

$action     = $options['action'] ?? 'help';
$id         = $options['id'] ?? null;
$isTestMode = isset($options['test']);

if ($isTestMode) {
    ApiClient::$testMode = true;
}

$noIdActionList = [
    'list',
    'help',
    'test-picture',
];

if (!in_array($action, $noIdActionList) && !$id) {
    echo 'id parameter required for action ' . $action . PHP_EOL . PHP_EOL;
    $action = 'help';
}

switch ($action) {
    case 'list'         : Executor::doList(); exit; break;
    case 'prepare'      : Executor::doPrepare($id); exit; break;
    case 'check-state'  : Executor::doCheckState($id); exit; break;
    case 'pictures'     : Executor::doPictures($id); exit; break;
    case 'upload'       : Executor::doPicturesUpload($id); exit; break;
    case 'create'       : Executor::doCreate($id); exit; break;
    case 'server'       : Executor::doServerCheck($id); exit; break;
    case 'test-picture' : Executor::doTestPicture(); exit; break;
    case 'help'         : doHelp(); exit; break;
}

function doHelp(): void {
    echo 'run as:' . PHP_EOL;
    echo './quizpromaker.php --action=<action> --id=<gameId> [--test]' . PHP_EOL;
    echo "\taction can be:" . PHP_EOL;
    echo "\t\tlist         - list local games" . PHP_EOL;
    echo "\t\tprepare      - make normal format from txt" . PHP_EOL;
    echo "\t\tcheck-state  - test questions and sync state" . PHP_EOL;
    echo "\t\tpictures     - make pictures" . PHP_EOL;
    echo "\t\tcreate       - create game on server" . PHP_EOL;
    echo "\t\tupload       - upload pictures" . PHP_EOL;
    echo "\t\tserver       - download gamestate from server and display information" . PHP_EOL;
    echo "\t\ttest-picture - create test picture in current folder" . PHP_EOL;
    echo "--test options do not launch any queries to server" . PHP_EOL;
    echo PHP_EOL;
    echo "Correct procedure:" . PHP_EOL;
    echo "\t1 - create folder <localGameId> in games" . PHP_EOL;
    echo "\t2 - create folder 'pictures' inside localGameId" . PHP_EOL;
    echo "\t3 - create questions.json inside localGameId, see format in game1" . PHP_EOL;
    echo "\t4 - launch 'pictures' to create pictures" . PHP_EOL;
    echo "\t5 - launch 'create' to create game on server" . PHP_EOL;
    echo "\t6 - launch 'server' to upload server ids from server" . PHP_EOL;
    echo "\t7 - launch 'upload' to upload pictures to server" . PHP_EOL;
    echo PHP_EOL;
    echo "You need to launch server every time after manual redo game on server" . PHP_EOL;
}
