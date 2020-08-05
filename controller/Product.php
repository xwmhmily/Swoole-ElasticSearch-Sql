<?php

class C_Product extends Controller{

    private $m_product;

    function __construct(){
        $this->m_product = Helper::load('Product');
    }

    public function createIndex(){
        $retval = ES::createIndex(ES::INDEX_PRODUCT);
        return JSON($retval);
    }

    public function removeIndex(){
        $retval = ES::removeIndex(ES::INDEX_PRODUCT);
        return JSON($retval);
    }

    public function rebuild(){
        $this->removeIndex();
        $this->createIndex();
        $this->indexAll();

        $retval = [];
        $retval['code']  = 1;
        $retval['error'] = 'Rebuilding, pls wait ......';
        return JSON($retval);
    }

    // Index all products
    public function indexAll(){
        try{
            $product_ids = $this->m_product->getAllProductId();

            if($product_ids){
                foreach($product_ids as $product){
                    $this->m_product->lpush($product['id']);
                }
            }

            $retval = [];
            $retval['code']  = 1;
            $retval['error'] = 'Indexing, pls wait ......';
            return JSON($retval);
        }catch(Throwable $e){
            return $e->getMessage();
        }
    }

    // Index a product
    public function create(){
        $product_id = $this->getParam('product_id');
        if(!$product_id){
            $retval = [];
            $retval['code']  = 0;
            $retval['error'] = 'Product_id is required';
            return JSON($retval);
        }

        $this->m_product->lpush($product_id);
        
        $retval = [];
        $retval['code']  = 1;
        $retval['error'] = 'Creating, pls wait ......';
        return JSON($retval);
    }

    // Update a product
    public function update(){
        $product_id = $this->getParam('product_id');
        if(!$product_id){
            $retval = [];
            $retval['code']  = 0;
            $retval['error'] = 'Product_id is required';
            return JSON($retval);
        }

        $create_after_delete = true;
        $this->m_product->removeDocument($product_id, $create_after_delete);
        
        $retval = [];
        $retval['code']  = 1;
        $retval['error'] = 'Updating, pls wait ......';
        return JSON($retval);
    }

    // Remove a product
    public function remove(){
        $product_id = $this->getParam('product_id');
        if(!$product_id){
            $retval = [];
            $retval['code']  = 0;
            $retval['error'] = 'Product_id is required';
            return JSON($retval);
        }

        $this->m_product->removeDocument($product_id);
        
        $retval = [];
        $retval['code']  = 1;
        $retval['error'] = 'Removing, pls wait ......';
        return JSON($retval);
    }

    // Remove all
    public function removeAll(){
        $product_ids = $this->m_product->getAllProductID();

        if($product_ids){
            foreach($product_ids as $product){
                $this->m_product->removeDocument($product['product_id']);
            }
        }

        $retval = [];
        $retval['code']  = 1;
        $retval['error'] = 'Removing, pls wait ......';
        return JSON($retval);
    }

}