<?php
/**
 * User: imyxz
 * Date: 2017-10-02
 * Time: 18:50
 * Github: https://github.com/imyxz/
 */
class job_model extends SlimvcModel
{
    function getJobInfo($job_id)
    {
        return $this->queryStmt("select * from run_job where job_id=?",
            "i",
            $job_id)->row();
    }
    function changeJobStatus($job_id,$job_status)
    {
        return $this->queryStmt("update run_job set job_status=? where job_id=?",
            "ii",
            $job_status,
            $job_id);
    }
    function updateJobResult($job_id,$stdout,$stderr,$run_state,$compile_error,
                            $compile_state,$time_suage,$mem_usage,$time_info)
    {
        return $this->queryStmt("update run_job set program_stdout=?,program_stderr=?,run_state=?,compile_error=?,
compile_state=?,time_usage=?,mem_usage=?,time_info=?,finish_time=now() where job_id=?",
            "ssisiiisi",
            $stdout,$stderr,$run_state,$compile_error,$compile_state,$time_suage,$mem_usage,$time_info,$job_id);
    }
    function newJob($source_code,$program_stdin,$code_type)
    {
        if(!$this->queryStmt("insert into run_job set source_code=?,program_stdin=?,code_type=?,submit_time=now()",
            "ssi",$source_code,$program_stdin,$code_type))
            return false;
        return $this->InsertId;
    }

}