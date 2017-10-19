<?php
include_once("enum.php");
class jobRunningStatus extends enum
{
    public $NOT_START=0;
    public $DISTRIBUTED_TO_WORKER=1;
    public $RUNNING_WAITING_RESULT=2;
    public $FINISH=3;
}