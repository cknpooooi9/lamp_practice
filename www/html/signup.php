<?php
//定数のファイルを読み込む
require_once '../conf/const.php';
//汎用の関数ファイルを読み込む
require_once MODEL_PATH . 'functions.php';

//セッション開始
session_start();

//ログインチェック
if(is_logined() === true){
  //ホームへリダイレクト
  redirect_to(HOME_URL);
}

//ビューを読み込む
include_once VIEW_PATH . 'signup_view.php';



