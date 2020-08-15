<?php

class C_Search extends Controller{
 
    public function index(){
        $start_time = Logger::getMicrotime();

        $tags        = $this->getParam('tags');
        $keyword     = $this->getParam('keyword');
        $brand_id    = $this->getParam('brand_id');
        $category_id = $this->getParam('category_id');

        $price  = $this->getParam('price');
        $cursor = $this->getParam('cursor');
        $sort   = $this->getParam('sort');
        $enging = new Engine($keyword, $tags, $price, $brand_id, $category_id, $cursor, $sort);
        $retval = $enging->search();

        $end_time = Logger::getMicrotime();
        $rep['start']  = $start_time;
        $rep['end']    = $end_time;
        $rep['cost']   = $end_time - $start_time;
        $rep['retval'] = $retval;
        
        $this->header();
        return JSON($rep);
    }
}