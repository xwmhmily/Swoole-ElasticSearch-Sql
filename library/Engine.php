<?php

class Engine {

	const SORT_TYPE_NEWEST         = 1;
    const SORT_TYPE_SALES_VOLUME   = 2;
    const SORT_TYPE_PRICE_ASC      = 3;
    const SORT_TYPE_PRICE_DESC     = 4;

	const KEYWORD_SEPERATOR        = ',';
	const MATCH_FUNCTION_SQL       = '_sql';
	const MATCH_FUNCTION_CLOSE_SQL = 'close';
	const MATCH_FUNCTION_ALI_SQL   = '_alisql';

	private static $url;
	private static $closeUrl;
	private static $use_or;
	private static $m_brand;
	private static $m_category;
	private static $m_product;
	private static $tags;
	private static $keyword;
	private static $page_size;
	private static $price;
	private static $low_price;
	private static $high_price;
	private static $sort;
	private static $cursor;
	private static $is_all_letter;
	private static $is_brand;
	private static $brand_id;
	private static $category_id;
	private static $is_top_category;
	private static $is_child_category;
	private static $child_category_tags;
	private static $children_categories;

	private static function getPageSize(){
		return Config::get('elasticsearch', 'page_size');
	}

	private static function getUseOr(){
		return Config::get('elasticsearch', 'use_or');
	}

	private static function init(){
		if(!self::$m_brand){
			self::$m_brand = Helper::load('Brand');
		}

		if(!self::$m_category){
			self::$m_category = Helper::load('Category');
		}

		if(!self::$m_product){
			self::$m_product = Helper::load('Product');
		}

		if(!self::$page_size){
			self::$page_size = self::getPageSize();
		}

		if(!self::$use_or){
			self::$use_or = self::getUseOr();
		}

		return true;
	}

	private static function calcKeywordCount(){
		$keyword_arr = explode(self::KEYWORD_SEPERATOR, self::$keyword);
		return sizeof($keyword_arr);
	}

	private static function isAllLetter($keyword){
		return preg_match("/^[a-z]*$/i", $keyword);
	}

	private static function initParam($keyword, $tags = '', $price = '', $brand_id = '', $category_id = '', $cursor = '', $sort = ''){
		if($keyword){
			$search = ['\\\'', '\"'];
			$keyword = str_replace($search, '', $keyword);
			if(self::isAllLetter($keyword)){
				self::$is_all_letter = true;
				Logger::log('All is letter');
			}else{
				$keyword = str_replace('*', 'x', $keyword);
			}

			self::$keyword = $keyword;
		}

		if($brand_id){
			self::$brand_id = intval($brand_id);
		}

		if($category_id){
			self::$category_id = intval($category_id);
		}

		if($tags){
			$tags = str_replace('*', 'x', $tags);
			self::$tags = str_replace(' ', self::KEYWORD_SEPERATOR, $tags);
		}

		if($cursor){
			self::$cursor = $cursor;
		}

		if($sort){
			self::$sort = intval($sort);
		}

		if($price){
			self::$price = $price;
			list($low, $high) = explode(self::KEYWORD_SEPERATOR, $price);
			if($low){
				self::$low_price = intval($low);
			}

			if($high){
				self::$high_price = intval($high);
			}
		}

		return true;
	}

	private static function initUrl(){
		if(!self::$url){
			$host   = Config::get('elasticsearch', 'host');
			$port   = Config::get('elasticsearch', 'port');
			$user   = Config::get('elasticsearch', 'user');
			$pass   = Config::get('elasticsearch', 'pass');
			$scheme = Config::get('elasticsearch', 'scheme');
			$format = Config::get('elasticsearch', 'format');
			$sql_function = Config::get('elasticsearch', 'sql_function');

			$base_url = $scheme.'://'.$user.':'.$pass.'@'.$host.':'.$port.'/'.$sql_function;

			self::setUrl($base_url, $format);
			self::setCloseUrl($base_url);
		}
		
		return true;
	}

	private static function setUrl($base_url, $format){
		self::$url = $base_url.'/?format='.$format;
		return true;
	}

	private static function setCloseUrl($base_url){
		self::$closeUrl = $base_url.'/'.self::MATCH_FUNCTION_CLOSE_SQL;
		return true;
	}

	public static function search($keyword, $tags = '', $price = '', $brand_id = '', $category_id = '', $cursor = '', $sort = self::SORT_TYPE_PRICE_ASC){
		self::init();
		self::initUrl();
		self::initParam($keyword, $tags, $price, $brand_id, $category_id, $cursor, $sort);

		$count = self::calcKeywordCount();

		if($count == 1){
			$is_brand = self::$m_brand->isBrand($keyword);

			if($is_brand){
				// TO-DO：增加品牌与顶级分类的关联
				self::$is_brand = true;
				Logger::log('This is a brand');
			}else{
				$category_id = self::$m_category->isFirstCategory($keyword);
				if($category_id){
					Logger::log('This is a top category');

					self::$is_top_category = true;
					self::$children_categories = self::$m_category->getChildrenCategories($category_id);
				}else{
					$category_id = self::$m_category->isSecondCategory($keyword);
					if($category_id){
						Logger::log('This is a child category');

						self::$is_child_category = true;
						self::$child_category_tags = self::$m_category->getCategoryTags($category_id);
					}
				}
			}
		}

		$retval = self::request();
		return self::response($retval);
	}

	private static function response($retval){
		$rep = [];
		$rep['cursor'] = '';
		$rep['brands'] = $rep['categories'] = $rep['tags'] = [];

		if(self::$is_brand){
			// TO_DO: 返回品牌与分类的关联
			$rep['brands'] = 'TO_DO';
		}else if(self::$is_top_category){
			$rep['categories'] = self::$children_categories;
		}else if(self::$is_child_category){
			$rep['tags'] = self::$child_category_tags;
		}

		$rep['products'] = $retval['rows'];
		$rep['cursor']   = $retval['cursor'];

		self::reset();
		return $rep;
	}

	private static function getHeader(){
		$header = [];
		$header = ["Content-type:application/json;charset='utf-8'", "Accept:application/json"];

		return $header;
	}

	private static function clearSql($sql){
		$search = ['`', TB_PREFIX];
		$sql = str_replace($search, '', $sql);

		$search = ['"'];
		$sql = str_replace($search, '\'', $sql);
		Logger::log('SQL => '.$sql);
		return $sql;
	}

	private static function getChildrenCategoryIdArray(){
		$category_id_array = [];
		if(self::$children_categories){
			foreach(self::$children_categories as $category){
				$category_id_array[] = $category['category_id'];
			}
		}

		return $category_id_array;
	}

	// $op: AND, OR
	private static function buildSql($op = 'AND'){
		$where = [];
		$where_category_id_string = '';
		$where_name_string = $where_tags_string = $where_price_string = '';

		if(self::$brand_id){
			$where['brand_id'] = self::$brand_id;
		}

		if(self::$category_id){
			$where['category_id'] = self::$category_id;
		}

		if(self::$is_brand){
			$where['brand'] = self::$keyword;
		}else if(self::$is_top_category){
			$category_id_array = self::getChildrenCategoryIdArray();
			$category_id_string = implode(',',$category_id_array);
			$where_category_id_string = 'category_id IN ('.$category_id_string.')';
		}else if(self::$is_child_category){
			$where['category'] = self::$keyword;
		}else{
			if(self::$keyword){
				$where_name_string = '(';
				if(self::$is_all_letter){
					Logger::log('Search with pinyin');
					$pinyin_fields = self::buildPinyinField();
					if($pinyin_fields){
						$i = 1;
						foreach($pinyin_fields as $field){
							$where_name_string .= $field.' LIKE "%'.self::$keyword.'%"';
							if($i != sizeof($pinyin_fields)){
								$where_name_string .= ' OR ';
							}
							$i++;
						}
					}
				}else{
					$keyword_arr = explode(',', self::$keyword);

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
		}

		if(self::$low_price || self::$high_price){
			$where_price_string = '(';
		}

		if(self::$low_price){
			$where_price_string .= 'price >= '.self::$low_price;
		}

		if(self::$high_price){
			if(self::$low_price){
				$where_price_string .= ' AND ';
			}
			$where_price_string .= 'price <= '.self::$high_price;
		}

		if(self::$low_price || self::$high_price){
			$where_price_string .= ')';
		}

		if(self::$tags){
			$where_tags_string .= '(';
			$tag_arr = explode(',', self::$tags);

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

		$field = self::buildField();
		$order = self::buildSort();

		if($op == 'AND'){
			$sql = self::$m_product->Field($field)->Where($where)->Where($where_name_string)->Where($where_tags_string)->Where($where_price_string)->Where($where_category_id_string)->Order($order)->generateSQL();
		}else{
			$sql = self::$m_product->Field($field)->Where($where)->Where($where_category_id_string)->Where($where_price_string)->Where($where_name_string)->ORR()->Where($where_tags_string)->Order($order)->generateSQL();
		}

		return self::clearSql($sql);
	}

	private static function buildPinyinField(){
		return ['brand_py', 'brand_pinyin', 'category_pinyin', 'category_py'];
	}

	private static function buildField(){
		return ['product_id', 'name', 'brand_id', 'brand', 'category_id', 'category', 'price', 'tags', 'brand_pinyin', 'brand_py', 'category_pinyin', 'category_py', 'sort_order'];
	}

	private static function buildSort(){
		$sort_key = $sort_val = '';
		switch (self::$sort) {
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

	private static function resetSql(){
		self::$m_product->resetOption();
	}

	private static function buildPost($op = 'AND'){
		$post = [];
		if(self::$cursor){
			$post['cursor'] = self::$cursor;
		}else{
			$post['query']      = self::buildSql($op);
			$post['fetch_size'] = self::$page_size;
		}

		if(ENV == 'DEV'){
			Logger::log('Post params => '.JSON($post));
		}

		return $post;
	}

	private static function request(){
		$json = self::performRequest(self::$url);
		$retval = json_decode($json, true);

		$cursor = '';
		if($retval && isset($retval['rows'])){
			if(isset($retval['cursor'])){
				$cursor = $retval['cursor'];
				if(ENV == 'DEV'){
					Logger::log('Cursor => '.$cursor);
				}
			}else{
				self::closeCursor();
			}

			$rep = [];
			$rep['cursor'] = $cursor;
			$rep['rows']   = $retval['rows'];
			return $rep;
		}else{
			self::closeCursor();
			Logger::error('Retval => '.$json);
			return null;
		}
	}

	final private static function performRequest($url){
		$header = self::getHeader();
		$post   = self::buildPost();
		$json   = HttpClient::post($url, JSON($post), $header);
		$retval = json_decode($json, true);

		if(isset($retval['rows']) && !$retval['rows']){
			Logger::log('Nothing return');
			if(!self::$price && self::$use_or){
				Logger::warn('Try to use OR');

				self::resetSql();
				$post = self::buildPost('OR');
				$json = HttpClient::post($url, JSON($post), $header);
			}
			unset($retval);
		}

		return $json;
	}

	private static function closeCursor(){
		if(self::$cursor){
			$json = self::performRequest(self::$closeUrl);
			Logger::log('Closing cursor => '.$json);
			self::$cursor = null;
		}
		return true;
	}

	private static function reset(){
		self::resetSql();
		self::$low_price     	   = null;
		self::$high_price    	   = null;
		self::$keyword       	   = null;
		self::$tags          	   = null;
		self::$is_brand      	   = false;
		self::$price         	   = false;
		self::$is_all_letter 	   = false;
		self::$sort          	   = null;
		self::$brand_id            = null;
		self::$category_id         = null;
		self::$is_top_category     = false;
		self::$is_child_category   = false;
		self::$child_category_tags = null;
		self::$children_categories = null;

		return true;
	}
}