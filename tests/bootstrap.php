<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

define('YII_DEBUG', true);
define('YII_ENV', 'test');
require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';
new \yii\web\Application([
    'id' => 'phtamas-yii2-sortable',
    'basePath' => __DIR__ . '/app',

    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'sqlite:memory',
        ],
    ],
]);

Yii::$app->db->open();

Yii::$app->db->pdo->exec(<<<SQL
DROP TABLE IF EXISTS tbl_items
SQL
);

Yii::$app->db->pdo->exec(<<<SQL
CREATE TABLE tbl_items(id INTEGER PRIMARY KEY, position INTEGER)
SQL
);

Yii::$app->db->pdo->exec(<<<SQL
DROP TABLE IF EXISTS tbl_items_with_scope
SQL
);

Yii::$app->db->pdo->exec(<<<SQL
CREATE TABLE tbl_items_with_scope(id INTEGER PRIMARY KEY, position INTEGER, scope1 INTEGER, scope2 INTEGER)
SQL
);