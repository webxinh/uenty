<?php


namespace aabc\behaviors;

use Aabc;
use Closure;
use aabc\base\Behavior;
use aabc\base\Event;
use aabc\db\ActiveRecord;


class AttributeBehavior extends Behavior
{
    
    public $attributes = [];
    
    public $value;
    
    public $skipUpdateOnClean = true;


    
    public function events()
    {
        return array_fill_keys(
            array_keys($this->attributes),
            'evaluateAttributes'
        );
    }

    
    public function evaluateAttributes($event)
    {
        if ($this->skipUpdateOnClean
            && $event->name == ActiveRecord::EVENT_BEFORE_UPDATE
            && empty($this->owner->dirtyAttributes)
        ) {
            return;
        }

        if (!empty($this->attributes[$event->name])) {
            $attributes = (array) $this->attributes[$event->name];
            $value = $this->getValue($event);
            foreach ($attributes as $attribute) {
                // ignore attribute names which are not string (e.g. when set by TimestampBehavior::updatedAtAttribute)
                if (is_string($attribute)) {
                    $this->owner->$attribute = $value;
                }
            }
        }
    }

    
    protected function getValue($event)
    {
        if ($this->value instanceof Closure || is_array($this->value) && is_callable($this->value)) {
            return call_user_func($this->value, $event);
        }

        return $this->value;
    }
}
