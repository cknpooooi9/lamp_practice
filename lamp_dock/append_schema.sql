-- 購入履歴テーブル --
-- 注文番号、購入日時、ユーザID、更新日 --
CREATE TABLE `orders` (
    `order_id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    primary key(order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- 購入詳細テーブル --
-- 明細番号、注文番号、商品ID、購入数、購入時の商品価格、新規作成日、更新日 --
CREATE TABLE `order_details` (
    `detail_id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `item_id` int(11) NOT NULL,
    `amount` int(11) NOT NULL,
    `price` int(11) NOT NULL,
    `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    primary key(detail_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;