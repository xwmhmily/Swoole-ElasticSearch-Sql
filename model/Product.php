<?php

class M_Product extends Model{

    public function __construct(){
        $this->table = TB_PREFIX.'products';
        parent::__construct();
    }

    public function getAllProductID(){
        $field = ['id'];
        $where = ['status' => 1];
        if(ENV == 'DEV'){
            return $this->Field($field)->Where($where)->Limit(1000)->Select();
        }else{
            return $this->Field($field)->Where($where)->Select();
        }
    }

    public function lpush($product_id){
        $product = $this->getProductInfo($product_id);

        if($product){
            $product['index'] = ES::INDEX_PRODUCT;
            ES::saveInsert($product);
        }

        return true;
    }

    public function getProductInfo($product_id){
        return $this->SelectByID('', $product_id);
    }

    public function saveElasticSearchId($product_id, $index_id){
        $u = [];
        $u['es_id'] = $index_id;
        return $this->UpdateByID($u, $product_id);
    }

    private function getElasticSearchId($product_id){
        return $this->SelectFieldByID('es_id', $product_id);
    }

    public function removeDocument($product_id, $create_after_delete = false){
        $es_id = $this->getElasticSearchId($product_id);

        $product = [];
        $product['es_id']      = $es_id;
        $product['product_id'] = $product_id;
        $product['index']      = ES::INDEX_PRODUCT;
        $product['create_after_delete'] = $create_after_delete;
        return ES::saveRemove($product);
    }
}