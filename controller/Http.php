<?php

class C_Http extends Controller {

	private $m_user;
	private $m_news;
    
    function __construct(){
    	$this->m_user = $this->load('User');
        $this->m_news = $this->load('News');
    }

    public function log(){
        Logger::debug('This is a debug msg');
        Logger::info('This is an info msg');
        Logger::warn('This is a warn msg');
        Logger::error('This is an error msg');
        Logger::fatal('This is a fatal msg');
        Logger::log('This is a log msg');

        $level = Config::get('common', 'error_level');
        return 'Current error_level is => '.$level;
    }

    // 测试onError事件
    // 为了避免由于exception, error 导致worker 退出后客户端一直收不回复的问题
    // 使用 try...catch(Throwable) 来处理
	public function onError(){
        $result = $this->m_player->SelectOne();
        return 'Result is => '.$result;
	}

    // Ping and Pong
    public function ping(){
        return 'PONG';
    }

    // get Config with key
    public function configAndKey(){
        $redis_config = Config::get('redis');
        return JSON($redis_config).'<br />';

        $redis_host = Config::get('redis', 'host');
        return JSON('Host is '.$redis_host).'<br />';

        $redis_port = Config::get('redis', 'port');
        return JSON('Port is '.$redis_port);
        return JSON($redis_config);
    }

    // Get all users
    public function users(){
        $users = $this->m_user->SelectAll();
        return JSON($users);
    }

    public function limitUsers(){
        $news = $this->m_user->Limit()->Select();
        return JSON($news);
    }

    // MySQL 压力测试
    public function stress(){
        $max = 10000;
        $start_time = Logger::getMicrotime();
        for($i = 1; $i <= $max; $i++){
            $users = $this->m_user->Select();
        }
        $end_time = Logger::getMicrotime();
        $cost = $end_time - $start_time;
        return 'Time => '.$cost.', TPS => '.$max/$cost;
    }

    // SelectAll
    public function all(){
        $users = $this->m_user->SelectAll();
        return JSON($users);

        $news = $this->m_news->Select();
        return JSON($news);

        $one_news = $this->m_news->SelectOne();
        return JSON($one_news);
    }

    // Security
    public function security(){
        return JSON($this->request);
    }

    // Autoload
    public function rabbit(){
        $rabbit = new RabbitMQ();
        return 'A Rabbit is running happily now';
    }

    public function selectOne(){
        $news = $this->m_user->SelectOne();
        return JSON($news);
    }

    public function pagination(){
        return JSON($this->m_user->Limit()->Select());
    }
    
    // 测试 MySQL 自动断线重连
    public function reconnect(){
        $i = 1;
        $max = 1000;
        while($i <= $max){
            $user = $this->m_user->SelectOne();
            if(!$user){
                $msg = 'Stop reconnecting';
                Logger::log($msg);
                $retval = $this->response->write($msg);
                break;
            }else{
                $users = JSON($user);
            }

            $retval = $this->response->write($i.' => '.$users.'<br />');
            if(!$retval){
                break;
            }

            $where  = ['id' => 1035];
            $user = $this->m_user->Where($where)->SelectOne();
            $retval = $this->response->write('Another '.JSON($user).'<br />');

            $i++; sleep(1);
        }

        return;
    }

    // 测试SQL 报错
    public function sql(){
        $field = ['id', 'usernamex'];
        $order = ['id' => 'DESC'];
        $users = $this->m_user->Field($field)->Order($order)->Select();
        if(!$users){
            $this->response->write('NO USERS FOUND'.'<br />');
        }else{
            $this->response->write(JSON($users).'<br />');
        }

        $users = $this->m_user->SelectAll();
        $this->response->write('Users => '.JSON($users).'<br />');

        $user = $this->m_user->SelectByID('', 24);
        $this->response->write('User => '.JSON($user).'<br />');

        return;
    }

    // Redis
    public function redis(){
        $key = $this->getParam('key');
        $this->response->write('Key => '.$key.'<br />');
        
        if($key){
            $i = 1;
            while($i < 3){
                $val = Cache::get($key);
                $this->response->write(date('Y-m-d H:i:s'). ' => '.$val.'<br />');
                $i++; sleep(1);
            }
        }else{
            $this->response->write('Key is required !');
        }
        
        return;
    }

    public function param(){
        return $this->getParam('username');
    }

    public function QueryOne(){
        $username = $this->getParam('username');
        $user = $this->m_user->getUserByUsername($username);
        return JSON($user);
    }

    public function multiInsert(){
        $user = $users = [];

        $user['username'] = 'Kobe';
        $user['password'] = md5('Lakers');
        $users[] = $user;

        $user['username'] = 'Curry';
        $user['password'] = md5('Warriors');
        $users[] = $user;

        $user['username'] = 'Thompson';
        $user['password'] = md5('Warriors');
        $users[] = $user;

        return $this->m_user->multiInsert($users);
    }

    public function timer(){
        $timerID = Timer::add(2000, [$this, 'tick'], ['xyz', 'abc', '123']);
        return 'Timer has been set, id is => '.$timerID;
    }

    public function tick($timerID, $args){
        Logger::log('Args in '.__METHOD__.' => '.JSON($args));
        Timer::clear($timerID);
        return;
    }

    public function task(){
        $args   = [];
        $args['callback'] = ['Importer', 'Run'];
        $args['param']    = ['Lakers', 'Swoole', 'Westlife'];
        $taskID = Task::add($args);
        return 'Task has been set, id is => '.$taskID;
    }

    public function follow(){
        $follows = $this->load('Follow')->SelectOne();
        return JSON($follows);
    }
}