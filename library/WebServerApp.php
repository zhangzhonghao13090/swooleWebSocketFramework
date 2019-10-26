<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/5 0005
 * Time: 17:02
 */

namespace Library;

use Library\Entity\Model\Cache\EntityRedis;
use Library\Entity\Model\DataBase\EntityMongo;
use Library\Entity\Model\DataBase\EntityMysql;
use Library\Helper\RequestHelper;
use Library\Helper\ResponseHelper;
use Library\Object\RouteObject;
use Library\Virtual\Middle\AbstractMiddleWare;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Throwable;

/**
 * Class WebServerApp
 * @package Library
 */
class WebServerApp
{
    /**
     * 初始化
     * @param int $workerId
     */
    public static function init(int $workerId)
    {
        //开启php调试模式
        if (Config::get('app.debug')) {
            error_reporting(E_ALL);
        }


        try {
            // 配置文件初始化
            Config::instanceStart();

            // Router初始化
            Router::instanceStart();

            // 数据库初始化
            EntityMysql::instanceStart($workerId);

            EntityMongo::instanceStart($workerId);

            // Redis初始化
            EntityRedis::instanceStart($workerId);
        } catch (Throwable $e) {
            echo "worker_id:{$workerId}  启动时报错  ".$e->getMessage()."\n";
            return;
        }

    }

    /**
     * 执行入口
     * @param SwooleRequest $request
     * @param SwooleResponse $response
     */
    public static function run(SwooleRequest $request, SwooleResponse $response)
    {
        //初始化请求实体类
        RequestHelper::setInstance($request);

        /* @var RouteObject $routeObject */
        $routeObject = Router::router($request->server['request_uri']);

        //初始化方法
        $methodName = $routeObject->getMethod();
        $controllerClass = $routeObject->getController();

        //初始化请求数据
        $getData = $request->get ?: [];
        $postData = $request->post ?: [];
        $requestData = array_merge($getData, $postData);

        //初始化请求中间件
        try {
            $middleClass = str_replace("Controller", "Middle", $controllerClass);;
            /* @var AbstractMiddleWare $middleWare */
            if (class_exists($middleClass)) {
                $middleWare = new $middleClass($requestData);
                if (method_exists($middleWare, $methodName)) {
                    $middleWare->$methodName();
                    $requestData = $middleWare->takeMiddleData();
                }
            }
        } catch (Throwable $e) {
            ResponseHelper::json(['msg' => $e->getMessage()]);
        }
        try {
            //初始化控制器
            if (class_exists($controllerClass)) {
                $controller = new $controllerClass($requestData);
                if (method_exists($controller, $methodName)) {
                    $returnData = $controller->$methodName();
                    if ($returnData) {
                        ResponseHelper::json($returnData);
                    }
                } else {
                    ResponseHelper::json(['msg' => "找不到{$methodName}"]);
                }
            } else {
                ResponseHelper::json(['msg' => "找不到{$controllerClass}"]);
            }
        } catch (Throwable $e) {
            if (Config::get('app.debug')) {
                $response->status(200);
                $response->end(json_encode([
                    'msg' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]));
            } else {
                $response->status(500);
                $response->end();
            }
            return;
        }

        // 支持跨域访问
        $response->status(200);
        $response->end(ResponseHelper::response());
    }
}