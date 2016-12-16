<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Produce extends CI_Controller {

    public function index()
    {
        if (($_SERVER['PHP_AUTH_USER'] == 'belstar') && ($_SERVER['PHP_AUTH_PW'] == '20161122')) {
            $this->load->library('xmlrpc');
            $this->load->library('xmlrpcs');

            $config['functions']['get_config'] = array('function' => 'Produce.get_config');
            $config['functions']['modify_config'] = array('function' => 'Produce.modify_config');


            $this->xmlrpcs->initialize($config);
            $this->xmlrpcs->serve();
        } 
/*
        else {
            header("WWW-Authenticate: Basic realm=\"My Private Area\"");
            header("HTTP/1.0 401 Unauthorized");
            print "You need valid credentials to get access!\n";
            exit;
        }
*/
    }
    public function set_password($request){
        $this->load->model('produce_m');
        $paras = $request->output_parameters();
        $result=$this->produce_m->set_password($paras[0],$paras[1],$paras[2]);
        $response = array(htmlspecialchars(json_encode($result)));
        return $this->xmlrpc->send_response($response);
    }


    public function migrate()
    {
        $this->load->library('migration');
        if ($this->migration->current() === false){
            show_error($this->migration->error_string());
        }else{
            echo '数据库迁移成功';
        }
    }
    
}
