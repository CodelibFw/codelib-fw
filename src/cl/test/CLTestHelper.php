<?php


namespace cl\test;


use cl\core\CLBaseServiceRequest;
use cl\core\CLInstantiator;
use cl\plugin\CLBaseResponse;
use cl\util\Util;
use cl\web\CLConfig;
use cl\web\CLHttpRequest;

class CLTestHelper
{
    public static function createRequest(string $flowKey, array $postData = null, CLConfig $clconfig) {
        $server = ['REQUEST_METHOD' => 'post'];
        if ($flowKey != null) {
            $server['PATH_INFO'] = $flowKey;
        }
        $clrequest = new CLHttpRequest(null, $postData, $server, null);
        $clrequest->setAppConfig($clconfig);
        return $clrequest;
    }

    public static function createConfig(array $options = null) {
        $clconfig = new CLConfig();
        if ($options != null && count($options) > 0) {
            $clconfig->addAppConfigArray($options);
        }
        return $clconfig;
    }

    public static function createPluginInstance($pluginName, $requestedService = 'run', $clRequest = null, $clConfig = null, $clSession = null, array $mockInjections = null) {
        if ($clConfig == null) {
            $clConfig = self::createConfig();
        }
        if ($clRequest == null) {
            $clRequest = self::createRequest(null, null, $clConfig);
        }
        $clServiceRequest = new CLBaseServiceRequest($pluginName, $requestedService, $clRequest, $clConfig, $clSession);
        $pluginResponse = new CLBaseResponse();
        $extFolder = $clConfig->getAppConfig(EXTENSIONS_FOLDER, 'plugin');
        if ($mockInjections != null) {
            foreach ($mockInjections as list($key, $value, $extClass, $params)) {
                CLInstantiator::addRef($key, $value, $extClass, $params);
            }
        }
        $pluginInstance =  self::getPluginInstance($pluginName, $extFolder, $clServiceRequest, $pluginResponse);
        CLInstantiator::iocCheck($pluginInstance, $clConfig);
        return $pluginInstance;
    }

    private static function getPluginInstance($plugin, $extFolder, $clServiceRequest, $pluginResponse) {
        $appNsPrefix = self::getAppNsPrefix();
        $className = Util::getPluginClassName($plugin, $extFolder, $appNsPrefix, true);
        return CLInstantiator::classInstance($className, 'cl\contract\CLPlugin', false, $clServiceRequest, $pluginResponse);
    }

    public static function getPluginVars(object $pluginInstance): ?array {
        $pluginRC = new \ReflectionClass($pluginInstance);
        if ($pluginRC == null) return null;
        $vars = [];
        foreach ($pluginRC->getProperties() as $prop) {
            $vars[$prop->getName()] = $prop->getValue();
        }
        return $vars;
    }
    private static function getAppNsPrefix() {
        $appNs = APP_NS;
        if ($appNs == null || $appNs === '') { $appNs = 'app'; }
        return '\\'.$appNs;
    }
}
