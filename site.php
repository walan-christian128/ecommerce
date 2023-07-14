<?php


use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\User;


$app->get('/', function () {
    $page = new Page();
    $page->setTpl("index");

});




?>