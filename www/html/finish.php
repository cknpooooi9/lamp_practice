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

//トークンのチェック
$token = get_post('token');
if(is_valid_csrf_token($token) === false) {
  redirect_to(LOGIN_URL);
}
unset($_SESSION['csrf_token']);

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

//トランザクション開始
$db->beginTransaction();

//購入履歴テーブルの追加関数($user、戻り値は成功したらtrue、失敗したらfalse)
if (insert_orders($db, $user['user_id']) === false) {
  $db->rollback();
  set_error('購入履歴の追加に失敗しました。');
  //カートページへリダイレクト
  redirect_to(CART_URL);
}

//lastInsertIdの取得を行う
$order_id = $db->lastInsertId();

//購入明細テーブルの追加関数($cartsとlastinsertidの値、戻り値は成功したらtrue、失敗したらfalse)
if (insert_order_details($db, $order_id, $carts) === false) {
  $db->rollback();
  set_error('購入明細情報を追加できませんでした。');
  //カートページへリダイレクト
  redirect_to(CART_URL);
}

//どちらもtrueの場合、コミット処理
$db->commit();

//トークンの生成
$token = get_csrf_token();

//ビューを読み込む
include_once '../view/finish_view.php';