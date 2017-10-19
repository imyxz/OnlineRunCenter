<?php
/**
 * User: imyxz
 * Date: 2017-10-02
 * Time: 15:06
 * Github: https://github.com/imyxz/
 */
class worker_model extends SlimvcModel
{
    function getWorkerInfoByWorkerID($worker_id)
    {
        return $this->queryStmt("select * from worker_info where worker_id=?",
            "i",
            $worker_id)->row();
    }
    function getWorkerJob($worker_id)
    {
        return $this->queryStmt("select job_id from run_job where worker_id=? and job_status=1 order by job_id desc limit 10",
            "i",
            $worker_id)->all();
    }
    function assignWorkerJob($worker_id,$job_id)
    {
        return $this->queryStmt("update run_job set worker_id=? where job_id=?",
            "ii",
            $worker_id,
            $job_id);
    }
    function addWorkerWorkingJob($worker_id,$how_much)
    {
        return $this->queryStmt("update worker_info set working_job=working_job+(?) where worker_id=?",
            "ii",
            $how_much,
            $worker_id);
    }
    function getMostFreeWorker()
    {
        return $this->query("select worker_id from worker_info where is_enable=true order by worker_id asc limit 1")->row();
    }
}