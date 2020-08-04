<?php
/*
 * Server callback functions
 * Remark: 1.7.15+版本, 当设置dispatch_mode = 1/3 时会自动去掉 onConnect / onClose 事件回调
 * */

class Hooker {

    // Manager start
    public static function onManagerStart(swoole_server $server){
        if(strtoupper(PHP_OS) == Server::OS_LINUX){
            swoole_set_process_name(APP_NAME.'_manager');
        }
    }

    // Worker start
    public static function onWorkerStart(swoole_server $server, int $workerID){
        $config = Config::get(Pool::TYPE_MYSQL);
        $max = $config['max'];

		if ($server->taskworker) {
            $max = 1;
            $process_name = APP_NAME.'_task';
        }else{
            !$max && $max = 1;
            $process_name = APP_NAME.'_worker';
        }

        self::setProcessName($process_name);

        // Is MySQL connection required ?
        if($config['required']){
            for($i = 1; $i <= $max; $i++){
                $retval = Pool::getInstance(Pool::TYPE_MYSQL);
                if($retval === FALSE){
                    Logger::error('Worker '.$workerID.' fail to connect MySQL !');
                }
            }
        }else{
            Logger::log('MySQL is not required');
        }

        // Is Redis connection required ?
        $config = Config::get(Pool::TYPE_REDIS);
        if($config['required']){
            $retval = Pool::getInstance(Pool::TYPE_REDIS);
            if($retval === FALSE){
                Logger::error('Worker '.$workerID.' fail to connect Redis !');
            }
        }else{
            Logger::log('Redis is not required');
        }

        Worker::afterStart($server, $workerID);
        Logger::log('Worker '.$workerID.' ready for connections ...');
    }

    private static function setProcessName($process_name){
        if(strtoupper(PHP_OS) == Server::OS_LINUX){
            swoole_set_process_name($process_name);
        }

        return TRUE;
    }

    // Http onRequest, 将请求路由至控制器
    public static function onRequest(swoole_http_request $request, swoole_http_response $response){
        $method = strtoupper($request->server['request_method']);
        if($method != Request::HTTP_METHOD_GET && $method != Request::HTTP_METHOD_POST){
            $response->end('Error: Only GET and POST supported now !'); return;
        }

        Worker::beforeRequest($method, $request, $response);
        Router::routerStartup();
        $retval = Router::parse($request->server['request_uri']);
        $module = $retval['module'];
        $controller = $retval['controller'];
        $action = $retval['action'];
        Router::routerShutdown();

        $instance = Helper::import($module, $controller);
        $middleware_status = Response::getMiddlewareStatus();

        if($middleware_status !== FALSE){
            if($instance !== FALSE){
                $instance->method   = $method;
                $instance->request  = $request;
                $instance->response = $response;

                try{
                    $retval = $instance->$action();
                    Response::status(HTTP_CODE_SUCCESS);
                    if($retval){
                        $response->write($retval);
                    }
                    
                    $response->end();
                }catch(Throwable $e){
                    Response::status(HTTP_CODE_INTERNAL_ERROR);
                    Response::error($e);
                }
            }else{
                $response->status(HTTP_CODE_NOT_FOUND);

                $rep['code']  = 0;
                $rep['error'] = 'Controller '.$controller.' not found';
                $response->end(JSON($rep));
            }
        }else{
            Response::endByMiddleware();
        }
    }

    // Worker error
	public static function onWorkerError(swoole_server $serv, int $workerID, int $workerPID, int $exitCode, int $signal){
		Logger::fatal('Worker '.$workerID.' exit with code '.$exitCode.' and signal '.$signal);
	}

    // Worker stop
	public static function onWorkerStop(swoole_server $server, int $workerID){
		Worker::afterStop($server, $workerID);
		Logger::log('Worker '.$workerID.' stop');
	}

}