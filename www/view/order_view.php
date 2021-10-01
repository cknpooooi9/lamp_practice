<?php
  // クリックジャッキング対策
  header('X-FRAME-OPTIONS: DENY');
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <?php include VIEW_PATH . 'templates/head.php'; ?>
  <title>購入履歴</title>
  <link rel="stylesheet" href="<?php print(h(STYLESHEET_PATH . 'admin.css')); ?>">
</head>
<body>
  <?php 
  include VIEW_PATH . 'templates/header_logined.php'; 
  ?>

  <div class="container">
    <h1>購入履歴</h1>

    <?php include VIEW_PATH . 'templates/messages.php'; ?>


    <?php if(count($orders) > 0){ ?>
      <table class="table table-bordered text-center">
        <thead class="thead-light">
          <tr>
            <th>注文番号</th>
            <th>購入日時</th>
            <th>合計金額</th>
            <th>購入明細</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($orders as $order){ ?>
          <tr class="<?php print(h(is_open($item) ? '' : 'close_item')); ?>">
            <td><?php print(h($order['order_id'])); ?></td>
            <td><?php print(h($order['created'])); ?></td>
            <td><?php print(h($order['total'])); ?>円</td>
            <td>
              <form method="post" action="detail.php">
                <input type="submit" value="購入明細表示">
                <input type="hidden" name="order_id" value="<?php print(h($order['order_id'])); ?>">
                <input type="hidden" name="created" value="<?php print(h($order['created'])); ?>">
                <input type="hidden" name="total" value="<?php print(h($order['total'])); ?>">
                <input type="hidden" name="token" value="<?php print h($token); ?>">
              </form>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    <?php } else { ?>
      <p>購入履歴はありません。</p>
    <?php } ?> 
  </div>
</body>
</html>