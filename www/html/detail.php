<?php
//定数のファイルを読み込む
require_once '../conf/const.php';
//汎用の関数ファイルを読み込む
require_once MODEL_PATH . 'functions.php';
//userデータ用の関数ファイルを読み込む
require_once MODEL_PATH . 'user.php';
//itemデータ用の関数ファイルを読み込む
require_once MODEL_PATH . 'item.php';
//cartデータ用の関数ファイルを読み込む
require_once MODEL_PATH . 'cart.php';

//セッション開始
session_start();

//トークンのチェック
$token = get_post('token');
if(is_valid_csrf_token($token) === false) {
  redirect_to(LOGIN_URL);
}
unset($_SESSION['csrf_token']);

//ログインチェック
if(is_logined() === false){
  //ログインしていなければログインページにリダイレクト
  redirect_to(LOGIN_URL);
}

//PDOを取得
$db = get_db_connect();

$order_id = get_post('order_id');
$created = get_post('created');
$total = get_post('total');

//購入明細テーブルから情報を取得($db、$order_idを引数に)
$details = get_detail($db, $order_id);

//ビューを読み込む
include_once VIEW_PATH . 'detail_view.php';