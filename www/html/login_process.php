<?php
//定数のファイルを読み込む
require_once '../conf/const.php';
//汎用の関数ファイルを読み込む
require_once MODEL_PATH . 'functions.php';
//userデータ関連の関数ファイルを読み込む
require_once MODEL_PATH . 'user.php';

//セッション開始
session_start();

//トークンの照合
$token = get_post('token');
if(is_valid_csrf_token($token) === false) {
  redirect_to(LOGIN_URL);
}
unset($_SESSION['csrf_token']);

//ログインチェックの関数
if(is_logined() === true){
  //ログインしていればホームにリダイレクト
  redirect_to(HOME_URL);
}

//nameを取得
$name = get_post('name');
//passwordを取得
$password = get_post('password');

//PDOを取得
$db = get_db_connect();

//ログインしたユーザーのチェック関数
$user = login_as($db, $name, $password);
//userデータと一致しない場合、ログインに失敗した旨表示
if( $user === false){
  set_error('ログインに失敗しました。');
  //ログインページにリダイレクト
  redirect_to(LOGIN_URL);
}

//メッセージを表示
set_message('ログインしました。');
//ユーザーが管理者であった場合
if ($user['type'] === USER_TYPE_ADMIN){
  //管理用ページにリダイレクト
  redirect_to(ADMIN_URL);
}
//ホームにリダイレクト
redirect_to(HOME_URL);