<?php
/**
 * CLFlag.php
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

/**
 * Defines constants used by CL and CL Apps
 * Use these constants instead of their string value to save yourself wasting time
 * looking for errors due to typing mistakes.
 * Class CLFlag
 * @package cl\core
 */
class CLFlag
{
    const SUCCESS = 'success';
    const FAILURE = 'failure';
    const REG_PAGE = 'reg_page';
    // used by CLInjectable, CLHtmlApp and other
    const SHARED = 'shared';
    const NOT_SHARED = 'not_shared';
    // used as Session keys and other places
    const IS_LOGGED_IN = 'IsLoggedIn';
    const ROLE_ID = 'roleid';
    const USERNAME = 'username';
    // pre-defined user roles (you may create your own ones, ignore or replace these ones)
    const USER_ROLE = 1;
    const EDITOR_ROLE = 2;
    const ADMIN_ROLE = 3;
    // key used to indicate what type of output a Plugin produces, such as JSON, XML, Text, etc
    const PRODUCES = 'produces';
}
