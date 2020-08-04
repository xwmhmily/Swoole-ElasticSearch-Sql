<?php

class C_Search extends Controller{
 
    public function index(){
        try{
            $tags        = $this->getParam('tags');
            $keyword     = $this->getParam('keyword');
            $brand_id    = $this->getParam('brand_id');
            $category_id = $this->getParam('category_id');

            if(!$keyword && !$tags && !$brand_id && !$category_id){
                $retval = [];
                $retval['code']  = 0;
                $retval['error'] = 'Keyword or category or tags or brand_id or category_id is required';
                return JSON($retval);
            }

            $start_time = Logger::getMicrotime();

            $price  = $this->getParam('price');
            $cursor = $this->getParam('cursor');
            $sort   = $this->getParam('sort');
            $retval = Engine::search($keyword, $tags, $price, $brand_id, $category_id, $cursor, $sort);

            $end_time = Logger::getMicrotime();
            $rep['start']  = $start_time;
            $rep['end']    = $end_time;
            $rep['cost']   = $end_time - $start_time;
            $rep['retval'] = $retval;
            
            $this->header();
            return JSON($rep);
        }catch(Throwable $e){
            return $e->getMessage();
        }
    }
}