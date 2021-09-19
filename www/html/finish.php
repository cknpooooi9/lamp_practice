<?php
//定数のファイルを読み込む
require_once '../conf/const.php';
//汎用の関数ファイルを読み込む
require_once MODEL_PATH . 'functions.php';
//userデータ用の関数ファイルを読み込む
require_once MODEL_PATH . 'user.php';
//itemデータ用の関数ファイルを読み込む
require_once MODEL_PATH . 'item.php';
//cart用の関数ファイルを読み込む
require_once MODEL_PATH . 'cart.php';

//セッション開始
session_start();

//ログインチェック
if(is_logined() === false){
  //ログインしてなければログインページにリダイレクト
  redirect_to(LOGIN_URL);
}

//PDO取得
$db = get_db_connect();
//ユーザ情報のチェック
$user = get_login_user($db);

//カートの中身を取得
$carts = get_user_carts($db, $user['user_id']);

//商品購入
if(purchase_carts($db, $carts) === false){
  //もしDBとカートの中身が一致しなければ、商品が購入できない
  set_error('商品が購入できませんでした。');
  //カートページへリダイレクト
  redirect_to(CART_URL);
} 

//カート内合計金額
$total_price = sum_carts($carts);

//トークンの生成
$token = get_csrf_token();

//ビューを読み込む
include_once '../view/finish_view.php';