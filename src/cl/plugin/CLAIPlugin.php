<?php

namespace cl\plugin;

use cl\contract\CLInjectable;
use cl\error\CLAppConfigException;
use cl\util\traits\HttpClientIocHelper;

class CLAIPlugin extends CLBasePlugin implements CLInjectable
{
    use HttpClientIocHelper;

    private $aiApi = [];

    protected function sendAiApiRequest($request) {
        try {
            $this->initAIConfig();
            $url = isset($request['url']) ? $request['url'] : $this->aiApi['url'];
            if (isset($request['uri'])) { $url .= $request['uri']; }
            $response = $this->httpclient->send(
                (new \cl\web\CLBasicHttpClientRequest($url, HTTP_JSON))
                    ->addHeader('Content-Type', 'application/json')
                    ->addHeader('Authorization', 'Bearer '.$this->aiApi['accessKey'])
                    ->setData($request['data'])
            );
            $this->pluginResponse->setVar('aiResponse', $response);
        } catch (CLAppConfigException $e) {
            _log('The AI API has not been properly configured for this Application');
            $this->pluginResponse->addVars(['feedbacktitle' => _T('Something is not right'), 'feedbackcolor' => 'red']);
            $this->pluginResponse->setVar('feedback', _T('Unfortunately we were unable to handle your request'));
            $this->pluginResponse->setVar('page', 'feedback');
        }
        return $this->pluginResponse;
    }

    /**
     * @throws \cl\error\CLAppConfigException
     */
    protected function initAIConfig() {
        $this->aiApi = ['provider' => $this->getAppConfig(AI_API_PROVIDER) ?? $this->configError(),
            'url' => $this->getAppConfig(AI_API_URL) ?? $this->configError(),
            'accessKey' => $this->getAppConfig(AI_API_KEY) ?? $this->configError()];
    }

    /**
     * @throws \cl\error\CLAppConfigException
     */
    protected function configError() {
        throw new \cl\error\CLAppConfigException();
    }
}
