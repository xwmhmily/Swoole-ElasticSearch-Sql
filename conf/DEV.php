<?php

$config = [
	'common' => [
		'app_name'                 => 'Swoole-ElasticSearch-Sql',
		'app_version'              => '1.0',
		'tb_pk'                    => 'id',
		'tb_prefix'                => 'oc_',
		'tb_suffix_sf'             => '_',
		'user'                     => 'www',
		'group'                    => 'www',
		'backlog'                  => 128,
		'daemonize'                => 1,
		'worker_num'               => 2,
		'task_ipc_mode'            => 1,
		'task_worker_num'          => 1,
		'open_cpu_affinity'        => 1,
		'dispatch_mode'            => 2,
		'heartbeat_idle_time'      => 120,
		'heartbeat_check_interval' => 30,
		'open_eof_check'           => TRUE,
		'package_eof'              => "\r\n",
		'open_length_check'        => true,
		'package_length_type'      => 'N',
		'package_length_offset'    => 8,
  		'package_body_offset'      => 16,
		'log_level'                => 3,
		'error_level'              => 2,
		'module'                   => '',
		'log_method'               => 'file',
		'pid_file'                 => APP_PATH.'/log/swoole.pid',
		'stat_file'                => APP_PATH.'/log/swoole.stat',
		'log_file'       => APP_PATH.'/log/swoole_error_'.date('Y-m-d').'.log',
		'mysql_log_file' => APP_PATH.'/log/swoole_mysql_'.date('Y-m-d').'.log',
	],

	'http' => [
		'ip'     => '*',
		'port'   => 8888,

		'enable_static_handler' => true,
		'document_root' => APP_PATH.'/public',
	],

	'mysql' => [
		'required' => true,
		'db'   => 'mall',
		'host' => '127.0.0.1',
		'port' => 3306,
		'user' => 'root',
		'pwd'  => '123456',
		'max'  => 2,
		'log_sql' => true,
	],

	'mysql_slave' => [
		'db'   => 'test',
		'host' => '127.0.0.1',
		'port' => 3306,
		'user' => 'root',
		'pwd'  => '123455',
	],
	
	'redis' => [
		'required' => true,
		'db'   => '0',
		'host' => '127.0.0.1',
		'port' => 6379,
		'pwd'  => '123456',
	],

	'elasticsearch' => [
		'host' => 'es-cn-zz11qxwk0000ytcg5.public.elasticsearch.aliyuncs.com',
		'port' => '9200',
		'scheme' => 'http',
		'user' => 'elastic',
		'pass' => 'Tongshang20200805$',
		'queue_insert'  => 'swoole_es_insert',
		'queue_remove'  => 'swoole_es_remove',
		'index'         => 'products',
		'page_size'     => 10,
		'format'        => 'json',
		'sql_function'  => '_sql',
		'pinyin_fields' => ['brand_py', 'brand_pinyin', 'category_pinyin', 'category_py'],
		'return_fields' => '*',
	],

	'process' => [
		'Swoole-ElasticSearch-Sql_Insert'=> [
			'num'   => 1, 
			'mysql' => true,
			'redis' => true,
			'callback' => ['ElasticSearch', 'Insert'],
		],

		'Swoole-ElasticSearch-Sql_Remove'=> [
			'num'   => 1, 
			'mysql' => true,
			'redis' => true,
			'callback' => ['ElasticSearch', 'Remove'],
		],
	],
];

return $config;