<?php
/**
 * CLUserPlugin.php
 */
namespace cl\plugin;
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
use cl\core\CLFlag;
use cl\messaging\email\Email;
use cl\store\CLBaseEntity;

/**
 * A simple user plugin provided by code-lib, which handles
 * user registration and login for a PHP web app
 * Functions are private, so no direct calling, only via the framework mechanism
 * Class CLUserPlugin
 * @package cl\plugin
 */
class CLUserPlugin extends CLBasePlugin implements \cl\contract\CLPlugin, CLInjectable {
    private $activeRepo;

    /**
     * Handles user registration
     * @return CLBaseResponse
     */
    protected function register() {
        $this->logger->info('processing user registration');
        if (!$this->activeRepo->connect()) {
            return $this->prepareResponse(_T('unable to connect to the database'), CLFlag::FAILURE,null, $this->clServiceRequest->getCLRequest()->getRequest());
        }
        $entity = $this->requestToUserEntity();
        if (!isset($entity)) { return $this->prepareResponse(_T('Input data seems incorrect'), CLFlag::FAILURE,null, $this->clServiceRequest->getCLRequest()->getRequest()); }
        $username = $entity->getData()['username'];
        if ($username == null) {
            return $this->prepareResponse(_T('invalid username', [$username]), CLFlag::FAILURE,null, $this->clServiceRequest->getCLRequest()->getRequest());
        }
        if ($this->activeRepo->count($entity->getEntityName(), "username = ?", array($username)) > 0) {
            return $this->prepareResponse(_T('Unfortunately this username is already in use'), CLFlag::FAILURE,null, $this->clServiceRequest->getCLRequest()->getRequest());
        }
        if ($this->activeRepo->create($entity)) {
            return $this->prepareResponse(_T('You have been successfully registered'), CLFlag::SUCCESS);
        }
        return $this->prepareResponse(_T('Unfortunately registration failed'), CLFlag::FAILURE,null, $this->clServiceRequest->getCLRequest()->getRequest());
    }

    /**
     * Handles user login
     * Expects POST data containing fields: username and password
     * @return CLBaseResponse
     */
    protected function login() {
        $this->logger->info('processing user login');
        $this->activeRepo->connect();
        $request = $this->clServiceRequest->getCLRequest()->getRequest();
        $username = $request['username'];
        $userdata = $this->activeRepo->read('user', "username = ?", array($username));
        if (!isset($userdata) || count($userdata) != 1) {
            return $this->prepareResponse(_T('Unfortunately your details were not found'), CLFlag::FAILURE, 'u.login', $this->clServiceRequest->getCLRequest()->getRequest());
        }
        if (password_verify($request['password'], $userdata[0]->getData()['password'])) {
            $login2session = $this->clServiceRequest->getCLConfig()->getAppConfig('logintosession');
            if ($login2session == null || $login2session) {
                $session = $this->clServiceRequest->getCLSession();
                $session->regenerateId();
                $session->put(CLFlag::IS_LOGGED_IN, true);
                $session->put(CLFlag::USERNAME, $username);
                $session->put(CLFlag::ROLE_ID, $userdata[0]->getData()['roleid'] ?? 1);
            }
            // notice that the payload (an array with user data) is wrapped in an array, as the method is expecting an array of payloads
            // notice also that we want to reset the view to the user dashboard, and we use the 'laf' key, within the $extraVars parameter for that
            return $this->prepareResponse(_T('Welcome back, ' . $username), CLFlag::SUCCESS, null, null, [array('user' => $userdata[0]->getData())]);
        }
        return $this->prepareResponse(_T('Unfortunately your details were not found'), CLFlag::FAILURE, 'u.login', $this->clServiceRequest->getCLRequest()->getRequest());
    }

    /**
     * Handles user logout
     */
    protected function logout() {
        $this->logger->info('processing user logout');
        $session = $this->clServiceRequest->getCLSession();
        $session->destroy();
    }

    /**
     * Password reminder (coming soon)
     */
    protected function reminder() {
        // TODO: password reminder: link valid for limited time emailed to user, if user-entered email address and
        // username, matches the existing username/email address combination.
        // Once the user presses the link, if valid link, a form is displayed to enter and confirm a new password for
        // that username. The user account will then be updated with the new password.
    }

    /**
     * Used internally by the plugin, to prepare an User entity when a login request is received
     * @return CLBaseEntity|null
     */
    private function requestToUserEntity(): ?CLBaseEntity
    {
        $entity = $this->requestToEntity('user', array('username', 'password', 'email'));
        if (isset($entity)) {
            $data = &$entity->getData();
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        return $entity;
    }

    /**
     * @param mixed $activeRepo
     * @return CLUserPlugin
     */
    public function setActiveRepo($activeRepo)
    {
        $this->activeRepo = $activeRepo;
        return $this;
    }

    /**
     * @return array with required dependencies
     */
    public function dependsOn(): array
    {
        return [CLDependency::new(ACTIVE_REPO)];
    }
}
