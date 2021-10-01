<?php
//汎用の関数ファイルを読み込む
require_once MODEL_PATH . 'functions.php';
//db接続用のファイルを読み込む
require_once MODEL_PATH . 'db.php';

// DB利用

//アイテム情報の取得
function get_item($db, $item_id){
  $sql = "
    SELECT
      item_id, 
      name,
      stock,
      price,
      image,
      status
    FROM
      items
    WHERE
      item_id = :item_id
  ";

  //DBの中から一つ取得
  return fetch_query($db, $sql, array(':item_id' => $item_id));
}

//アイテム情報の取得
function get_items($db, $is_open = false){
  $sql = '
    SELECT
      item_id, 
      name,
      stock,
      price,
      image,
      status
    FROM
      items
  ';
  //trueの場合
  if($is_open === true){
    $sql .= '
      WHERE status = 1
    ';
  }

  //DBからすべて取得
  return fetch_all_query($db, $sql);
}

//アイテム情報すべて取得
function get_all_items($db){
  return get_items($db);
}

//チェックした時、trueだった商品のみ取得
function get_open_items($db){
  return get_items($db, true);
}

//商品の新規登録
function regist_item($db, $name, $price, $stock, $status, $image){
  //新しくアップロードされたファイルの名前
  $filename = get_upload_filename($image);
  //バリデーション
  if(validate_item($name, $price, $stock, $filename, $status) === false){
    return false;
  }
  return regist_item_transaction($db, $name, $price, $stock, $status, $image, $filename);
}

//トランザクション
function regist_item_transaction($db, $name, $price, $stock, $status, $image, $filename){
  //トランザクション開始
  $db->beginTransaction();
  //商品追加且つ商品画像を保存
  if(insert_item($db, $name, $price, $stock, $filename, $status) 
    && save_image($image, $filename)){
    //コミット処理
    $db->commit();
    return true;
  }
  //ロールバック処理
  $db->rollback();
  return false;
  
}

//商品追加
function insert_item($db, $name, $price, $stock, $filename, $status){
  $status_value = PERMITTED_ITEM_STATUSES[$status];
  $sql = "
    INSERT INTO
      items(
        name,
        price,
        stock,
        image,
        status
      )
    VALUES(:name, :price, :stock, :filename, :status_value);
  ";

  return execute_query($db, $sql, array(':name' => $name, ':price' => $price, ':stock' => $stock, ':filename' => $filename, ':status_value' => $status_value));
}

//商品の公開状態を更新
function update_item_status($db, $item_id, $status){
  $sql = "
    UPDATE
      items
    SET
      status = :status
    WHERE
      item_id = :item_id
    LIMIT 1
  ";
  
  //SQL文を実行
  return execute_query($db, $sql, array(':status' => $status, ':item_id' => $item_id));
}

//商品在庫を更新
function update_item_stock($db, $item_id, $stock){
  $sql = "
    UPDATE
      items
    SET
      stock = :stock
    WHERE
      item_id = :item_id
    LIMIT 1
  ";
  
  //SQL文を実行
  return execute_query($db, $sql, array(':stock' => $stock, ':item_id' => $item_id));
}

//商品情報削除
function destroy_item($db, $item_id){
  //商品情報を取得
  $item = get_item($db, $item_id);
  if($item === false){
    return false;
  }
  //トランザクション開始
  $db->beginTransaction();
  //商品と商品画像を削除
  if(delete_item($db, $item['item_id'])
    && delete_image($item['image'])){
    //コミット処理
    $db->commit();
    return true;
  }
  //ロールバック処理
  $db->rollback();
  return false;
}

//商品データを削除
function delete_item($db, $item_id){
  $sql = "
    DELETE FROM
      items
    WHERE
      item_id = :item_id
    LIMIT 1
  ";
  
  return execute_query($db, $sql, array(':item_id' => $item_id));
}


// 非DB

function is_open($item){
  return $item['status'] === 1;
}

function validate_item($name, $price, $stock, $filename, $status){
  $is_valid_item_name = is_valid_item_name($name);
  $is_valid_item_price = is_valid_item_price($price);
  $is_valid_item_stock = is_valid_item_stock($stock);
  $is_valid_item_filename = is_valid_item_filename($filename);
  $is_valid_item_status = is_valid_item_status($status);

  return $is_valid_item_name
    && $is_valid_item_price
    && $is_valid_item_stock
    && $is_valid_item_filename
    && $is_valid_item_status;
}

function is_valid_item_name($name){
  $is_valid = true;
  if(is_valid_length($name, ITEM_NAME_LENGTH_MIN, ITEM_NAME_LENGTH_MAX) === false){
    set_error('商品名は'. ITEM_NAME_LENGTH_MIN . '文字以上、' . ITEM_NAME_LENGTH_MAX . '文字以内にしてください。');
    $is_valid = false;
  }
  return $is_valid;
}

function is_valid_item_price($price){
  $is_valid = true;
  if(is_positive_integer($price) === false){
    set_error('価格は0以上の整数で入力してください。');
    $is_valid = false;
  }
  return $is_valid;
}

function is_valid_item_stock($stock){
  $is_valid = true;
  if(is_positive_integer($stock) === false){
    set_error('在庫数は0以上の整数で入力してください。');
    $is_valid = false;
  }
  return $is_valid;
}

function is_valid_item_filename($filename){
  $is_valid = true;
  if($filename === ''){
    $is_valid = false;
  }
  return $is_valid;
}

function is_valid_item_status($status){
  $is_valid = true;
  if(isset(PERMITTED_ITEM_STATUSES[$status]) === false){
    $is_valid = false;
  }
  return $is_valid;
}

//ログインしている一般ユーザの購入履歴情報の取得（降順で表示）
function get_user_orders($db, $user_id){
  $sql = "
    SELECT
      orders.order_id,
      orders.created,
      SUM(order_details.amount * order_details.price) AS total
    FROM
      orders
    JOIN
      order_details
    ON
      orders.order_id = order_details.order_id
    WHERE
      user_id = :user_id
    GROUP BY
      order_id
    ORDER BY
      created desc
  ";

  return fetch_all_query($db, $sql, array(':user_id' => $user_id));
}

//1行だけ取得
function get_user_order($db, $user_id) {
  $sql = "
    SELECT
      orders.order_id,
      orders.created,
      SUM(order_details.amount * order_details.price) AS total
    FROM
      orders
    JOIN
      order_details
    ON
      orders.order_id = order_details.order_id
    WHERE
      user_id = :user_id
    GROUP BY
      order_id
  ";

  return fetch_query($db, $sql, array(':user_id' => $user_id));
}

function get_all_orders($db) {
  $sql = "
  SELECT
    orders.order_id,
    orders.created,
    SUM(order_details.amount * order_details.price) AS total
  FROM
    orders
  JOIN
    order_details
  ON
    orders.order_id = order_details.order_id
  GROUP BY
    order_id
  ORDER BY
    created desc
  ";

  return fetch_all_query($db, $sql);
}

function get_detail($db, $order_id) {
  $sql = "
  SELECT
    items.name,
    order_details.price,
    order_details.amount,
    order_details.amount * order_details.price AS subtotal,
    order_details.created
  FROM
    order_details
  JOIN
    items
  ON
    order_details.item_id = items.item_id
  WHERE
    order_id = :order_id
  ";

  return fetch_all_query($db, $sql, array(':order_id' => $order_id));
}