<?php
/**
 * File: MiniSwoole
 * Author: 大眼猫
 */

class MiniSwoole {

	const MODE_CLI = 'CLI';

	private $log_file;
	private $min_version = '7.0';	
	private $extensions  = ['pdo', 'redis', 'swoole', 'pdo_mysql'];

	public function boostrap(){
		$this->init();
		$this->checkSapi();
		$this->checkVersion();
		$this->checkExtension();
		$this->initLogger();
		$this->initAutoload();

		return $this;
	}

	private function init(){
		date_default_timezone_set('Asia/Shanghai');
		error_reporting(E_ALL ^ E_NOTICE);
		
		ini_set('log_errors', 'on');
		ini_set('display_errors', 'off');
		
		define('MINI_HTTP_VERSION', '1.0');
		define('LIB_PATH',  APP_PATH.'/library');
		define('CORE_PATH', LIB_PATH.'/core');
		define('CONF_PATH', APP_PATH.'/conf');
		require_once LIB_PATH.'/Function.php';

		$core_files = glob(CORE_PATH.'/*.php');
		foreach($core_files as $f){
			require_once $f;
		}

		$config = Config::get('common');
		define('APP_NAME', $config['app_name']);
		
		// PK and TABLE_PREFIX and TB_SUFFIX_SF
		define('TB_PK', $config['tb_pk']);
		define('TB_PREFIX', $config['tb_prefix']);
		if($config['tb_suffix_sf']){
			define('TB_SUFFIX_SF', $config['tb_suffix_sf']);
		}

		$this->setLogFile($config['log_file']);
	}

	private function setLogFile($log_file){
		$this->log_file = $log_file;
	}

	private function error($error){
		file_put_contents($this->log_file, $error."\r\n", FILE_APPEND);
	}

	// Only run in CLI
	private function checkSapi(){
		$sapi = php_sapi_name();
		if (strtoupper($sapi) != self::MODE_CLI) {
			$error = 'Error: MiniHttp ONLY run in cli mode';
			$this->error($error);
			echo $error.PHP_EOL; die;
		}
	}

	// PHP Version must be greater then 7.0
	private function checkVersion(){
		$retval = version_compare(PHP_VERSION, $this->min_version);
		if(-1 == $retval){
			$error = 'Error: PHP version must be greater then 7.0';
			$this->error($error);
			echo $error.PHP_EOL; die;
		}
	}

	// Must install necessary extensions
	private function checkExtension(){
		foreach($this->extensions as $extension){
			if(!extension_loaded($extension)){
				$error = 'Error: Extension '.$extension.' is required';
				$this->error($error);
				echo $error.PHP_EOL; die;
			}
		}
	}

	private function initLogger(){
        ini_set('error_log', Config::get('common', 'log_file'));
		set_error_handler(['Logger', 'errorHandler'], E_ALL | E_STRICT);
		Logger::init();
	}

	// Autoload
	private function initAutoload(){
		spl_autoload_register([$this, 'classLoader']);
        spl_autoload_register(function($class){
			$file = LIB_PATH.'/'.$class.'.php';
			if(file_exists($file)){
				require_once($file);
			}else{
				$error = 'Error in autoload: No such file => '.$file;
				Helper::raiseError(debug_backtrace(), $error);			
			}
		}, true, false);
	}

	public function classLoader(){
		$autoload_file = LIB_PATH.'/vendor/autoload.php';
		if(file_exists($autoload_file)){
			require_once $autoload_file;
		}else{
			return false;
		}
	}

	public function process(){
		Process::heartbeat();
	}

	// Let's go
	public function run(){
		$server = new HttpServer();
		$server->start();
	}
}