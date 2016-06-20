<?php
/**
 * Services are globally registered in this file
 *
 * @var \Phalcon\Config $config
 */

use Phalcon\Mvc\View\Simple as View;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Di\FactoryDefault;
use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileLogger;
use Phalcon\Logger\Formatter\Line as LineFormatter;

require_once __DIR__ . "/../library/aliyun-oss-php-sdk-2.0.7/autoload.php";

use OSS\OssClient;
use OSS\Core\OssException;

$di = new FactoryDefault();

/**
 * Sets the view component
 */
$di->setShared('view', function () use ($config) {
    $view = new View();
    $view->setViewsDir($config->application->viewsDir);
    return $view;
});

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di->setShared('url', function () use ($config) {
    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);
    return $url;
});

/**
 * 注入oss client
 */
$di->setShared('ossClient', function() use ($config) {
    $endpoint = $config->alioss->endpointInternet;

    if($config->alioss->useInternetEndpoint == false){
        $endpoint = $config->alioss->endpointInner;
    }

    try {
        return new OssClient(
            $config->alioss->accessKeyId,
            $config->alioss->accessKeySecret,
            $endpoint
        );
    } catch (OssException $e) {
        echo $e->getMessage();
    }
});

/**
 * 日志
 */
$di->setShared('log', function () use ($config){
    $logger = new FileLogger($config->application->logFile);

//时间戳|应用所属系统标识|应用标识|版本标识|类名方法名行号|日志级别|认证方式|UUID|用户标识|MAC地址|[请求/响应标识]|日志详细内容
    $formatter = new LineFormatter("%date%|%type%|COUNTING|1.0|%message%");
    $formatter->setDateFormat("Y-m-d H:i:s");
    $logger->setFormatter($formatter);
    $logger->setLogLevel($config->application->logLevel);

    return $logger;
});