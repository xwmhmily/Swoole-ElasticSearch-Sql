<?php

class ElasticSearch {

	public static function Insert(){
		Logger::log(__METHOD__.' is ready');

		while(TRUE){
			$queue = Config::get('elasticsearch', 'queue_insert');
			$data = Cache::rpop($queue);
			if($data){
				go(['ES', 'Insert'], $data);
			}

			sleep(1);
		}
	}

	public static function Remove(){
		Logger::log(__METHOD__.' is ready');

		while(TRUE){
			$queue = Config::get('elasticsearch', 'queue_remove');
			$data = Cache::rpop($queue);
			if($data){
				go(['ES', 'remove'], $data);
			}

			sleep(1);
		}
	}

}