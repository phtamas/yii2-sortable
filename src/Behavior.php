<?php
namespace phtamas\yii2\sortable;

use yii\base\Behavior as BaseBehavior;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;

/**
 * @property ActiveRecord $owner
 */
class Behavior extends BaseBehavior
{
    /** @var string */
    public $positionAttribute;

    /** @var null|string|string[] */
    public $scope;

    /**
     * @param ActiveRecord $owner
     */
    public function attach($owner)
    {
        if (!$owner instanceof ActiveRecord) {
            throw new \InvalidArgumentException(sprintf(
                'Behavior %s can only be attached to an instace of yii\db\ActiveRecord, %s given.',
                get_called_class(),
                is_object($owner) ? get_class($owner) : gettype($owner)
            ));
        }
        parent::attach($owner);
    }

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'handleBeforeInsert',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'handleBeforeUpdate',
            ActiveRecord::EVENT_BEFORE_DELETE => 'handleBeforeDelete',
        ];
    }

    /**
     * @param array $condition
     * @return int
     */
    public function findLastAvailablePosition(array $condition = null)
    {
        $owner = $this->owner;
        $query = $owner::find();
        if ($condition) {
            $query->where($condition);
        }
        $lastAvailablePosition = $query->count();
        if ($owner->isNewRecord) {
            $lastAvailablePosition ++;
        }
        return intval($lastAvailablePosition);
    }

    /**
     * @param ModelEvent $event
     */
    public function handleBeforeInsert(ModelEvent $event)
    {
        $owner = $this->owner;
        $position = $owner->getAttribute($this->positionAttribute);
        $scopeCondition = $this->scope ? $owner->getAttributes((array)$this->scope) : null;
        $lastAvailablePosition = $this->findLastAvailablePosition($scopeCondition);
        if (!$this->isInteger($position) || $position < 1 || $position > $lastAvailablePosition) {
            $position = $lastAvailablePosition;
        } else {
            $position = intval($position);
        }
        $condition = $this->quoteColumnName($this->positionAttribute) . ' >= :position';
        if ($scopeCondition) {
            $condition = ['and', $scopeCondition, $condition];
        }
        $owner::updateAllCounters(
            [$this->positionAttribute => 1],
            $condition,
            [':position' => $position]
        );
        $owner->setAttribute($this->positionAttribute, $position);
    }

    /**
     * @param ModelEvent $event
     */
    public function handleBeforeUpdate(ModelEvent $event)
    {
        $owner = $this->owner;
        $oldPosition = intval($owner->getOldAttribute($this->positionAttribute));
        $newPosition = $owner->getAttribute($this->positionAttribute);
        $lastAvailablePosition = $this->findLastAvailablePosition();
        if (!$this->isInteger($newPosition) || $newPosition < 1) {
            $newPosition = $oldPosition;
        } elseif ($newPosition > $lastAvailablePosition) {
            $newPosition = $lastAvailablePosition;
        } else {
            $newPosition = intval($newPosition);
        }
        $scopeCondition = $this->scope ? $this->owner->getAttributes((array)$this->scope) : [];
        $oldScopeCondition = $this->scope
            ? array_intersect_key($this->owner->getOldAttributes(), array_flip((array)$this->scope))
            : [];
        if (array_diff_assoc($scopeCondition, $oldScopeCondition)) {
            $owner::updateAllCounters(
                [$this->positionAttribute => -1],
                [
                    'and',
                    $oldScopeCondition,
                    $this->quoteColumnName($this->positionAttribute) . ' > :position',
                ],
                [':position' => $oldPosition]
            );
            $owner::updateAllCounters(
                [$this->positionAttribute => 1],
                [
                    'and',
                    $scopeCondition,
                    $this->quoteColumnName($this->positionAttribute) . '>= :position',
                ],
                [':position' => $newPosition]
            );
        } elseif ($newPosition < $oldPosition) {
            $condition = sprintf(
                '%1$s >= :newPosition AND %1$s < :oldPosition',
                $this->quoteColumnName($this->positionAttribute)
            );
            if ($scopeCondition) {
                $condition = ['and', $scopeCondition, $condition];
            }
            $owner::updateAllCounters(
                [$this->positionAttribute => 1],
                $condition,
                [
                    ':newPosition' => $newPosition,
                    ':oldPosition' => $oldPosition,
                ]
            );
        } elseif ($newPosition > $oldPosition) {
            $condition = sprintf(
                '%1$s <= :newPosition AND %1$s > :oldPosition',
                $this->quoteColumnName($this->positionAttribute)
            );
            if ($scopeCondition) {
                $condition = ['and', $scopeCondition, $condition];
            }
            $owner::updateAllCounters(
                [$this->positionAttribute => -1],
                $condition,
                [
                    ':newPosition' => $newPosition,
                    ':oldPosition' => $oldPosition,
                ]
            );
        }
        $owner->setAttribute($this->positionAttribute, $newPosition);
    }

    /**
     * @param ModelEvent $event
     */
    public function handleBeforeDelete(ModelEvent $event)
    {
        $owner = $this->owner;
        $position = $owner->getAttribute($this->positionAttribute);
        $condition = $this->quoteColumnName($this->positionAttribute) . ' > :position';
        if ($this->scope) {
            $condition = ['and', $owner->getAttributes((array)$this->scope), $condition];
        }
        $owner::updateAllCounters(
            [$this->positionAttribute => -1],
            $condition,
            [':position' => $position]
        );
    }

    private function isInteger($value)
    {
        return is_integer($value) || (is_string($value) && (string)intval($value) === ltrim($value, '0'));
    }

    private function quoteColumnName($columnName)
    {
        $owner = $this->owner;
        return $owner::getDb()->quoteColumnName($columnName);
    }
}