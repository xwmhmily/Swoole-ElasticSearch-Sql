<?php
/**
 * Index Controller
 */

class C_Index extends Controller {

    // http index 就写这里
    public function index(){
        return 'Welcome to '.APP_NAME.' http server';
    }
}