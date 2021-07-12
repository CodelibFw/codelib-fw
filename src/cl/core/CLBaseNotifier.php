<?php
/**
 * CLBaseNotifier.php
 */

namespace cl\core;
/*
 * MIT License
 *
 * Copyright Codelib Framework (https://codelibfw.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 */

use cl\contract\CLEntity;
use cl\contract\CLInjectable;
use cl\contract\CLSubscription;

/**
 * Class CLBaseNotifier
 * @package cl\core
 */
class CLBaseNotifier implements \cl\contract\CLNotifier, CLInjectable
{
    private $subscriptions = [];

    public function subscribe(CLSubscription $subscription)
    {
        $details = $subscription->getDetails();
        $event = $details->getData()['event'] ?? null;
        if ($event == null) {
            // log error
            return;
        }
        $this->subscriptions[$event] = $subscription;
    }

    public function notify(string $event, CLEntity $eventData)
    {
        if (!isset($this->subscriptions[$event])) return;
        foreach ($this->subscriptions[$event] as $subscription) {

        }
    }

    /**
     * @return array required dependencies
     * each entry is an array as well, which specifies the dependency key, optional class, optional params, and optional instantiation
     * type. Ex.:
     * return [['cache', null, CLFlag::SHARED],  // <-- requires a cache instance. CL knows about this key, so no class is required
     *         ['mysmartclass', '\app\core\Smartest.php', CLFlag::NOT_SHARED]]; // <-- requires this App class, which CL might not know about, so we tell it where to find it
     * notice that CL will use the key passed to determine the name of a setter method that will receive the instance in your Plugin class.
     * so, in the example above, it would call: setCache(cacheInstance); and setMysmartclass(smartInstance);
     */
    public function dependsOn(): array
    {
        // TODO: Implement dependsOn() method.
    }
}
