<?php
/**
 * @date: 2025/8/8 09:47
 * @author: fanxiaobin <fanxiaobin@email.cn>
 */

namespace app\components;

use Yii;
use yii\web\ErrorHandler;
use yii\web\Response;

class CustomErrorHandler extends ErrorHandler
{
    protected function renderException($exception)
    {
        $response = Yii::$app->response;
        $request = Yii::$app->request;
        if ($request->isAjax) {
            $response->format = Response::FORMAT_JSON;
            $response->data = [
                'success' => false,
                'error' => [
                    'code' => $exception->getCode(),
                    'message' => $exception->getMessage(),
                    'type' => get_class($exception),
                ]
            ];
        } else {
            $response->format = Response::FORMAT_HTML;
            parent::renderException($exception);
        }
    }
}