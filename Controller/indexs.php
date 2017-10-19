<?php
class indexs extends SlimvcController{
    function IndexAction()
    {
        $this->outputJson($this->model("index_model")->showTable("servos"));
        $this->helper("helper_test")->test();
    }
    function test()
    {
        echo "hellow";
    }
}