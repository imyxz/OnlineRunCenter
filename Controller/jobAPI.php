<?php
/**
 * User: imyxz
 * Date: 2017-10-02
 * Time: 14:40
 * Github: https://github.com/imyxz/
 */
class jobAPI extends SlimvcController
{

    function newJob()
    {
        $return=array();
        try {
            /** @var worker_model $worker_model */
            $worker_model=$this->model("worker_model");
            /** @var job_model $job_model */
            $job_model=$this->model("job_model");
            $json=$this->getRequestJson();
            $source_code=$json['source_code'];
            $stdin=$json['program_stdin'];
            $code_type=intval($json['code_type']);
            if(!$source_code)   $source_code="";
            if(!$stdin) $stdin="";
            if( strlen($source_code)>64*1024)
                throw new Exception("source code must be no longer than 64K");
            if( strlen($stdin)>64*1024)
                throw new Exception("program input must be no longer than 64K");

            if(!in_array($code_type,array(0,1,2,3,4,5)))
                throw new Exception("no such type");
            $worker_id=$worker_model->getMostFreeWorker()['worker_id'];
            if(!$worker_id) throw new Exception("system busy!");
            $job_id=$job_model->newJob($source_code,$stdin,$code_type);
            if(!$job_id)    throw new Exception("system error!");
            $worker_model->assignWorkerJob($worker_id,$job_id);
            $worker_model->addWorkerWorkingJob($worker_id,1);
            $job_model->changeJobStatus($job_id,1);

            $return['job_id']=$job_id;
            $return['status'] = 0;
            $this->outputJson($return);

        } catch (Exception $e) {
            $return['status'] = 1;
            $return['err_msg'] = $e->getMessage();
            $this->outputJson($return);

        }
    }
    function getJobResult()
    {
        $job_id=intval($_GET['job_id']);
        try {
            /** @var worker_model $worker_model */
            $worker_model=$this->model("worker_model");
            /** @var job_model $job_model */
            $job_model=$this->model("job_model");
            $job_info=$job_model->getJobInfo($job_id);
            if(!$job_info)  throw new Exception("No such job");
            $return['job_status']=$job_info['job_status'];
            switch($job_info['job_status'])
            {
                case 0:
                    $return['info']="In queue";
                    break;
                case 1:
                    $return['info']="In queue";
                    break;
                case 2:
                    $return['info']="Running";
                    break;
                case 3:
                    $return['info']="Success";
                    $return['job_info']=array(
                        "program_stdout"=>$job_info["program_stdout"],
                        "program_stderr"=>$job_info["program_stderr"],
                        "compile_error"=>$job_info["compile_error"],
                        "compile_state"=>$job_info["compile_state"],
                        "run_state"=>$job_info["run_state"],
                        "time_usage"=>$job_info["time_usage"],
                        "mem_usage"=>$job_info["mem_usage"]);
            }
            $return['status'] = 0;
            $this->outputJson($return);

        } catch (Exception $e) {
            $return['status'] = 1;
            $return['err_msg'] = $e->getMessage();
            $this->outputJson($return);

        }
    }

}