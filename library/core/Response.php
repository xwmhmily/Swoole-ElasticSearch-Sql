<?php
/**
 * Author: 大眼猫
 * File: HTTP Response.php
 */

class Response {

	private static $instance;
	private static $middleware_error;
	private static $middleware_status;

	public static function getInstance(){
		return self::$instance;
	}

	public static function setInstance($instance){
		self::$instance = $instance;
	}

	public static function setMiddlewareStatus($status){
		self::$middleware_status = $status;
	}

	public static function getMiddlewareStatus(){
		return self::$middleware_status;
	}

	public static function setMiddlewareError($error){
		self::$middleware_error = $error;
	}

	public static function getMiddlewareError(){
		return self::$middleware_error;
	}

	public static function endByMiddleware(){
		Response::setMiddlewareStatus(TRUE);
		$error = self::getMiddlewareError();
		return self::send($error);
	}

	public static function send($output){
		return self::getInstance()->end($output);
	}

	public static function status(int $statusCode){
		return self::getInstance()->status($statusCode);
	}

	// Output error
	public static function error($error){
		if(ENV == 'DEV'){
			$trace = $error->getTrace();
			$last_error = Logger::getLastError();
	
			$error  = self::importStatic();
			$error .= self::initStatic();
			$error .= self::initHtml($last_error['errorStr']);
			$error = self::generalError($last_error, $error);
			$error = self::traceError($trace, $error);
			$error = self::configError($error);
			$error = self::getError($error);
			$error = self::postError($error);
			$error = self::cookieError($error);
			$error = self::serverError($error);
			$error = self::sqlError($last_error, $error);
		}else{
			$error = '<html>
				<head><title>500 Internal Server Error</title></head>
				<body bgcolor="white">
				<center><h1>500 Internal Server Error</h1></center>
				<hr>
				</body>
			</html>';
		}

		return self::send($error);
	}

	private static function initStatic(){
		$html = '<style>
				body{
					font-family:"ff-tisa-web-pro-1","ff-tisa-web-pro-2","Lucida Grande","Helvetica Neue",Helvetica,Arial,"Hiragino Sans GB","Hiragino Sans GB W3","Microsoft YaHei UI","Microsoft YaHei","WenQuanYi Micro Hei",sans-serif;
					padding: 10px;
				}
				</style>';
		$html .= "<script> 
				$(function(){
					$('#errorTab a').click(function(e){
						e.preventDefault();
						$('#errorTab a').parent().removeClass('active'); 
						$(this).parent().addClass('active');

						$('.tab-content div').removeClass('active');
						var id = $(this).attr('val');
						$('#'+id).addClass('active');
					}) 
				}) 
				</script>";
		return $html;
	}

	private static function initHtml($errorString){
		$html = '<h4>Error : '.$errorString.'</h4>
					<ul class="nav nav-tabs" id="errorTab"> 
					<li class="active"><a val="general" href="#general">General</a></li> 
					<li><a val="trace" href="#trace">Trace</a></li>
					<li><a val="config" href="#config">Config</a></li>
					<li><a val="get" href="#get">GET</a></li>
					<li><a val="post" href="#post">POST</a></li> 
					<li><a val="cookie" href="#cookie">COOKIE</a></li>  
					<li><a val="server" href="#server">SERVER</a></li>
					<li><a val="sql" href="#sql">SQL</a></li>
					</ul>';
		$html .= '<div class="tab-content">
					<div class="tab-pane active" id="general">[GENERAL_ERR]</div> 
					<div class="tab-pane" id="config">[CONFIG_ERR]</div>
					<div class="tab-pane" id="trace">[TRACE_ERR]</div>
					<div class="tab-pane" id="get">[GET_ERR]</div>
					<div class="tab-pane" id="post">[POST_ERR]</div>
					<div class="tab-pane" id="cookie">[COOKIE_ERR]</div>
					<div class="tab-pane" id="server">[SERVER_ERR]</div>
					<div class="tab-pane" id="sql">[SQL_ERR]</div>
					</div>';
		return $html;
	}

	private static function importStatic(){
		$html = '<link href="/css/bootstrap.min.css" rel="stylesheet">
		<link href="/css/bootstrap-responsive.min.css" rel="stylesheet">
		<link href="/css/docs.css" rel="stylesheet">
		<script src="/js/jquery-1.7.min.js"></script>
		<link href="/css/prettify.css" rel="stylesheet">';
		return $html;
	}

	private static function sqlError($last_error, $error){
		$sqlError = '<ul>';
		if ($last_error['errorSQL']) {
			$sqlError .= '<li>' . $last_error['errorSQL'] . '</li>';
		}
		$sqlError .= '</ul>';
		return str_replace('[SQL_ERR]', $sqlError, $error);
	}

	private static function serverError($error){
		$serverError = '<ul>';
		foreach (Request::getServer() as $key => $val) {
			$serverError .= '<li>' . $key . ' => ' . var_export($val, TRUE) . '</li>';
		}
		$serverError .= '</ul>';
		return str_replace('[SERVER_ERR]', $serverError, $error);
	}

	private static function cookieError($error){
		$cookieError = '<ul>';
		if(Request::getCookie()){
			foreach (Request::getCookie() as $key => $val) {
				$cookieError .= '<li>' . $key . ' => ' . $val . '</li>';
			}
		}
		$cookieError .= '</ul>';
		return str_replace('[COOKIE_ERR]', $cookieError, $error);
	}

	private static function postError($error){
		$postError = '<ul>';
		if(Request::isPost() && Request::getData()){
			foreach(Request::getData() as $key => $val) {
				$postError .= '<li>' . $key . ' => ' . $val . '</li>';
			}
		}
		$postError .= '</ul>';
		return str_replace('[POST_ERR]', $postError, $error);
	}

	private static function getError($error){
		$getError = '<ul>';
		if(Request::isGet() && Request::getData()){
			foreach (Request::getData() as $key => $val) {
				$getError .= '<li>' . $key . ' => ' . $val . '</li>';
			}
		}
		$getError .= '</ul>';
		return str_replace('[GET_ERR]', $getError, $error);
	}

	private static function configError($error){
		$config = Config::get();
		$configError = '<ul>';
		foreach ($config as $key => $val) {
			$configError .= '<li>' . $key . ' => ' . var_export($val, TRUE) . '</li>';
		}
		$configError .= '</ul>';
		return str_replace('[CONFIG_ERR]', $configError, $error);
	}

	private static function traceError($trace, $error){
		$traceError = '<ul>';
		foreach ($trace as $val) {
			foreach ($val as $k => $v) {
				if($k != 'type' && $k != 'args'){
					$traceError .= '<li>' . $k . ' => '.var_export($v, TRUE).'</li>';
				}
			}
			$traceError .= '<hr />';
		}
		$traceError .= '</ul>';
		return str_replace('[TRACE_ERR]', $traceError, $error);
	}

	private static function generalError($last_error, $error){
		$generalError = '<ul>';
		$generalError .= '<li>APP: '.APP_NAME.'</li>';
		$generalError .= '<li>Environ: ' . ENV . '</li>';
		$generalError .= '<li>Error NO: ' . $last_error['errorNO'] . '</li>';
		$generalError .= '<li>Error: ' . $last_error['errorStr'] . '</li>';
		$generalError .= '<li>File: ' . $last_error['errorFile'] . '</li>';
		$generalError .= '<li>Line: ' . $last_error['errorLine'] . '</li>';
		$generalError .= '</ul>';
		return str_replace('[GENERAL_ERR]', $generalError, $error);
	}

}