<?php
/**
 * Author: 大眼猫
 * File: Request.php
 */

class Request {

	private static $data;
	private static $page;
	private static $server;
	private static $cookie;
	private static $method;
	private static $instance;

	const HTTP_METHOD_GET  = 'GET';
	const HTTP_METHOD_POST = 'POST';

	public static function has($key){
		return isset(self::$data[$key]);
	}

	public static function setData($data){
		self::$data = $data;
	}

	public static function getData(){
		return self::$data;
	}

	public static function get($key){
		if(isset(self::$data[$key])){
			return self::$data[$key];
		}else{
			return NULL;
		}
	}

	public static function set($key, $val){
		self::$data[$key] = $val;
	}

	public static function setInstance($instance){
		self::$instance = $instance;
	}

	public static function getInstance(){
		return self::$instance;
	}

	public static function getPage(){
		return self::$page;
	}

	public static function setPage($page){
		self::$page = $page;
	}

	public static function setMethod($method){
		self::$method = $method;
	}

	public static function getMethod(){
		return self::$method;
	}

	public static function isGet(){
		return self::getMethod() == self::HTTP_METHOD_GET;
	}

	public static function isPost(){
		return self::getMethod() == self::HTTP_METHOD_POST;
	}

	public static function setCookie($cookie){
		self::$cookie = $cookie;
	}

	public static function getCookie(){
		return self::$cookie;
	}

	public static function setServer($server){
		self::$server = $server;
	}

	public static function getServer(){
		return self::$server;
	}

}