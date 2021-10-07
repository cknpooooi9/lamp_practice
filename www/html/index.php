<?php
//定数のファイルを読み込む
require_once '../conf/const.php';
//関数のファイルを読み込む
require_once MODEL_PATH . 'functions.php';
//userに関しての関数ファイルを読み込む
require_once MODEL_PATH . 'user.php';
//itemに関しての関数ファイルを読み込む
require_once MODEL_PATH . 'item.php';

//セッション開始
session_start();

//ログインチェックの関数
if(is_logined() === false){
  //ログインしてなければログイン用のURLにリダイレクト
  redirect_to(LOGIN_URL);
}

//PDOの取得
$db = get_db_connect();
//ログインユーザーのデータ取得
$user = get_login_user($db);

//送信されたデータの取得
$change = get_get('change', 'new');

//商品一覧のデータを取得
if ($change === 'new') {
  $items = get_open_items($db);
} else if ($change === 'lowprice') {
  //価格の安い順に商品を取得する関数(引数$db)
  $items = get_lowprice_items($db);
} else if ($change === 'highprice') {
  //価格の高い順に商品を取得する関数(引数$db)
  $items = get_highprice_items($db);
} 


//トークンの生成
$token = get_csrf_token();

//ビューを読み込む
include_once VIEW_PATH . 'index_view.php';