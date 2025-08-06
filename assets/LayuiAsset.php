<?php
/**
 * @date: 2025/8/6 10:50
 * @author: fanxiaobin <fanxiaobin@email.cn>
 */

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

class LayuiAsset extends AssetBundle
{
    public $sourcePath = '@npm/layui/dist';

    public $css = [
        'css/layui.css',
    ];

    public $js = [
        'layui.js',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
        'onload' => 'layui.use(["layer"], function(){ window.layer = layui.layer;});',
    ];
}