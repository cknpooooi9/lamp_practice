<?php
//定数のファイルを読み込む
require_once '../conf/const.php';
//汎用の関数ファイルを読み込む
require_once MODEL_PATH . 'functions.php';

//セッション開始
session_start();
//セッションデータを配列にする
$_SESSION = array();
//セッションに関する設定を取得
$params = session_get_cookie_params();
//Cookieの有効期限を過去に設定
setcookie(session_name(), '', time() - 42000,
  $params["path"], 
  $params["domain"],
  $params["secure"], 
  $params["httponly"]
);
//セッション破棄
session_destroy();

//ログインページへリダイレクト
redirect_to(LOGIN_URL);

