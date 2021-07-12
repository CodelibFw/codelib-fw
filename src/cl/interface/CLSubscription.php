<?php
/**
 * CLSubscription.php
 */

namespace cl\contract;
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

/**
 * Defines the contract for a subscription to a particular type of notification
 * Interface CLSubscription
 * @package cl\contract
 */
interface CLSubscription
{
    const EMAIL_DELIVERY = 1;
    const DB_DELIVERY = 2;
    const CALLABLE_DELIVERY = 3;
    const REMOTE_CALLABLE_DELIVERY = 4;

    /**
     * Specifies details of this subscription
     * The details must include, via specific keys set using the CLEntity setData function:
     * - which event the subscriber requires notification for, using the 'event' key
     * - the delivery type as above (EMAIL_DELIVERY, ... etc), using the 'type' key
     * - the delivery address (an email address, a repository instance, a callable, a remote API call details),
     * using the 'address' key
     * @param CLEntity $subscriptionDetails
     * @return mixed
     */
    public function details(CLEntity $subscriptionDetails);

    public function getDetails() : CLEntity;
}
