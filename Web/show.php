<?php
require_once('config/config.php');
require_once('class/log.class.php');
$log = new log;
?>
<header>
    <div class="logo">Analyst</div>
    <div class="info">
        <span class="menu"><i class="fa fa-exclamation-triangle"></i> <?php $log->getWarning(); ?></span>
        <span class="menu"><i class="fa fa-eye "></i> <?php $log->getApplication(); ?></div></span>
</header>
<div class="container">
    <h5 class="ui header">Dangerous</h5>
    <div class="dangerous">
        <table class="ui very basic table">
            <tbody>
             <?php
                $log->show_log_dangerous();
              ?>
            </tbody>
        </table>
    </div>
    <h5 class="ui header">Suspicious</h5>
    <div class="suspicious">
        <table class="ui very basic table">
            <tbody>
             <?php
                $log->show_log_suspicious();
              ?>
            </tbody>
        </table>
    </div>
<!--    <h5 class="ui header">Basic</h5>
    <div class="basic">
        <table class="ui very basic table">
            <tbody>
             <?php
                $log->show_log_basic();
              ?>
            </tbody>
        </table>
    </div>-->
</div>