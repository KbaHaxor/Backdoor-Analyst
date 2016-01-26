<?php
require_once('config/config.php');
require_once('class/log.class.php');
if(isset($_GET['id']) && $_GET['id'] != '')
{
    $log = new log;
    $log->checkId($_GET['id']);
}
?>
<header>
    <div class="logo">Analyst</div>
    <div class="info">
        <span class="menu"><i class="fa fa-exclamation-triangle"></i> <?php $log->getWarning(); ?></span>
        <span class="menu"><i class="fa fa-eye "></i> <?php $log->getApplication(); ?></div></span>
</header>
<div class="container">
    <h5 class="ui header">Suspect data</h5>
    <div class="dangerousid">
        <?php
        $log->showID($_GET['id']);
        ?>
    </div>
    <div class="back"><a href='index.php'><i class="fa fa-backward"></i></a></div>
</div>