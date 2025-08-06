<?php
/**
 * @date: 2025/8/6 16:06
 * @author: fanxiaobin <fanxiaobin@email.cn>
 */

namespace app\widgets\layui;

use yii\helpers\Html;
use Yii;

class Breadcrumbs extends \yii\widgets\Breadcrumbs
{
    public $tag = 'span';
    public $options = ['class' => 'layui-breadcrumb'];
    public $itemTemplate = "{link}\n";
    public $activeItemTemplate = "<a><cite>{link}</cite></a>\n";

    public string $separator = '';

    public function run()
    {
        if (empty($this->links)) {
            return;
        }
        $links = [];
        if ($this->homeLink === null) {
            $links[] = $this->renderItem([
                'label' => Yii::t('yii', 'Home'),
                'url' => Yii::$app->homeUrl,
            ], $this->itemTemplate);
        } elseif ($this->homeLink !== false) {
            $links[] = $this->renderItem($this->homeLink, $this->itemTemplate);
        }
        foreach ($this->links as $link) {
            if (!is_array($link)) {
                $link = ['label' => $link];
            }
            $links[] = $this->renderItem($link, isset($link['url']) ? $this->itemTemplate : $this->activeItemTemplate);
        }

        $options = $this->options;
        if (!empty($this->separator)) {
            $options['lay-separator'] = $this->separator;
        }
        echo Html::tag($this->tag, implode('', $links), $options);
    }
}