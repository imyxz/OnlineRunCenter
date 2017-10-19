<?php
/**
 * User: imyxz
 * Date: 2017-10-02
 * Time: 14:40
 * Github: https://github.com/imyxz/
 */
class workerAPI extends SlimvcController
{
    private function getRequestHeaders() {
        $headers = array();
        foreach($_SERVER as $key => $value) {
            if (substr($key, 0, 5) <> 'HTTP_') {
                continue;
            }
            $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
            $headers[$header] = $value;
        }
        return $headers;
    }
    private function checkPrivilege()
    {
        /*$origin_headers=$this->getRequestHeaders();
        var_dump($origin_headers);
        $apikey="";
        $worker_id=0;
        $headers=array();
        foreach($origin_headers as &$one)
        {
            $two=explode(":",$one);
            if(sizeof($two)>=2);
            {
                $headers[trim($two[0])]=trim($two[1]);
            }
        }
        var_dump($headers);*/
        $headers=$this->getRequestHeaders();
        if(!isset($headers['X-Apikey']) || !isset($headers['X-Worker-Id']))
            exit;
        $apikey=$headers['X-Apikey'];
        $worker_id=intval($headers['X-Worker-Id']);
        /** @var worker_model $worker_model */
        $worker_model=$this->model("worker_model");
        $worker_info=$worker_model->getWorkerInfoByWorkerID($worker_id);
        if(!$worker_info || empty($apikey) || $apikey!=$worker_info['api_key'])
            exit;
        return $worker_id;
    }
    function getJobs()
    {
        $worker_id = $this->checkPrivilege();
        $return=array();
        try {
            /** @var worker_model $worker_model */
            $worker_model=$this->model("worker_model");
            /** @var job_model $job_model */
            $job_model=$this->model("job_model");
            $jobs=$worker_model->getWorkerJob($worker_id);
            $return['job_id']=array();
            foreach($jobs as $one)
            {
                $return['job_id'][]=$one['job_id'];
            }
            $return['status'] = 0;
            $this->outputJson($return);

        } catch (Exception $e) {
            $return['status'] = 1;
            $return['err_msg'] = $e->getMessage();
            $this->outputJson($return);

        }
    }
    function beginRunningJob()
    {
        $worker_id = $this->checkPrivilege();
        $return=array();
        try {
            /** @var worker_model $worker_model */
            $worker_model=$this->model("worker_model");
            /** @var job_model $job_model */
            $job_model=$this->model("job_model");
            $job_id=intval($_GET['job_id']);
            $job_info=$job_model->getJobInfo($job_id);
            if(!$job_info || $job_info['worker_id']!=$worker_id)
                throw new Exception("This is not your job");
            $job_model->changeJobStatus($job_id,2);
            $return['job_info']=$job_info;
            $return['status'] = 0;
            $this->outputJson($return);

        } catch (Exception $e) {
            $return['status'] = 1;
            $return['err_msg'] = $e->getMessage();
            $this->outputJson($return);

        }
    }
    function uploadJobResult()
    {
        $worker_id = $this->checkPrivilege();
        $return=array();
        try {
            /** @var worker_model $worker_model */
            $worker_model=$this->model("worker_model");
            /** @var job_model $job_model */
            $job_model=$this->model("job_model");
            $job_id=intval($_GET['job_id']);
            $job_info=$job_model->getJobInfo($job_id);
            if(!$job_info || $job_info['worker_id']!=$worker_id)
                throw new Exception("This is not your job");
            $json=$this->getRequestJson();
            $job_model->updateJobResult($job_id,
                $json['program_stdout'],
                $json['program_stderr'],
                $json['run_state'],
                $json['compile_error'],
                $json['compile_state'],
                $json['time_usage'],
                $json['mem_usage'],
                $json['time_info']);
            if($job_info['job_status']!=3)
                $worker_model->addWorkerWorkingJob($worker_id,-1);
            $job_model->changeJobStatus($job_id,3);

            $return['status'] = 0;
            $this->outputJson($return);

        } catch (Exception $e) {
            $return['status'] = 1;
            $return['err_msg'] = $e->getMessage();
            $this->outputJson($return);

        }
    }
}