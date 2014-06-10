<?php

/*
 *  Copyright 2014 Opendi Software AG
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing,
 *  software distributed under the License is distributed
 *  on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 *  either express or implied. See the License for the specific
 *  language governing permissions and limitations under the License.
 */
namespace Opendi\Solr\Client;

/**
 * Add the instance() method which returns a new instance of the called class.
 *
 * Useful for chaining method calls in builder-style interfaces.
 *
 * Instead of writing:
 * ```
 * $class = new MyClass();
 * $class->foo()
 *     ->bar()
 *     ->baz();
 * ```
 *
 * It is possible to write:
 * ```
 * MyClass::instance()
 *     ->foo()
 *     ->bar()
 *     ->baz();
 * ```
 */
trait InstanceTrait
{
    /**
     * Returns a new instance of the called class.
     *
     * @param  array $params The constuctor parameters.
     */
    public static function instance($params = [])
    {
        $class = get_called_class();
        $reflect  = new \ReflectionClass($class);
        return $reflect->newInstanceArgs($params);
    }
}
