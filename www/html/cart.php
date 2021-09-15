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

//ログインチェック
if(is_logined() === false){
  //ログインしていなければログインページにリダイレクト
  redirect_to(LOGIN_URL);
}

//PDOを取得
$db = get_db_connect();
//ユーザ情報を取得
$user = get_login_user($db);

//ユーザと紐づいたカート情報を取得
$carts = get_user_carts($db, $user['user_id']);

//カート内の合計金額
$total_price = sum_carts($carts);

//ビューを読み込む
include_once VIEW_PATH . 'cart_view.php';