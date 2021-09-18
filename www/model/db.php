<?php

function get_db_connect(){
  // MySQL用のDSN文字列
  $dsn = 'mysql:dbname='. DB_NAME .';host='. DB_HOST .';charset='.DB_CHARSET;
 
  try {
    // データベースに接続
    $dbh = new PDO($dsn, DB_USER, DB_PASS, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    exit('接続できませんでした。理由：'.$e->getMessage() );
  }
  return $dbh;
}

//SQL文を実行するためのユーザ定義関数
function fetch_query($db, $sql, $params = array()){
  try{
    //プリペアドステートメント
    $statement = $db->prepare($sql);
    //取得したparamsを実行
    $statement->execute($params);
    //情報の一つを取得
    return $statement->fetch();
  }catch(PDOException $e){
    //例外処理
    set_error('データ取得に失敗しました。');
  }
  return false;
}

//SQL文を実行するためのユーザ定義関数
function fetch_all_query($db, $sql, $params = array()){
  try{
    //プリペアドステートメント
    $statement = $db->prepare($sql);
    //取得したparamsを実行
    $statement->execute($params);
    //情報をすべて取得
    return $statement->fetchAll();
  }catch(PDOException $e){
    //例外処理
    set_error('データ取得に失敗しました。');
  }
  return false;
}

//DB更新のSQL文を実行するためのユーザ定義関数
function execute_query($db, $sql, $params = array()){
  try{
    //プリペアドステートメント
    $statement = $db->prepare($sql);
    //取得したparamsを実行
    return $statement->execute($params);
  }catch(PDOException $e){
    //例外処理
    set_error('更新に失敗しました。');
  }
  return false;
}