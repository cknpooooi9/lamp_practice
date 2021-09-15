<?php
//定数のファイルを読み込む
require_once '../conf/const.php';
//汎用の関数ファイルを読み込む
require_once MODEL_PATH . 'functions.php';
//userデータ用の関数ファイルを読み込む
require_once MODEL_PATH . 'user.php';

//セッション開始
session_start();
//ログインチェック関数
if(is_logined() === true){
  //ログインしていればホームへリダイレクト
  redirect_to(HOME_URL);
}

//POST通信で受け取ったname
$name = get_post('name');
//POST通信で受け取ったパスワード
$password = get_post('password');
//パスワードのバリデーション
$password_confirmation = get_post('password_confirmation');

//PDOを取得
$db = get_db_connect();

//ユーザ登録
try{
  //入力項目の確認
  $result = regist_user($db, $name, $password, $password_confirmation);
  //falseならユーザ登録失敗
  if( $result=== false){
    //エラーメッセージ
    set_error('ユーザー登録に失敗しました。');
    //ユーザ登録ページにリダイレクト
    redirect_to(SIGNUP_URL);
  }
  //データベースに接続失敗した場合例外処理
}catch(PDOException $e){
  //エラーメッセージ
  set_error('ユーザー登録に失敗しました。');
  //ユーザ登録ページにリダイレクト
  redirect_to(SIGNUP_URL);
}

//ユーザ登録完了メッセージ
set_message('ユーザー登録が完了しました。');
//ログインする
login_as($db, $name, $password);
//ホームへリダイレクト
redirect_to(HOME_URL);