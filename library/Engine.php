<?php

class Engine {

	const SORT_TYPE_NEWEST         = 1;
    const SORT_TYPE_SALES_VOLUME   = 2;
    const SORT_TYPE_PRICE_ASC      = 3;
    const SORT_TYPE_PRICE_DESC     = 4;
	const KEYWORD_SEPERATOR        = ',';
	const MATCH_FUNCTION_CLOSE_SQL = 'close';

	private static $m_product;

	private $url;
	private $closeUrl;

	private $page_size;
	private $is_all_letter;

	private $tags;
	private $keyword;
	private $price;
	private $low_price;
	private $high_price;
	private $sort;
	private $cursor;
	private $brand_id;
	private $category_id;
	private $pinyin_fields;
	private $return_fields;

	function __construct($keyword, $tags = '', $price = '', $brand_id = '', $category_id = '', $cursor = '', $sort = self::SORT_TYPE_PRICE_ASC){
		if($keyword){
			$search = ['\\\'', '\"'];
			$keyword = str_replace($search, '', $keyword);
			if($this->isAllLetter($keyword)){
				$this->is_all_letter = true;
				Logger::log('All is letter');
			}else{
				$keyword = str_replace('*', 'x', $keyword);
			}

			$this->keyword = $keyword;
		}

		if($brand_id){
			$this->brand_id = intval($brand_id);
		}

		if($category_id){
			$this->category_id = intval($category_id);
		}

		if($tags){
			$this->tags = $tags;
		}

		if($cursor){
			$this->cursor = $cursor;
		}

		if($sort){
			$this->sort = intval($sort);
		}

		if($price){
			$this->price = $price;
			list($low, $high) = explode(self::KEYWORD_SEPERATOR, $price);
			if($low){
				$this->low_price = intval($low);
			}

			if($high){
				$this->high_price = intval($high);
			}
		}

		$this->init();
		$this->initUrl();
	}

	private function getPageSize(){
		return Config::get('elasticsearch', 'page_size');
	}

	private function init(){
		if(!self::$m_product){
			self::$m_product = Helper::load('Product');
		}

		$this->page_size     = $this->getPageSize();
		$this->pinyin_fields = $this->getPinyinFields();
		$this->return_fields = $this->getReturnFields();

		return true;
	}

	private function getReturnFields(){
		return Config::get('elasticsearch', 'return_fields');
	}

	private function getPinyinFields(){
		return Config::get('elasticsearch', 'pinyin_fields');
	}

	private function isAllLetter($keyword){
		return preg_match("/^[a-z]*$/i", $keyword);
	}

	private function initUrl(){
		if(!$this->url){
			$host   = Config::get('elasticsearch', 'host');
			$port   = Config::get('elasticsearch', 'port');
			$user   = Config::get('elasticsearch', 'user');
			$pass   = Config::get('elasticsearch', 'pass');
			$scheme = Config::get('elasticsearch', 'scheme');
			$format = Config::get('elasticsearch', 'format');
			$sql_function = Config::get('elasticsearch', 'sql_function');

			$base_url = $scheme.'://'.$user.':'.$pass.'@'.$host.':'.$port.'/'.$sql_function;

			$this->setUrl($base_url, $format);
			$this->setCloseUrl($base_url);
		}
		
		return true;
	}

	private function setUrl($base_url, $format){
		$this->url = $base_url.'/?format='.$format;
		return true;
	}

	private function setCloseUrl($base_url){
		$this->closeUrl = $base_url.'/'.self::MATCH_FUNCTION_CLOSE_SQL;
		return true;
	}

	public function search(){
		$retval = $this->request();
		$this->reset();
		return $retval;
	}

	private function getPinyinField(){
		return $this->pinyin_fields;
	}

	private function getReturnField(){
		return $this->return_fields;
	}

	private function getHeader(){
		$header = [];
		$header = ["Content-type:application/json;charset='utf-8'", "Accept:application/json"];

		return $header;
	}

	private function clearSql($sql){
		$search = ['`', TB_PREFIX];
		$sql = str_replace($search, '', $sql);

		$search = ['"'];
		$sql = str_replace($search, '\'', $sql);
		Logger::log('SQL => '.$sql);
		return $sql;
	}

	private function buildSql(){
		$where = [];
		$where_category_id_string = '';
		$where_name_string = $where_tags_string = $where_price_string = '';

		if($this->brand_id){
			$where['brand_id'] = $this->brand_id;
		}

		if($this->category_id){
			$where['category_id'] = $this->category_id;
		}

		if($this->keyword){
			$where_name_string = '(';
			if($this->is_all_letter){
				Logger::log('Search with pinyin');
				$pinyin_fields = $this->getPinyinField();
				if($pinyin_fields){
					$i = 1;
					foreach($pinyin_fields as $field){
						$where_name_string .= $field.' LIKE "%'.$this->keyword.'%"';
						if($i != sizeof($pinyin_fields)){
							$where_name_string .= ' OR ';
						}
						$i++;
					}
				}
			}else{
				$keyword_arr = explode(self::KEYWORD_SEPERATOR, $this->keyword);

				$i = 1;
				foreach($keyword_arr as $keyword){
					if($keyword){
						$where_name_string .= 'name LIKE "%'.$keyword.'%"';
						if($i != sizeof($keyword_arr)){
							$where_name_string .= ' OR ';
						}
					}
					$i++;
				}
			}

			$where_name_string .= ')';
		}

		if($this->low_price || $this->high_price){
			$where_price_string = '(';
		}

		if($this->low_price){
			$where_price_string .= 'price >= '.$this->low_price;
		}

		if($this->high_price){
			if($this->low_price){
				$where_price_string .= ' AND ';
			}
			$where_price_string .= 'price <= '.$this->high_price;
		}

		if($this->low_price || $this->high_price){
			$where_price_string .= ')';
		}

		if($this->tags){
			$where_tags_string .= '(';
			$tag_arr = explode(',', $this->tags);

			$j = 1;
			foreach($tag_arr as $tag){
				if($tag){
					$where_tags_string .= 'tags LIKE "%'.$tag.'%"';
					if($j != sizeof($tag_arr)){
						$where_tags_string .= ' AND ';
					}
				}
				$j++;
			}
			$where_tags_string .= ')';
		}

		$order = $this->buildSort();
		$field = $this->getReturnField();

		$sql = self::$m_product->Field($field)->Where($where)->Where($where_name_string)->Where($where_tags_string)->Where($where_price_string)->Where($where_category_id_string)->Order($order)->generateSQL();

		return $this->clearSql($sql);
	}

	private function buildSort(){
		$sort_key = $sort_val = '';
		switch ($this->sort) {
            case self::SORT_TYPE_NEWEST:
				$sort_key = 'date_added';
				$sort_val = 'DESC';
			break;

            case self::SORT_TYPE_SALES_VOLUME:
				$sort_key = 'sales';
				$sort_val = 'DESC';
			break;

            case self::SORT_TYPE_PRICE_ASC:
				$sort_key = 'price';
				$sort_val = 'ASC';
			break;

            case self::SORT_TYPE_PRICE_DESC:
				$sort_key = 'price';
				$sort_val = 'DESC';
			break;

            default:
				$sort_key = 'sort_order';
				$sort_val = 'DESC';
		}
		
		return [$sort_key => $sort_val];
	}

	private function resetSql(){
		self::$m_product->resetOption();
	}

	private function buildPost(){
		$post = [];
		if($this->cursor){
			$post['cursor'] = $this->cursor;
		}else{
			$post['query']      = $this->buildSql();
			$post['fetch_size'] = $this->page_size;
		}

		if(ENV == 'DEV'){
			Logger::log('Post params => '.JSON($post));
		}

		return $post;
	}

	private function request(){
		$json = $this->performRequest($this->url);
		$retval = json_decode($json, true);

		$cursor = '';
		if($retval && isset($retval['rows'])){
			if(isset($retval['cursor'])){
				$cursor = $retval['cursor'];
				if(ENV == 'DEV'){
					Logger::log('Cursor => '.$cursor);
				}
			}else{
				$this->closeCursor();
			}

			$rep = [];
			$rep['cursor'] = $cursor;
			$rep['rows']   = $retval['rows'];
			return $rep;
		}else{
			$this->closeCursor();
			Logger::error('Retval => '.$json);
			return null;
		}
	}

	final private function performRequest($url){
		$header = $this->getHeader();
		$post   = $this->buildPost();
		$json   = HttpClient::post($url, JSON($post), $header);
		$retval = json_decode($json, true);

		if(isset($retval['rows']) && !$retval['rows']){
			Logger::log('Nothing return');
			unset($retval);
		}

		return $json;
	}

	private function closeCursor(){
		if($this->cursor){
			$json = $this->performRequest($this->closeUrl);
			Logger::log('Closing cursor => '.$json);
			$this->cursor = null;
		}
		return true;
	}

	private function reset(){
		$this->resetSql();
		$this->low_price     = null;
		$this->high_price    = null;
		$this->keyword       = null;
		$this->tags          = null;
		$this->price         = false;
		$this->is_all_letter = false;
		$this->sort          = null;
		$this->brand_id      = null;
		$this->category_id   = null;

		return true;
	}
}