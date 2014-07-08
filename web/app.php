<?php
/**
 * 首页入口文件
 */

if (!file_exists(__DIR__ . '/../app/data/install.lock')) {
	header("Location: install/install.php");
	exit(); 
}

use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\User\CurrentUser;

$loader = require_once __DIR__.'/../app/bootstrap.php.cache';

// Use APC for autoloading to improve performance.
// Change 'sf2' to a unique prefix in order to prevent cache key conflicts
// with other applications also using APC.
/*
$loader = new ApcClassLoader('sf2', $loader);
$loader->register(true);
*/

require_once __DIR__.'/../app/AppKernel.php';
//require_once __DIR__.'/../app/AppCache.php';

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();
//$kernel = new AppCache($kernel);
Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();

$kernel->boot();

// START: init service kernel
$serviceKernel = ServiceKernel::create($kernel->getEnvironment(), $kernel->isDebug());
$serviceKernel->setParameterBag($kernel->getContainer()->getParameterBag());
$serviceKernel->setConnection($kernel->getContainer()->get('database_connection'));

$currentUser = new CurrentUser();
$currentUser->fromArray(array(
    'id' => 0,
    'nickname' => '娓稿',
    'currentIp' =>  $request->getClientIp(),
    'roles' => array(),
));
$serviceKernel->setCurrentUser($currentUser);
// END: init service kernel

// NOTICE: 闃叉璇锋眰鎹曟崏澶辫触鑰屽仛寮傚父澶勭悊 
// 鍖呮嫭锛氭暟鎹簱杩炴帴澶辫触绛�try {
	$response = $kernel->handle($request);
} catch (\RuntimeException $e) {
    echo "Error!  ". $e->getMessage();
    die();
}

$response->send();
$kernel->terminate($request, $response);
