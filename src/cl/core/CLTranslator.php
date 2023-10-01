<?php
/**
 * CLTranslator.php
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


use cl\util\Util;
use MessageFormatter;

/**
 * Class CLTranslator
 * @package cl\core
 * Provides spoken language translation
 */
class CLTranslator
{
    private $locale, $kb;
    static protected $translations = [];

    static private function checkKB($locale = "en", $kb = "common")
    {
        if (!isset(CLTranslator::$translations[$locale])) {
            CLTranslator::$translations[$locale] = [];
            CLTranslator::$translations[$locale][$kb] = CLTranslator::loadKB($locale.'/'.$kb);
        } elseif (!isset(CLTranslator::$translations[$locale][$kb])) {
            CLTranslator::$translations[$locale][$kb] = CLTranslator::loadKB($locale.'/'.$kb);
        }
    }

    static public function translate($key, $vars = null, $locale = ['en'], $kb = "common") {
        if ($key == null || $locale == null) { return CLTranslator::replace($key, $vars, $l); }
        foreach($locale as $l) {
            CLTranslator::checkKB($l, $kb);
            if (isset(CLTranslator::$translations[$l][$kb][$key])) {
                return CLTranslator::replace(CLTranslator::$translations[$l][$kb][$key], $vars, $l);
            }
        }
        return $key;
    }

    static private function replace($translation, $vars, $locale) {
        if ($vars == null) { return $translation; }
        return MessageFormatter::formatMessage($locale, $translation, $vars);
    }

    static private function loadKB($path) {
        $path = Util::addExt($path, 'php');
        if (file_exists(APP_RES.'language/'.$path)) {
            return require APP_RES.'language/'.$path;
        } elseif (file_exists(CL_DIR . '/../resources/language/'.$path)) {
            return require CL_DIR . '/../resources/language/'.$path;
        }
        return null;
    }
}
