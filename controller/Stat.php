<?php

class C_Stat extends Controller{

    public function index(){
        $stat['app']    = APP_NAME;
        $stat['server'] = Server::getServerType();
        $stat['php_version']    = phpversion();
        $stat['swoole_version'] = swoole_version();
        $stat['masterPID'] = Server::getInstance()->master_pid;

        $ports = array(Server::getInstance()->ports)[0];
        $ports_arr = [];
        foreach($ports as $port){
            $port = array($port);
            foreach($port as $p){
                $p = array($p)[0];
                unset($p->setting);
                $ports_arr[] = $p;
            }
        }

        $stat['ports']   = $ports_arr;
        $stat['config']  = Config::get();
        $stat['stats']   = Server::getInstance()->stats();
        $stat['setting'] = Server::getInstance()->setting;
        return JSON($stat);
    }

    public function ping(){
        return 'PONG';
    }

    public function process(){
        $cmd = "ps -ef | grep Swoole | grep -v grep | awk -F ' ' '{print $2\"-\"$8}'";
        exec($cmd, $retval, $execState);
        return JSON($retval);
    }
}