<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\Instantiator;

use Closure;
use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Doctrine\Instantiator\Exception\UnexpectedValueException;
use Exception;
use ReflectionClass;


final class Instantiator implements InstantiatorInterface
{
    
    const SERIALIZATION_FORMAT_USE_UNSERIALIZER   = 'C';
    const SERIALIZATION_FORMAT_AVOID_UNSERIALIZER = 'O';

    
    private static $cachedInstantiators = array();

    
    private static $cachedCloneables = array();

    
    public function instantiate($className)
    {
        if (isset(self::$cachedCloneables[$className])) {
            return clone self::$cachedCloneables[$className];
        }

        if (isset(self::$cachedInstantiators[$className])) {
            $factory = self::$cachedInstantiators[$className];

            return $factory();
        }

        return $this->buildAndCacheFromFactory($className);
    }

    
    private function buildAndCacheFromFactory($className)
    {
        $factory  = self::$cachedInstantiators[$className] = $this->buildFactory($className);
        $instance = $factory();

        if ($this->isSafeToClone(new ReflectionClass($instance))) {
            self::$cachedCloneables[$className] = clone $instance;
        }

        return $instance;
    }

    
    private function buildFactory($className)
    {
        $reflectionClass = $this->getReflectionClass($className);

        if ($this->isInstantiableViaReflection($reflectionClass)) {
            return function () use ($reflectionClass) {
                return $reflectionClass->newInstanceWithoutConstructor();
            };
        }

        $serializedString = sprintf(
            '%s:%d:"%s":0:{}',
            $this->getSerializationFormat($reflectionClass),
            strlen($className),
            $className
        );

        $this->checkIfUnSerializationIsSupported($reflectionClass, $serializedString);

        return function () use ($serializedString) {
            return unserialize($serializedString);
        };
    }

    
    private function getReflectionClass($className)
    {
        if (! class_exists($className)) {
            throw InvalidArgumentException::fromNonExistingClass($className);
        }

        $reflection = new ReflectionClass($className);

        if ($reflection->isAbstract()) {
            throw InvalidArgumentException::fromAbstractClass($reflection);
        }

        return $reflection;
    }

    
    private function checkIfUnSerializationIsSupported(ReflectionClass $reflectionClass, $serializedString)
    {
        set_error_handler(function ($code, $message, $file, $line) use ($reflectionClass, & $error) {
            $error = UnexpectedValueException::fromUncleanUnSerialization(
                $reflectionClass,
                $message,
                $code,
                $file,
                $line
            );
        });

        $this->attemptInstantiationViaUnSerialization($reflectionClass, $serializedString);

        restore_error_handler();

        if ($error) {
            throw $error;
        }
    }

    
    private function attemptInstantiationViaUnSerialization(ReflectionClass $reflectionClass, $serializedString)
    {
        try {
            unserialize($serializedString);
        } catch (Exception $exception) {
            restore_error_handler();

            throw UnexpectedValueException::fromSerializationTriggeredException($reflectionClass, $exception);
        }
    }

    
    private function isInstantiableViaReflection(ReflectionClass $reflectionClass)
    {
        if (\PHP_VERSION_ID >= 50600) {
            return ! ($this->hasInternalAncestors($reflectionClass) && $reflectionClass->isFinal());
        }

        return \PHP_VERSION_ID >= 50400 && ! $this->hasInternalAncestors($reflectionClass);
    }

    
    private function hasInternalAncestors(ReflectionClass $reflectionClass)
    {
        do {
            if ($reflectionClass->isInternal()) {
                return true;
            }
        } while ($reflectionClass = $reflectionClass->getParentClass());

        return false;
    }

    
    private function getSerializationFormat(ReflectionClass $reflectionClass)
    {
        if ($this->isPhpVersionWithBrokenSerializationFormat()
            && $reflectionClass->implementsInterface('Serializable')
        ) {
            return self::SERIALIZATION_FORMAT_USE_UNSERIALIZER;
        }

        return self::SERIALIZATION_FORMAT_AVOID_UNSERIALIZER;
    }

    
    private function isPhpVersionWithBrokenSerializationFormat()
    {
        return PHP_VERSION_ID === 50429 || PHP_VERSION_ID === 50513;
    }

    
    private function isSafeToClone(ReflectionClass $reflection)
    {
        if (method_exists($reflection, 'isCloneable') && ! $reflection->isCloneable()) {
            return false;
        }

        // not cloneable if it implements `__clone`, as we want to avoid calling it
        return ! $reflection->hasMethod('__clone');
    }
}
