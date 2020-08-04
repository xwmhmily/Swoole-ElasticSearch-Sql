<?php

class Worker {

	// Do something after worker start
    public static function afterStart(swoole_server $server, int $workerID){
		if($workerID == 0){
			$server->tick(1000, function(){
				Server::stat();
			});
		}
	}

	// Do something after worker stop
	public static function afterStop(swoole_server $server, int $workerID){

	}

	// Do something want before http request
	public static function beforeRequest($method, swoole_http_request $request, swoole_http_response $response){
		if(isset($request->get['page'])){
			Request::setPage(intval($request->get['page']));
		}else{
			Request::setPage(1);
		}

		Request::setInstance($request);
		Response::setInstance($response);
		
		$method = strtoupper($method);
		Request::setMethod($method);

		if($method == Request::HTTP_METHOD_GET){
			Request::setData($request->get);
		}else if($method == Request::HTTP_METHOD_POST){
			Request::setData($request->post);
		}

		Request::setCookie($request->cookie);
		Request::setServer($request->server);
	}

	// Do something after http request
	// TO-DO: 检测Connection是否Alive
	public static function afterRequest($method, swoole_http_request $request, swoole_http_response $response){
		
	}
	
}