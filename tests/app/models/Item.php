<?php
namespace phtamas\yii2\sortable\tests\app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $position
 */
class Item extends ActiveRecord
{
    public static function tableName()
    {
        return 'tbl_items';
    }

}