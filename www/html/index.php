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

//商品一覧のデータを取得
$items = get_open_items($db);

//トークンのチェック
$token = get_csrf_token();

//ビューを読み込む
include_once VIEW_PATH . 'index_view.php';