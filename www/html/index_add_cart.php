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
  //ログインしてなければログインページにリダイレクト
  redirect_to(LOGIN_URL);
}

//PDO取得
$db = get_db_connect();
//ユーザ情報を取得
$user = get_login_user($db);

//POST通信でitem_idを取得
$item_id = get_post('item_id');

//user_idとitem_idを一緒に送信してカートに追加
if(add_cart($db,$user['user_id'], $item_id)){
  //完了メッセージ
  set_message('カートに商品を追加しました。');
} else {
  //失敗メッセージ
  set_error('カートの更新に失敗しました。');
}

//ホームへリダイレクト
redirect_to(HOME_URL);