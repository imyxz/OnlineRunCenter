<?php
class indexs extends SlimvcControllerCli{
    function IndexAction()
    {
        var_dump(self::$cliArg);


    }
    function test()
    {
        var_dump(self::$cliArg);
        var_dump($this);
    }
}