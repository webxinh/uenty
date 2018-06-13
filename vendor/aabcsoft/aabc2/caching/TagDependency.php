<?php


namespace aabc\caching;


class TagDependency extends Dependency
{
    
    public $tags = [];


    
    protected function generateDependencyData($cache)
    {
        $timestamps = $this->getTimestamps($cache, (array) $this->tags);

        $newKeys = [];
        foreach ($timestamps as $key => $timestamp) {
            if ($timestamp === false) {
                $newKeys[] = $key;
            }
        }
        if (!empty($newKeys)) {
            $timestamps = array_merge($timestamps, static::touchKeys($cache, $newKeys));
        }

        return $timestamps;
    }

    
    public function isChanged($cache)
    {
        $timestamps = $this->getTimestamps($cache, (array) $this->tags);
        return $timestamps !== $this->data;
    }

    
    public static function invalidate($cache, $tags)
    {
        $keys = [];
        foreach ((array) $tags as $tag) {
            $keys[] = $cache->buildKey([__CLASS__, $tag]);
        }
        static::touchKeys($cache, $keys);
    }

    
    protected static function touchKeys($cache, $keys)
    {
        $items = [];
        $time = microtime();
        foreach ($keys as $key) {
            $items[$key] = $time;
        }
        $cache->multiSet($items);
        return $items;
    }

    
    protected function getTimestamps($cache, $tags)
    {
        if (empty($tags)) {
            return [];
        }

        $keys = [];
        foreach ($tags as $tag) {
            $keys[] = $cache->buildKey([__CLASS__, $tag]);
        }

        return $cache->multiGet($keys);
    }
}
