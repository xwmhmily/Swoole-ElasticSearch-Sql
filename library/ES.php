<?php

use Elasticsearch\ClientBuilder;
require LIB_PATH.'/pinyin.php';

class ES {

	const ES_INDEX_CREATED_TEXT = 'created';
	const ES_INDEX_UPDATED_TEXT = 'updated';
	const ES_INDEX_DELETED_TEXT = 'deleted';

	private static $client;

	private static function getClientInstance(){
		if(!self::$client){
			$hosts = [
				[
					'host'   => Config::get('elasticsearch', 'host'),
					'port'   => Config::get('elasticsearch', 'port'),
					'user'   => Config::get('elasticsearch', 'user'),
					'pass'   => Config::get('elasticsearch', 'pass'),
					'scheme' => Config::get('elasticsearch', 'scheme'),
				]
			];

			self::$client = ClientBuilder::create()->setHosts($hosts)->build();
		}

		return self::$client;
	}

	// Create a new index
	public static function createIndex($index){
		$params = ['index' => $index];
		return self::getClientInstance()->indices()->create($params);
	}

	// Delete an existed index
	public static function removeIndex($index){
		$params = ['index' => $index];
		return self::getClientInstance()->indices()->delete($params);
	}

	// Push data to insert queue
	public static function saveInsert($data){
		$data = JSON($data);
		Logger::log('Push to insert queue '.$data);
		Cache::lpush(Config::get('elasticsearch', 'queue_insert'), $data);
		
		return true;
	}

	// Do the insert action
	public static function Insert($data){
		$data = json_decode($data, true);
		$index = $data['index'];
		switch($index){
			case INDEX_PRODUCT:
				self::productInsert($data);
			break;
		}
	}

	private static function productInsert($data){
		$search  = ['*', '\n'];
		$replace = ['x', ''];

		unset($data['index']);
		if($data['brand']){
			$data['brand_py']     = pinyin($data['brand'], 'first');
			$data['brand_pinyin'] = str_replace(' ', '', pinyin($data['brand']));
		}

		if($data['category']){
			$data['category_py']     = pinyin($data['category'], 'first');
			$data['category_pinyin'] = str_replace(' ', '', pinyin($data['category']));
		}

		$data['price']       = round($data['price'], 2);
		$data['brand_id']    = intval($data['brand_id']);
		$data['product_id']  = intval($data['product_id']);
		$data['category_id'] = intval($data['category_id']);
		$data['name']        = str_replace($search, $replace, $data['name']);
		$data['tags']        = str_replace($search, $replace, $data['tags']);

		$params = [
			'index' => INDEX_PRODUCT,
			'type'  => INDEX_PRODUCT,
			'body'  => $data,
		];

		$retval = self::getClientInstance()->index($params);
		if($retval['result'] == self::ES_INDEX_CREATED_TEXT){
			$index_id = $retval['_id'];
			Helper::load('Product')->saveElasticSearchId($data['product_id'], $index_id);
		}
	}

	public static function saveRemove($data){
		$data = JSON($data);
		Logger::log('Push to remove queue '.$data);
		Cache::lpush(Config::get('elasticsearch', 'queue_remove'), $data);
		
		return true;
	}

	// Remove an index
	public static function Remove($data){
		Logger::log('Removing '.$data);

		$data = json_decode($data, true);
		$index = $data['index'];

		switch($index){
			case INDEX_PRODUCT:
				self::productRemove($data);
			break;
		}
	}

	private static function productRemove($data){
		$es_id      = $data['es_id'];
		$product_id = $data['product_id'];
		$create_after_delete = $data['create_after_delete'];

		$params = [
			'id'    => $es_id,
			'index' => INDEX_PRODUCT,
			'type'  => INDEX_PRODUCT,
		];
		
		$retval = self::getClientInstance()->delete($params);
		if($retval['result'] == self::ES_INDEX_DELETED_TEXT && $create_after_delete){
			Helper::load('Product')->lpush($product_id);
		}

		return true;
	}

}