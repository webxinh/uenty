<?php


namespace aabc\web;

use Aabc;
use aabc\base\Component;
use aabc\base\InvalidConfigException;
use aabc\base\InvalidParamException;


class Session extends Component implements \IteratorAggregate, \ArrayAccess, \Countable
{
    
    public $flashParam = '__flash';
    
    public $handler;

    
    private $_cookieParams = ['httponly' => true];


    
    public function init()
    {
        parent::init();
        register_shutdown_function([$this, 'close']);
        if ($this->getIsActive()) {
            Aabc::warning('Session is already started', __METHOD__);
            $this->updateFlashCounters();
        }
    }

    
    public function getUseCustomStorage()
    {
        return false;
    }

    
    public function open()
    {
        if ($this->getIsActive()) {
            return;
        }

        $this->registerSessionHandler();

        $this->setCookieParamsInternal();

        @session_start();

        if ($this->getIsActive()) {
            Aabc::info('Session started', __METHOD__);
            $this->updateFlashCounters();
        } else {
            $error = error_get_last();
            $message = isset($error['message']) ? $error['message'] : 'Failed to start session.';
            Aabc::error($message, __METHOD__);
        }
    }

    
    protected function registerSessionHandler()
    {
        if ($this->handler !== null) {
            if (!is_object($this->handler)) {
                $this->handler = Aabc::createObject($this->handler);
            }
            if (!$this->handler instanceof \SessionHandlerInterface) {
                throw new InvalidConfigException('"' . get_class($this) . '::handler" must implement the SessionHandlerInterface.');
            }
            AABC_DEBUG ? session_set_save_handler($this->handler, false) : @session_set_save_handler($this->handler, false);
        } elseif ($this->getUseCustomStorage()) {
            if (AABC_DEBUG) {
                session_set_save_handler(
                    [$this, 'openSession'],
                    [$this, 'closeSession'],
                    [$this, 'readSession'],
                    [$this, 'writeSession'],
                    [$this, 'destroySession'],
                    [$this, 'gcSession']
                );
            } else {
                @session_set_save_handler(
                    [$this, 'openSession'],
                    [$this, 'closeSession'],
                    [$this, 'readSession'],
                    [$this, 'writeSession'],
                    [$this, 'destroySession'],
                    [$this, 'gcSession']
                );
            }
        }
    }

    
    public function close()
    {
        if ($this->getIsActive()) {
            AABC_DEBUG ? session_write_close() : @session_write_close();
        }
    }

    
    public function destroy()
    {
        if ($this->getIsActive()) {
            $sessionId = session_id();
            $this->close();
            $this->setId($sessionId);
            $this->open();
            session_unset();
            session_destroy();
            $this->setId($sessionId);
        }
    }

    
    public function getIsActive()
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    private $_hasSessionId;

    
    public function getHasSessionId()
    {
        if ($this->_hasSessionId === null) {
            $name = $this->getName();
            $request = Aabc::$app->getRequest();
            if (!empty($_COOKIE[$name]) && ini_get('session.use_cookies')) {
                $this->_hasSessionId = true;
            } elseif (!ini_get('session.use_only_cookies') && ini_get('session.use_trans_sid')) {
                $this->_hasSessionId = $request->get($name) != '';
            } else {
                $this->_hasSessionId = false;
            }
        }

        return $this->_hasSessionId;
    }

    
    public function setHasSessionId($value)
    {
        $this->_hasSessionId = $value;
    }

    
    public function getId()
    {
        return session_id();
    }

    
    public function setId($value)
    {
        session_id($value);
    }

    
    public function regenerateID($deleteOldSession = false)
    {
        if ($this->getIsActive()) {
            // add @ to inhibit possible warning due to race condition
            // https://github.com/aabcsoft/aabc2/pull/1812
            if (AABC_DEBUG && !headers_sent()) {
                session_regenerate_id($deleteOldSession);
            } else {
                @session_regenerate_id($deleteOldSession);
            }
        }
    }

    
    public function getName()
    {
        return session_name();
    }

    
    public function setName($value)
    {
        session_name($value);
    }

    
    public function getSavePath()
    {
        return session_save_path();
    }

    
    public function setSavePath($value)
    {
        $path = Aabc::getAlias($value);
        if (is_dir($path)) {
            session_save_path($path);
        } else {
            throw new InvalidParamException("Session save path is not a valid directory: $value");
        }
    }

    
    public function getCookieParams()
    {
        return array_merge(session_get_cookie_params(), array_change_key_case($this->_cookieParams));
    }

    
    public function setCookieParams(array $value)
    {
        $this->_cookieParams = $value;
    }

    
    private function setCookieParamsInternal()
    {
        $data = $this->getCookieParams();
        if (isset($data['lifetime'], $data['path'], $data['domain'], $data['secure'], $data['httponly'])) {
            session_set_cookie_params($data['lifetime'], $data['path'], $data['domain'], $data['secure'], $data['httponly']);
        } else {
            throw new InvalidParamException('Please make sure cookieParams contains these elements: lifetime, path, domain, secure and httponly.');
        }
    }

    
    public function getUseCookies()
    {
        if (ini_get('session.use_cookies') === '0') {
            return false;
        } elseif (ini_get('session.use_only_cookies') === '1') {
            return true;
        } else {
            return null;
        }
    }

    
    public function setUseCookies($value)
    {
        if ($value === false) {
            ini_set('session.use_cookies', '0');
            ini_set('session.use_only_cookies', '0');
        } elseif ($value === true) {
            ini_set('session.use_cookies', '1');
            ini_set('session.use_only_cookies', '1');
        } else {
            ini_set('session.use_cookies', '1');
            ini_set('session.use_only_cookies', '0');
        }
    }

    
    public function getGCProbability()
    {
        return (float) (ini_get('session.gc_probability') / ini_get('session.gc_divisor') * 100);
    }

    
    public function setGCProbability($value)
    {
        if ($value >= 0 && $value <= 100) {
            // percent * 21474837 / 2147483647 ≈ percent * 0.01
            ini_set('session.gc_probability', floor($value * 21474836.47));
            ini_set('session.gc_divisor', 2147483647);
        } else {
            throw new InvalidParamException('GCProbability must be a value between 0 and 100.');
        }
    }

    
    public function getUseTransparentSessionID()
    {
        return ini_get('session.use_trans_sid') == 1;
    }

    
    public function setUseTransparentSessionID($value)
    {
        ini_set('session.use_trans_sid', $value ? '1' : '0');
    }

    
    public function getTimeout()
    {
        return (int) ini_get('session.gc_maxlifetime');
    }

    
    public function setTimeout($value)
    {
        ini_set('session.gc_maxlifetime', $value);
    }

    
    public function openSession($savePath, $sessionName)
    {
        return true;
    }

    
    public function closeSession()
    {
        return true;
    }

    
    public function readSession($id)
    {
        return '';
    }

    
    public function writeSession($id, $data)
    {
        return true;
    }

    
    public function destroySession($id)
    {
        return true;
    }

    
    public function gcSession($maxLifetime)
    {
        return true;
    }

    
    public function getIterator()
    {
        $this->open();
        return new SessionIterator;
    }

    
    public function getCount()
    {
        $this->open();
        return count($_SESSION);
    }

    
    public function count()
    {
        return $this->getCount();
    }

    
    public function get($key, $defaultValue = null)
    {
        $this->open();
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $defaultValue;
    }

    
    public function set($key, $value)
    {
        $this->open();
        $_SESSION[$key] = $value;
    }

    
    public function remove($key)
    {
        $this->open();
        if (isset($_SESSION[$key])) {
            $value = $_SESSION[$key];
            unset($_SESSION[$key]);

            return $value;
        } else {
            return null;
        }
    }

    
    public function removeAll()
    {
        $this->open();
        foreach (array_keys($_SESSION) as $key) {
            unset($_SESSION[$key]);
        }
    }

    
    public function has($key)
    {
        $this->open();
        return isset($_SESSION[$key]);
    }

    
    protected function updateFlashCounters()
    {
        $counters = $this->get($this->flashParam, []);
        if (is_array($counters)) {
            foreach ($counters as $key => $count) {
                if ($count > 0) {
                    unset($counters[$key], $_SESSION[$key]);
                } elseif ($count == 0) {
                    $counters[$key]++;
                }
            }
            $_SESSION[$this->flashParam] = $counters;
        } else {
            // fix the unexpected problem that flashParam doesn't return an array
            unset($_SESSION[$this->flashParam]);
        }
    }

    
    public function getFlash($key, $defaultValue = null, $delete = false)
    {
        $counters = $this->get($this->flashParam, []);
        if (isset($counters[$key])) {
            $value = $this->get($key, $defaultValue);
            if ($delete) {
                $this->removeFlash($key);
            } elseif ($counters[$key] < 0) {
                // mark for deletion in the next request
                $counters[$key] = 1;
                $_SESSION[$this->flashParam] = $counters;
            }

            return $value;
        } else {
            return $defaultValue;
        }
    }

    
    public function getAllFlashes($delete = false)
    {
        $counters = $this->get($this->flashParam, []);
        $flashes = [];
        foreach (array_keys($counters) as $key) {
            if (array_key_exists($key, $_SESSION)) {
                $flashes[$key] = $_SESSION[$key];
                if ($delete) {
                    unset($counters[$key], $_SESSION[$key]);
                } elseif ($counters[$key] < 0) {
                    // mark for deletion in the next request
                    $counters[$key] = 1;
                }
            } else {
                unset($counters[$key]);
            }
        }

        $_SESSION[$this->flashParam] = $counters;

        return $flashes;
    }

    
    public function setFlash($key, $value = true, $removeAfterAccess = true)
    {
        $counters = $this->get($this->flashParam, []);
        $counters[$key] = $removeAfterAccess ? -1 : 0;
        $_SESSION[$key] = $value;
        $_SESSION[$this->flashParam] = $counters;
    }

    
    public function addFlash($key, $value = true, $removeAfterAccess = true)
    {
        $counters = $this->get($this->flashParam, []);
        $counters[$key] = $removeAfterAccess ? -1 : 0;
        $_SESSION[$this->flashParam] = $counters;
        if (empty($_SESSION[$key])) {
            $_SESSION[$key] = [$value];
        } else {
            if (is_array($_SESSION[$key])) {
                $_SESSION[$key][] = $value;
            } else {
                $_SESSION[$key] = [$_SESSION[$key], $value];
            }
        }
    }

    
    public function removeFlash($key)
    {
        $counters = $this->get($this->flashParam, []);
        $value = isset($_SESSION[$key], $counters[$key]) ? $_SESSION[$key] : null;
        unset($counters[$key], $_SESSION[$key]);
        $_SESSION[$this->flashParam] = $counters;

        return $value;
    }

    
    public function removeAllFlashes()
    {
        $counters = $this->get($this->flashParam, []);
        foreach (array_keys($counters) as $key) {
            unset($_SESSION[$key]);
        }
        unset($_SESSION[$this->flashParam]);
    }

    
    public function hasFlash($key)
    {
        return $this->getFlash($key) !== null;
    }

    
    public function offsetExists($offset)
    {
        $this->open();

        return isset($_SESSION[$offset]);
    }

    
    public function offsetGet($offset)
    {
        $this->open();

        return isset($_SESSION[$offset]) ? $_SESSION[$offset] : null;
    }

    
    public function offsetSet($offset, $item)
    {
        $this->open();
        $_SESSION[$offset] = $item;
    }

    
    public function offsetUnset($offset)
    {
        $this->open();
        unset($_SESSION[$offset]);
    }
}
