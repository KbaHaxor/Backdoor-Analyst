<?php
require_once('config/config.php');
require_once('class/log.class.php');

if(isset($_POST['app']) && $_POST['app'] != '' && isset($_POST['level']) && $_POST['level'] != '' && isset($_POST['grep']) && $_POST['grep'] != '')
{
    $insert = new log;
    $insert->setApplication($_POST['app']);
    $insert->setWarning($_POST['level']);
    $insert->setSuspicious($_POST['grep']);
    if(isset($_POST['word']) && $_POST['word'] != '')
        $insert->setWord($_POST['word']);
    if(isset($_POST['word2']) && $_POST['word2'] != '')
        $insert->setWord2($_POST['word2']);
    if(isset($_POST['source']) && $_POST['source'] != '')
        $insert->setSource($_POST['source']);
    $insert->insertLog();
}