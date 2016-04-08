<?php
namespace phtamas\yii2\sortable\tests;

use PHPUnit_Extensions_Database_TestCase;
use phtamas\yii2\sortable\Behavior;
use phtamas\yii2\sortable\tests\app\models\Item;
use phtamas\yii2\sortable\tests\app\models\ItemWithScope;

class BehaviorTest extends PHPUnit_Extensions_Database_TestCase
{
    private $dbConnection;

    public function testAttach()
    {
        $this->setExpectedException('InvalidArgumentException');
        $behavior = new Behavior();
        $behavior->attach($this->getMock('yii\base\Component'));
    }

    public function testFindLastAvailablePositionOnInsert()
    {
        $model = new Item();
        $behavior = new Behavior();
        $behavior->positionAttribute = 'position';
        $model->attachBehavior('sortable', $behavior);
        $this->assertEquals(5, $behavior->findLastAvailablePosition());
    }

    public function testFindLastAvailablePositionOnUpdate()
    {
        $model = Item::findOne(1);
        /* @var $model Item */
        $behavior = new Behavior();
        $behavior->positionAttribute = 'position';
        $model->attachBehavior('sortable', $behavior);
        $this->assertEquals(4, $behavior->findLastAvailablePosition());
    }

    public function testInsertWithNoPosition()
    {
        $model = new Item();
        $behavior = new Behavior();
        $behavior->positionAttribute = 'position';
        $model->attachBehavior('sortable', $behavior);
        $model->save();
        $this->assertTablesEqual(
            $this->getAssertedTable('insert_with_no_position', 'tbl_items'),
            $this->createQueryTable('tbl_items')
        );
    }

    public function testInsertWithIvalidPositionType()
    {
        $model = new Item();
        $model->position = new \stdClass();
        $behavior = new Behavior();
        $behavior->positionAttribute = 'position';
        $model->attachBehavior('sortable', $behavior);
        $model->save();
        $this->assertTablesEqual(
            $this->getAssertedTable('insert_with_no_position', 'tbl_items'),
            $this->createQueryTable('tbl_items')
        );
    }

    public function testInsertWithTooSmallPosition()
    {
        $model = new Item();
        $model->position = 0;
        $behavior = new Behavior();
        $behavior->positionAttribute = 'position';
        $model->attachBehavior('sortable', $behavior);
        $model->save();
        $this->assertTablesEqual(
            $this->getAssertedTable('insert_with_no_position', 'tbl_items'),
            $this->createQueryTable('tbl_items')
        );
    }

    public function testInsertWithToBigPosition()
    {
        $model = new Item();
        $model->position = 5;
        $behavior = new Behavior();
        $behavior->positionAttribute = 'position';
        $model->attachBehavior('sortable', $behavior);
        $model->save();
        $this->assertTablesEqual(
            $this->getAssertedTable('insert_with_no_position', 'tbl_items'),
            $this->createQueryTable('tbl_items')
        );
    }

    public function testInsert()
    {
        $model = new Item();
        $model->position = 2;
        $behavior = new Behavior();
        $behavior->positionAttribute = 'position';
        $model->attachBehavior('sortable', $behavior);
        $model->save();
        $this->assertTablesEqual(
            $this->getAssertedTable('insert', 'tbl_items'),
            $this->createQueryTable('tbl_items')
        );
    }

    public function testUpdateWithNoPosition()
    {
        $model = Item::findOne(2);
        /* @var $model Item */
        $model->position = null;
        $behavior = new Behavior();
        $behavior->positionAttribute = 'position';
        $model->attachBehavior('sortable', $behavior);
        $model->beforeSave(false);
        $this->assertEquals(2, $model->position);
    }

    public function testUpdateWithInvalidPositionType()
    {
        $model = Item::findOne(2);
        /* @var $model Item */
        $model->position = new \stdClass();
        $behavior = new Behavior();
        $behavior->positionAttribute = 'position';
        $model->attachBehavior('sortable', $behavior);
        $model->beforeSave(false);
        $this->assertEquals(2, $model->position);
    }

    public function testUpdateWithTooSmallPosition()
    {
        $model = Item::findOne(2);
        /* @var $model Item */
        $model->position = 0;
        $behavior = new Behavior();
        $behavior->positionAttribute = 'position';
        $model->attachBehavior('sortable', $behavior);
        $model->beforeSave(false);
        $this->assertEquals(2, $model->position);
    }

    public function testUpdateWithTooBigPosition()
    {
        $model = Item::findOne(2);
        /* @var $model Item */
        $model->position = 5;
        $behavior = new Behavior();
        $behavior->positionAttribute = 'position';
        $model->attachBehavior('sortable', $behavior);
        $model->beforeSave(false);
        $this->assertEquals(4, $model->position);
    }

    public function testMoveUp()
    {
        $model = Item::findOne(3);
        /* @var $model Item */
        $model->position = 2;
        $behavior = new Behavior();
        $behavior->positionAttribute = 'position';
        $model->attachBehavior('sortable', $behavior);
        $model->save();
        $this->assertTablesEqual(
            $this->getAssertedTable('update', 'tbl_items'),
            $this->createQueryTable('tbl_items')
        );
    }

    public function testMoveDown()
    {
        $model = Item::findOne(2);
        /* @var $model Item */
        $model->position = 3;
        $behavior = new Behavior();
        $behavior->positionAttribute = 'position';
        $model->attachBehavior('sortable', $behavior);
        $model->save();
        $this->assertTablesEqual(
            $this->getAssertedTable('update', 'tbl_items'),
            $this->createQueryTable('tbl_items')
        );
    }

    public function testDelete()
    {
        $model = Item::findOne(2);
        /* @var $model Item */
        $behavior = new Behavior();
        $behavior->positionAttribute = 'position';
        $model->attachBehavior('sortable', $behavior);
        $model->delete();
        $this->assertTablesEqual(
            $this->getAssertedTable('delete', 'tbl_items'),
            $this->createQueryTable('tbl_items')
        );
    }

    public function testInsertWithScopeAndNoPosition()
    {
        $model = new ItemWithScope();
        $model->scope1 = 1;
        $model->scope2 = 1;
        $behavior = new Behavior();
        $behavior->positionAttribute = 'position';
        $behavior->scope = ['scope1', 'scope2'];
        $model->attachBehavior('sortable', $behavior);
        $model->save();
        $this->assertTablesEqual(
            $this->getAssertedTable('insert_with_scope_and_no_position', 'tbl_items_with_scope'),
            $this->createQueryTable('tbl_items_with_scope')
        );
    }

    public function testInsertWithScope()
    {
        $model = new ItemWithScope();
        $model->scope1 = 1;
        $model->scope2 = 1;
        $model->position = 2;
        $behavior = new Behavior();
        $behavior->positionAttribute = 'position';
        $behavior->scope = ['scope1', 'scope2'];
        $model->attachBehavior('sortable', $behavior);
        $model->save();
        $this->assertTablesEqual(
            $this->getAssertedTable('insert_with_scope', 'tbl_items_with_scope'),
            $this->createQueryTable('tbl_items_with_scope')
        );
    }

    public function testMoveUpWithScope()
    {
        $model = ItemWithScope::findOne(3);
        /* @var $model ItemWithScope */
        $model->position = 2;
        $behavior = new Behavior();
        $behavior->positionAttribute = 'position';
        $behavior->scope = ['scope1', 'scope2'];
        $model->attachBehavior('sortable', $behavior);
        $model->save();
        $this->assertTablesEqual(
            $this->getAssertedTable('update_with_scope', 'tbl_items_with_scope'),
            $this->createQueryTable('tbl_items_with_scope')
        );
    }

    public function testMoveDownWithScope()
    {
        $model = ItemWithScope::findOne(2);
        /* @var $model ItemWithScope */
        $model->position = 3;
        $behavior = new Behavior();
        $behavior->positionAttribute = 'position';
        $behavior->scope = ['scope1', 'scope2'];
        $model->attachBehavior('sortable', $behavior);
        $model->save();
        $this->assertTablesEqual(
            $this->getAssertedTable('update_with_scope', 'tbl_items_with_scope'),
            $this->createQueryTable('tbl_items_with_scope')
        );
    }

    public function testUpdateWithScopeChange()
    {
        $model = ItemWithScope::findOne(2);
        /* @var $model ItemWithScope */
        $model->position = 3;
        $model->scope2 = 2;
        $behavior = new Behavior();
        $behavior->positionAttribute = 'position';
        $behavior->scope = ['scope1', 'scope2'];
        $model->attachBehavior('sortable', $behavior);
        $model->save();
        $this->assertTablesEqual(
            $this->getAssertedTable('update_with_scope_change', 'tbl_items_with_scope'),
            $this->createQueryTable('tbl_items_with_scope')
        );
    }

    public function testDeleteWithScope()
    {
        $model = ItemWithScope::findOne(2);
        /* @var $model ItemWithScope */
        $behavior = new Behavior();
        $behavior->positionAttribute = 'position';
        $behavior->scope = ['scope1', 'scope2'];
        $model->attachBehavior('sortable', $behavior);
        $model->delete();
        $this->assertTablesEqual(
            $this->getAssertedTable('delete_with_scope', 'tbl_items_with_scope'),
            $this->createQueryTable('tbl_items_with_scope')
        );
    }

    protected function getConnection()
    {
        if (!isset($this->dbConnection)) {
            $this->dbConnection = $this->createDefaultDBConnection(\Yii::$app->db->pdo);
        }
        return $this->dbConnection;
    }

    protected function getDataSet()
    {
        return new \PHPUnit_Extensions_Database_DataSet_YamlDataSet(__DIR__ . '/fixture.yml');
    }

    protected function getAssertedTable($file, $tableName)
    {
        return (new \PHPUnit_Extensions_Database_DataSet_YamlDataSet(__DIR__ . '/assertions/' . $file . '.yml'))
            ->getTable($tableName);
    }

    protected function createQueryTable($tableName, $sql = null)
    {
        if (is_null($sql)) {
            $sql = 'SELECT * FROM ' . $tableName;
        }
        return $this->getConnection()->createQueryTable($tableName, $sql);
    }
}