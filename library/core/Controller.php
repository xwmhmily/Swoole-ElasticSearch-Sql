<?php
/**
 * File: Controller.php
 * Author: 大眼猫
 */

abstract class Controller {

	public $request;  // http request
	public $response; // http response
	public $method;   // http request method: GET or POST

	protected function getParam($key, $filter = TRUE){
		$method = strtolower($this->method);
		if(isset($this->request->$method[$key])){
			$value = $this->request->$method[$key];
		}else{
			$value = NULL;
		}

		if($filter){
			$value = Security::filter($value);
		}

		return $value;
	}

	protected function header(string $key = 'Content-Type', string $value = 'text/html; charset=utf-8'){
		return $this->response->header($key, $value);
	}

	protected function cookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = FALSE, bool $httpOnly = FALSE){
		return $this->response->cookie($key, $value, $expire, $path, $domain, $secure, $httpOnly);
	}

	protected function status(int $statusCode){
		return $this->response->status($statusCode);
	}

	protected function gzip(int $level = 1){
		return $this->response->gzip($level);
	}

	// 中间件
	protected function middleware($middleware){
		try{
			(new Pipeline)->send()->through($middleware)->via('handle')->then(function(){
				Response::setMiddlewareStatus(TRUE);
			});
		}catch (Throwable $e){
			Response::setMiddlewareStatus(FALSE);

			$error = [];
			$error['code']  = $e->getCode();
			$error['error'] = $e->getMessage();
			Response::setMiddlewareError(JSON($error));
		}
	}

	// 加载模型
	protected function load($model){
		return Helper::load($model);
	}

	public function __call($name, $arguments){
		$rep['code']  = 0;
		$rep['error'] = 'Method '.$name.' not found';
		$this->status(HTTP_CODE_NOT_FOUND);
		return JSON($rep);
	}
	
}