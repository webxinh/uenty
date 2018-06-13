<?php


namespace aabc\behaviors;

use Aabc;
use aabc\db\BaseActiveRecord;


class BlameableBehavior extends AttributeBehavior
{
    
    public $createdByAttribute = 'created_by';
    
    public $updatedByAttribute = 'updated_by';
    
    public $value;


    
    public function init()
    {
        parent::init();

        if (empty($this->attributes)) {
            $this->attributes = [
                BaseActiveRecord::EVENT_BEFORE_INSERT => [$this->createdByAttribute, $this->updatedByAttribute],
                BaseActiveRecord::EVENT_BEFORE_UPDATE => $this->updatedByAttribute,
            ];
        }
    }

    
    protected function getValue($event)
    {
        if ($this->value === null) {
            $user = Aabc::$app->get('user', false);
            return $user && !$user->isGuest ? $user->id : null;
        }

        return parent::getValue($event);
    }
}
