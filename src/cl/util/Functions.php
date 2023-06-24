<?php
/**
 * Functions.php
 * a collection of helper functions for your Code-lib app
 */
//namespace cl\util; <-- left on global namespace on purpose, to facilitate easier use of these functions
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
use cl\core\CLTranslator;
use cl\store\CLBaseEntity;
use cl\web\CLHtmlApp;

function _T($key, $vars = null, $locale = null, $category = "common") {
    if ($locale == null) { $locale = _getLocale(); }
    if (is_string($locale)) { $locale = [$locale]; }
    return CLTranslator::translate($key, $vars, $locale, $category);
}

function _getLocale() {
    $instance = CLHtmlApp::$clapp;
    return $instance->getClrequest()->getLang();
}

function _log(string $msg, int $level=\cl\contract\CLLogger::INFO, string $type='', string $prefix="") {
    $logger = CLHtmlApp::$clapp->getCllogger();
    if ($logger != null) {
        return $logger->addLog($msg, $level, $type, $prefix);
    }
    // fall back to default log
    error_log($msg);
    return false;
}

/**
 * Finds if a strings ends with specified ending (search)
 * if $search is an array, it will return true, if the string ends with any of the strings in the array
 * @param string $str
 * @param mixed $search
 * @return bool
 */
function endsWith(string $str, $search) {
    if (!is_array($search)) {
        $lg = mb_strlen($str);
        $slg = mb_strlen($search);
        if ($lg < $slg) {
            return false;
        }
        return mb_strrpos($str, $search, $lg - $slg) !== false;
    } else {
        foreach ($search as $searchKey) {
            if (endsWith($str, $searchKey)) { return true; }
        }
        return false;
    }
}

/**
 * Finds if a strings starts with specified substring (search)
 * if $search is an array, it will return true, if the string starts with any of the strings in the array
 * @param string $str
 * @param mixed $search
 * @return bool
 */
function startsWith(string $str, $search) {
    if (!is_array($search)) {
        if (mb_strlen($str) < mb_strlen($search)) {
            return false;
        }
        return mb_strpos($str, $search) === 0;
    } else {
        foreach ($search as $searchKey) {
            if (startsWith($str, $searchKey)) { return true; }
        }
        return false;
    }
}

function isTrue($var) {
    return $var != null && is_bool($var) && $var;
}

/**
 * Checks if the Logged In user has the requested role or roles (priviledges)
 * if role ends in plus, for instance 3+, then check if user has 3 or higher access level (role)
 * @param mixed $role role or roles to check (if array)
 * @param \cl\web\CLSession $clsession current user session
 * @return bool
 */
function greenLight($role, $clsession) {
    if ($role == null) { return true; }
    if ($clsession == null) { return false; }
    if (!is_array($role)) {
        $rolePlus = false;
        if (endsWith($role, '+')) {
            $rolePlus = true;
            $role = substr($role, 0, strlen($role) - 1);
        }
        if (!isTrue($clsession->get(CLFlag::IS_LOGGED_IN)) || $clsession->get(CLFlag::ROLE_ID) == null) {
            return false;
        }
        return ($role === $clsession->get(CLFlag::ROLE_ID) || ($rolePlus && $clsession->get(CLFlag::ROLE_ID) > intval($role)));
    } else {
        foreach ($role as $userrole) {
            if (greenLight($userrole, $clsession)) { return true; }
        }
        return false;
    }
}

/**
 * Populates an entity with data coming in a request
 * (Convenience function for Plugins not extending CLBasePlugin)
 * @param string $entityName name of the repository entity
 * @param array $fieldKeys keys to be populated in the entity (for instance, column names of a database table)
 * @param array|null $optional (optional) an array to indicate which fields are optional, to avoid validation errors. Ex. array('surname' => 1, 'age' => 1)
 *        [do not add any required field here.]
 * @param array|null $formNames (optional) an associative array to map store fields to form names, when they do not match. Ex. array('username' => 'user_name')
 * @return CLBaseEntity returns a populated entity
 * @throws Exception if the value for a required field is not in the request
 */
function requestToEntity(string $entityName, array $fieldKeys, \cl\contract\CLRequest $request, array $optional = null, array $formNames = null) {
    $clrequest = $request->getRequest();
    $entity = new CLBaseEntity($entityName);
    $data = array();
    foreach($fieldKeys as $key) {
        $name = (isset($formNames) && isset($formNames[$key])) ? $formNames[$key] : $key;
        if (isset($clrequest[$name])) {
            $data[$key] = $clrequest[$name];
        } elseif (isset($optional) && !isset($optional[$name])) {
             throw new \Exception($name. ' is required, but it was not provided');
        }
    }
    $entity->setData($data);
    return $entity;
}

/**
 * Loads and parses as json the contents of a file
 * @param string $filepath absolute path to the file to load
 * @param bool $throwOnError if true, will throw an Exception on failure. Defaults to false
 * @return array|null a json object or null
 * @throws Exception
 */
function loadJson(string $filepath, bool $throwOnError = false): ?array
{
    if (file_exists($filepath)) {
        $fm = file_get_contents($filepath);
        if ($fm == null) { return null; }
        $json = json_decode($fm, true);
        $error = json_last_error();
        if ($error != JSON_ERROR_NONE) {
            if ($throwOnError) {
                throw new Exception("JSON Parse error for file: $filepath: ".json_last_error_msg());
            } else {
                _log("JSON Parse error for file: $filepath: ".json_last_error_msg(), \cl\contract\CLLogger::ERROR);
            }
        }
        if ($json == null) { return null; }
        return $json;
    }
    if ($throwOnError) {
        throw new Exception("File not found: $filepath");
    } else {
        _log("File not found: $filepath", \cl\contract\CLLogger::ERROR);
    }
    return null;
}

function makedir($path, $mode, $recursive) {
    if (!mkdir($path, $mode, $recursive)) {
        throw new Exception('Unable to create folder: '.$path);
    }
}
