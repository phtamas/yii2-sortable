<?php
namespace phtamas\yii2\sortable\tests\app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $position
 * @property int $scope1
 * @property int $scope2
 */
class ItemWithScope extends ActiveRecord
{
    public static function tableName()
    {
        return 'tbl_items_with_scope';
    }
}