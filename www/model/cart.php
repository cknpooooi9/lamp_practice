<?php 
//汎用の関数ファイルを読み込む
require_once MODEL_PATH . 'functions.php';
//db接続用のファイルを読み込む
require_once MODEL_PATH . 'db.php';

//カートの中身を取得
function get_user_carts($db, $user_id){
  $sql = "
    SELECT
      items.item_id,
      items.name,
      items.price,
      items.stock,
      items.status,
      items.image,
      carts.cart_id,
      carts.user_id,
      carts.amount
    FROM
      carts
    JOIN
      items
    ON
      carts.item_id = items.item_id
    WHERE
      carts.user_id = :user_id
  ";
  //ユーザIDを取得
  return fetch_all_query($db, $sql, array(':user_id' => $user_id));
}

//ログインしているユーザのカートの中身を取得
function get_user_cart($db, $user_id, $item_id){
  $sql = "
    SELECT
      items.item_id,
      items.name,
      items.price,
      items.stock,
      items.status,
      items.image,
      carts.cart_id,
      carts.user_id,
      carts.amount
    FROM
      carts
    JOIN
      items
    ON
      carts.item_id = items.item_id
    WHERE
      carts.user_id = :user_id
    AND
      items.item_id = :item_id
  ";

  //user_idごとにカートの中身を表示
  return fetch_query($db, $sql, array(':user_id' => $user_id, ':item_id' => $item_id));

}

//商品追加
function add_cart($db, $user_id, $item_id ) {
  //カートの中身を取得
  $cart = get_user_cart($db, $user_id, $item_id);
  if($cart === false){
    //カートに同じ商品がなければ商品を新しく追加
    return insert_cart($db, $user_id, $item_id);
  }
  //同じ商品があれば数量を１つ追加する
  return update_cart_amount($db, $cart['cart_id'], $cart['amount'] + 1);
}

//カートに商品を追加
function insert_cart($db, $user_id, $item_id, $amount = 1){
  $sql = "
    INSERT INTO
      carts(
        item_id,
        user_id,
        amount
      )
    VALUES(:item_id, :user_id, :amount)
  ";

  //SQL文を実行
  return execute_query($db, $sql, array(':item_id' => $item_id, ':user_id' => $user_id, ':amount' => $amount));
}

//カート内の数量を更新する
function update_cart_amount($db, $cart_id, $amount){
  $sql = "
    UPDATE
      carts
    SET
      amount = :amount
    WHERE
      cart_id = :cart_id
    LIMIT 1
  ";
  //SQL文を実行
  return execute_query($db, $sql, array(':amount' => $amount, ':cart_id' => $cart_id));
}

//カート内商品を削除
function delete_cart($db, $cart_id){
  $sql = "
    DELETE FROM
      carts
    WHERE
      cart_id = :cart_id
    LIMIT 1
  ";

  //SQL文を実行
  return execute_query($db, $sql, array(':cart_id' => $cart_id));
}

//購入する
function purchase_carts($db, $carts){
  //バリデーションチェック
  if(validate_cart_purchase($carts) === false){
    return false;
  }
  //カート内の情報を連想配列として取得
  foreach($carts as $cart){
    //itemテーブルの更新
    if(update_item_stock(
        $db, 
        $cart['item_id'], 
        $cart['stock'] - $cart['amount']
      ) === false){
        //更新ができなければ購入失敗メッセージ
      set_error($cart['name'] . 'の購入に失敗しました。');
    }
  }
  
  //カートの中身を削除
  delete_user_carts($db, $carts[0]['user_id']);
}

//カートの中身を削除
function delete_user_carts($db, $user_id){
  $sql = "
    DELETE FROM
      carts
    WHERE
      user_id = :user_id
  ";

  //SQL文を実行
  execute_query($db, $sql, array(':user_id' => $user_id));
}


//カート内商品の金額の合計
function sum_carts($carts){
  //初期値は0円
  $total_price = 0;
  //カート内商品を連想配列として取得
  foreach($carts as $cart){
    //カート内の合計×個数が合計金額になる
    $total_price += $cart['price'] * $cart['amount'];
  }
  return $total_price;
}

//購入可能かチェック
function validate_cart_purchase($carts){
  if(count($carts) === 0){
    //カートの中身は０だったら、カートに商品が入ってないメッセージを表示
    set_error('カートに商品が入っていません。');
    return false;
  }
  //カート内商品を連想配列として取得
  foreach($carts as $cart){
    //cartデータを確認
    if(is_open($cart) === false){
      //falseなら、商品名と購入できない旨メッセージを表示
      set_error($cart['name'] . 'は現在購入できません。');
    }
    //商品在庫が０個より少ない場合
    if($cart['stock'] - $cart['amount'] < 0){
      //商品名と在庫が足りない旨と、現在の在庫数を表示
      set_error($cart['name'] . 'は在庫が足りません。購入可能数:' . $cart['stock']);
    }
  }

  //エラーチェック
  if(has_error() === true){
    return false;
  }
  return true;
}

//購入履歴テーブルの追加
function insert_orders($db, $user_id) {
  $sql = "
  INSERT INTO
    orders(
      user_id
    )
  VALUES(:user_id)
";
  return execute_query($db, $sql, array(':user_id' => $user_id));
}

//購入明細テーブルの追加
function insert_order_details($db, $order_id, $carts) {
  foreach ($carts as $cart) {
    if (insert_order_detail($db, $order_id, $cart['item_id'], $cart['amount'], $cart['price']) === false){
      return false;
    }
  }
  return true;
}

function insert_order_detail($db, $order_id, $item_id, $amount, $price) {
  $sql = "
  INSERT INTO
    order_details(
      order_id,
      item_id,
      amount,
      price
    )
  VALUES(:order_id, :item_id, :amount, :price)
";
  return execute_query($db, $sql, array(':order_id' => $order_id, 'item_id' => $item_id, 'amount' => $amount, ':price' => $price));
}