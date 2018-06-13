<?php


namespace aabc\widgets;

use Aabc;
use aabc\base\Widget;
use aabc\caching\Cache;
use aabc\caching\Dependency;
use aabc\di\Instance;


class FragmentCache extends Widget
{
    
    public $cache = 'cache';
    
    public $duration = 0; // Khong bao gio het han
    
    public $dependency;
    
    public $variations;
    
    public $enabled = true;
    
    public $dynamicPlaceholders;

    public $tuyen_delete = '';

    
    public function init()
    {
        parent::init();

        $this->cache = $this->enabled ? Instance::ensure($this->cache, Cache::className()) : null;

        if ($this->cache instanceof Cache && $this->getCachedContent() === false) {
            $this->getView()->cacheStack[] = $this;
            ob_start();
            ob_implicit_flush(false);
        }
    }

    
    public function run()
    {
        if (($content = $this->getCachedContent()) !== false) {
            echo $content;
        } elseif ($this->cache instanceof Cache) {
            array_pop($this->getView()->cacheStack);
            
            $content = ob_get_clean();
            if ($content === false || $content === '') {
                return;
            }
            if (is_array($this->dependency)) {
                $this->dependency = Aabc::createObject($this->dependency);
            }
            $data = [$content, $this->dynamicPlaceholders];
            $this->cache->set($this->calculateKey(), $data, $this->duration, $this->dependency);

            if (empty($this->getView()->cacheStack) && !empty($this->dynamicPlaceholders)) {
                $content = $this->updateDynamicContent($content, $this->dynamicPlaceholders);
            }
            echo $content;
        }
    }

    
    private $_content;


    public function clear($id = ''){
        $properties['id'] = $id;
        $properties['tuyen_delete'] = 'xoa';        
        FragmentCache::begin($properties);
    }
    
    public function getCachedContent()
    {     
        if ($this->_content === null) {
            $this->_content = false;
            if ($this->cache instanceof Cache) {
                $key = $this->calculateKey();

                //Tuyền: neews có truyền vào biến tuyen_delete thì xóa cache và return true;
                if(!empty($this->tuyen_delete)) {
                    $this->cache->delete($key);
                    return true;
                }

                $data = $this->cache->get($key);
                if (is_array($data) && count($data) === 2) {
                    list ($content, $placeholders) = $data;
                    if (is_array($placeholders) && count($placeholders) > 0) {
                        if (empty($this->getView()->cacheStack)) {
                            // outermost cache: replace placeholder with dynamic content
                            $content = $this->updateDynamicContent($content, $placeholders);
                        }
                        foreach ($placeholders as $name => $statements) {
                            $this->getView()->addDynamicPlaceholder($name, $statements);
                        }
                    }
                    $this->_content = $content;
                }
            }
        }

        return $this->_content;
    }

    
    protected function updateDynamicContent($content, $placeholders)
    {
        foreach ($placeholders as $name => $statements) {
            $placeholders[$name] = $this->getView()->evaluateDynamicContent($statements);
        }

        return strtr($content, $placeholders);
    }

    
    protected function calculateKey()
    {
        $factors = [__CLASS__, $this->getId()];
        if (is_array($this->variations)) {
            foreach ($this->variations as $factor) {
                $factors[] = $factor;
            }
        }

        return $factors;
    }
}
