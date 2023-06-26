<?php
/**
 * CLSession.php
 */

namespace cl\web;
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

use cl\core\CLFlag;

/**
 * Wrapper of the user session
 * Class CLSession
 * @package cl\web
 */
class CLSession
{
    private $session = null;

    public function __construct(array &$session)
    {
        $this->session = &$session;
    }

    /**
     * Returns the session object
     * @return array|null
     */
    public function &getSession(): ?array
    {
        return $this->session;
    }

    /**
     * Stores a key/value pair in the session object
     * @param $key
     * @param $value
     */
    public function put($key, $value) {
        $this->session[$key] = $value;
    }

    /**
     * Returns the value of the provided session key, if it exists, or null otherwise
     * @param $key
     */
    public function get($key, $default = null) {
        return $this->session[$key] ?? $default;
    }

    public function destroy() {
        // only really cleaning the session variables, not actually destroying the session
        $this->put(CLFlag::IS_LOGGED_IN, false);
        $this->session = array();
        session_destroy();
    }

    public function close() {
        return session_write_close();
    }

    public function getId() {
        return session_id();
    }

    public function setId($sessionId) {
        session_id($sessionId);
    }

    public function regenerateId() {
        session_regenerate_id();
    }
}
