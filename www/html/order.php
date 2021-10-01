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

if ($user['type'] === USER_TYPE_ADMIN){
  //adminでログインした時はすべての購入履歴を表示
  $orders = get_all_orders($db);
} else {
  //購入履歴テーブルから情報を取得($db、$userを引数に)
  $orders = get_user_orders($db, $user['user_id']);
}

//トークンの生成
$token = get_csrf_token();

//ビューを読み込む
include_once VIEW_PATH . 'order_view.php';