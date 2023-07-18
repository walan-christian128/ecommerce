<?php
session_start();
require_once("vendor/autoload.php");
require_once("functions.php");

use \Slim\Slim;

use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Products;


///inicio da classe login 

$app = new Slim();

$app->config('debug', true);

$app->get('/', function () {

    $products = Products::listAll();

    $page = new Page();

    $page->setTpl("index", [
      'products'=>Products::checkList($products)
    ]);

});

$app->get('/admin', function () {

    User::verifyLogin();
    $page = new PageAdmin();
    $page->setTpl("index");
  

});


$app->get('/admin/login', function () {
    //desabiliatr header e footer da tela de login ////
    $page = new PageAdmin([
        "header" => false,
        "footer" => false

    ]);
    $page->setTpl("login");

});
$app->post('/admin/login', function () {

    User::login($_POST["login"], $_POST["password"]);
    header("Location:/admin");
    exit;


});

$app->get('/admin/logout', function () {
    User::logout();
    header("Location: /admin/login");
    exit;

});
 ///rotas usuarios adm

$app->get("/admin/users", function () {
    User::verifyLogin();

    $users = User::listAll();

    $page = new PageAdmin();

    $page->setTpl("users", array(
        "users" => $users
    )
    );
});

$app->get('/admin/users/create', function () {
    User::verifyLogin();

    $page = new PageAdmin();

    $page->setTpl("users-create");

});
$app->get('/admin/users/:iduser/delete', function ($iduser) {

    User::verifyLogin();
    $user = new User();
    $user->get((int) $iduser);
    $user->delete();

    header("Location: /admin/users");
    exit;
});

$app->get('/admin/users/:iduser', function ($iduser) {
    User::verifyLogin();
    $user = new User();

    $user->get((int) $iduser);

    $page = new PageAdmin();

    $page->setTpl("users-update", array(
        "user" => $user->getValues()

    )
    );

});
$app->post('/admin/users/create', function () {
    User::verifyLogin();

    $user = new User();

    $_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

    $user->setData($_POST);

    $user->save();
    header("Location: /admin/users");
    exit;


});
$app->post('/admin/users/:iduser', function ($iduser) {

    User::verifyLogin();

    $user = new User();

    $_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

    $user->get((int) $iduser);

    $user->setData($_POST);

    $user->update();

    header("location: /admin/users");
    exit;
});

$app->get("/admin/forgot", function () {
    $page = new PageAdmin([
        "header" => false,
        "footer" => false

    ]);
    $page->setTpl("forgot");

});

$app->post("/admin/forgot", function () {

   
    $user=User::getForgot( $_POST["email"]);
    header ("Location: /admin/forgot/sent");

    exit;
});

$app -> get("/admin/forgot/sent", function () {
     
    $page = new PageAdmin([
        "header" => false,
        "footer" => false

    ]);
    $page->setTpl("forgot-sent");


});
///rotas das categorias adm/usuario
$app ->get("/admin/categories", function() {
    User::verifyLogin();

    $categories = Category :: listAll();
    $page = new PageAdmin();
    $page ->setTpl("categories",[
         'categories' => $categories
    ]);     

});

$app ->get("/admin/categories/create", function () {
    User ::verifyLogin();

    $page = new PageAdmin();
    $page ->setTpl("categories-create");

});

$app ->post("/admin/categories/create", function () {
  User ::verifyLogin();

  $category = new Category();

  $category ->setData($_POST);
  $category ->save();

  header('Location: /admin/categories');
   exit;
});

$app->get("/admin/categories/:idcategory/delete", function($idcategory){
     User ::verifyLogin();

    $category =  new Category();

    $category ->get((int)$idcategory);
    $category ->delete();


   header ('Location: /admin/categories');
   exit;

});

$app-> get ("/admin/categories/:idcategory", function($idcategory){
     
    User::verifyLogin();
    $category = new Category();
    $category -> get ((int)$idcategory);


    $page = new PageAdmin();

    $page ->setTpl("categories-update", [
     'category' => $category -> getValues()

   ]);



});
$app-> post ("/admin/categories/:idcategory", function($idcategory){
    User::verifyLogin();

    $category = new Category();
    $category -> get ((int)$idcategory);


    $page = new PageAdmin();
    $category -> setData($_POST);
    $category -> save();
    header ('Location: /admin/categories');
   exit;
});


$app->get("/categories/:idcategory", function($idcategory){


   $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

   $category = new Category();

   $category-> get((int)$idcategory);

   $pagination = $category ->getProductsPage($page);

   $pages = [];

   for($i = 1; $i <= $pagination['pages']; $i++ ){
        array_push($pages,[
                     'link' =>'/categories/'.$category ->getidcategory().'?page='.$i,
                     'page' => $i
  ]);

}

   $page = new Page();

     $page->setTpl("category",[
      'category' => $category->getValues(),
      'products' => $pagination["data"],
      'pages'    => $pages

   ]);


});

$app->get("/admin/categories/:idcategory/products", function($idcategory){
    User::verifyLogin();

   $category = new Category();

   $category-> get((int)$idcategory);

   $page = new PageAdmin();

   $page->setTpl("categories-products",[
    'category' => $category->getValues(),
    'productsRelated' =>$category->getProducts(),
    'productsNotRelated'=>$category->getProducts(false)
   ]);


});

$app->get("/admin/categories/:idcategory/products/:idproduct/add", function($idcategory, $idproduct){
    User::verifyLogin();

   $category = new Category();

   $category-> get((int)$idcategory);

   $products = new Products();

   $products ->get((int)$idproduct);
   $category-> addProduct($products);

   header("Location:/admin/categories/".$idcategory."/products");
   exit;

});
$app->get("/admin/categories/:idcategory/products/:idproduct/remove", function($idcategory, $idproduct){
    User::verifyLogin();

   $category = new Category();

   $category-> get((int)$idcategory);

   $products = new Products();

   $products ->get((int)$idproduct);
   $category-> removeProduct($products);

   header("Location:/admin/categories/".$idcategory."/products");
   exit;

});


///rotas dos produtos adm/usuario

$app->get("/admin/products",function(){
    User::verifyLogin();

    $products = Products::listAll();

    $page = new PageAdmin();

    $page ->setTpl("products",[
      "products"=>$products
    ]);


});

$app->get("/admin/products/create", function(){
   User::verifyLogin();
   
    $page = new PageAdmin();
    $page ->setTpl("products-create");


});

$app->post("/admin/products/create", function(){
   User::verifyLogin();

    $product = new Products();

    $product -> setData($_POST);

    $product ->save();

    header("Location: /admin/products");
    exit;

});

$app->get("/admin/products/:idproduct", function($idproduct){

    User::verifyLogin();

    $product = new Products();

    $product->get((int)$idproduct);

    $page = new PageAdmin();

    $page->setTpl("products-update", [
        'product'=>$product->getValues()
    ]);

});
$app->post("/admin/products/:idproduct", function($idproduct){

    User::verifyLogin();

    $product = new Products();

    $product->get((int)$idproduct);

    $product -> setData($_POST);

    $product -> save();

    $product ->setPhoto($_FILES['file']);

    header("Location: /admin/products");
    exit;
  

});
$app->get("/admin/products/:idproduct/delete", function($idproduct){

    User::verifyLogin();

    $product = new Products();

    $product->get((int)$idproduct);
      
    $product ->delete();

     header("Location: /admin/products");
    exit;
    
});



$app ->run ();


?>