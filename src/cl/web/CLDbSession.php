<?php


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

use cl\contract\CLInjectable;
use cl\core\CLDependency;
use cl\store\CLBaseEntity;
use DateTime;
use SessionHandlerInterface;

class CLDbSession implements SessionHandlerInterface, CLInjectable
{
    private $activeRepo;

    /**
     * Close the session
     * @link https://php.net/manual/en/sessionhandlerinterface.close.php
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4
     */
    public function close()
    {
        return true;
    }

    /**
     * Destroy a session
     * @link https://php.net/manual/en/sessionhandlerinterface.destroy.php
     * @param string $session_id The session ID being destroyed.
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4
     */
    public function destroy($session_id)
    { error_log('destroying session');
        return session_destroy();
    }

    /**
     * Cleanup old sessions
     * @link https://php.net/manual/en/sessionhandlerinterface.gc.php
     * @param int $maxlifetime <p>
     * Sessions that have not updated for
     * the last maxlifetime seconds will be removed.
     * </p>
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4
     */
    public function gc($maxlifetime)
    {
        $expired = ($date = new DateTime())->getTimestamp() - $maxlifetime;
        return $this->activeRepo->delete('clsession', "logintime <= ?", array($expired));
    }

    /**
     * Initialize session
     * @link https://php.net/manual/en/sessionhandlerinterface.open.php
     * @param string $save_path The path where to store/retrieve the session.
     * @param string $name The session name.
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4
     */
    public function open($save_path, $name)
    {
        return $this->activeRepo->connect();
    }

    /**
     * Read session data
     * @link https://php.net/manual/en/sessionhandlerinterface.read.php
     * @param string $session_id The session id to read data for.
     * @return string <p>
     * Returns an encoded string of the read data.
     * If nothing was read, it must return an empty string.
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4
     */
    public function read($session_id)
    {
        $session = $this->activeRepo->read('clsession', "sessionid = ?", array($session_id));
        return ($session == null || count($session) == 0) ? '' : base64_decode($session[0]->getData()['data']);
    }

    /**
     * Write session data
     * @link https://php.net/manual/en/sessionhandlerinterface.write.php
     * @param string $session_id The session id.
     * @param string $session_data <p>
     * The encoded session data. This data is the
     * result of the PHP internally encoding
     * the $_SESSION superglobal to a serialized
     * string and passing it as this parameter.
     * Please note sessions use an alternative serialization method.
     * </p>
     * @return bool <p>
     * The return value (usually TRUE on success, FALSE on failure).
     * Note this value is returned internally to PHP for processing.
     * </p>
     * @since 5.4
     */
    public function write($session_id, $session_data)
    {
        $entity = new CLBaseEntity('clsession');
        $entity->setData(['sessionid' => $session_id, 'data' => base64_encode($session_data)]);
        $session = $this->activeRepo->read('clsession', "sessionid = ?", array($session_id));
        if ($session != null && count($session) == 1) {
            $entity->setId($session[0]->getData()['id']);
            return $this->activeRepo->update($entity);
        } else {
            return $this->activeRepo->create($entity);
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
        return [CLDependency::new(ACTIVE_REPO)];
    }

    public function setActiveRepo($activeRepo)
    {
        $this->activeRepo = $activeRepo;
        return $this;
    }
}
