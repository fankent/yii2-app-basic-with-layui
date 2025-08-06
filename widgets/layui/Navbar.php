<?php
/**
 * @date: 2025/8/6 10:56
 * @author: fanxiaobin <fanxiaobin@email.cn>
 */

namespace app\widgets\layui;

use app\assets\LayuiAsset;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use Yii;

/**
 *
 * For example:
 * ```php
 * echo Navbar::widget([
 *      'theme' => 'cyan',
 *      'items' => [
 *          ['label' => 'Home', 'url' => ['site/home']],
 *          ['label' => 'System', 'items' => [
 *              ['label' => 'User', 'url' => ['system/user']]
 *              ['label' => 'Setting', 'url' => ['system/setting']]
 *          ]],
 *          ['label' => 'Logout', 'url' => ['site/logout'], 'linkOptions' => ['data-method' => 'post']]
 *      ]
 * ]);
 * ```
 */
class Navbar extends Widget
{
    public array $items = [];
    public string $theme = '';
    public bool $encodeLabels = true;
    public bool $activateItems = true;
    public bool $activateParents = true;

    public array $options = ['class' => 'layui-nav'];

    public $route = null;
    public $params = null;

    public function init()
    {
        parent::init();
        if ($this->route === null && Yii::$app->controller !== null) {
            $this->route = Yii::$app->controller->getRoute();
        }
        if ($this->params === null) {
            $this->params = Yii::$app->request->getQueryParams();
        }
    }

    public function run(): string
    {
        LayuiAsset::register($this->getView());

        return $this->renderItems();
    }

    public function renderItems(): string
    {
        $items = [];
        foreach ($this->items as $item) {
            if (isset($item['visible']) && !$item['visible']) {
                continue;
            }
            // 一级菜单
            $items[] = $this->renderItem($item);
        }
        // 主题设置
        if ($this->theme) {
            $themeClass = $this->getThemeClass($this->theme);
            Html::addCssClass($this->options, ['class' => $themeClass]);
        }
        return Html::tag('ul', implode("\n", $items), $this->options);
    }

    public function renderItem($item)
    {
        if (is_string($item)) {
            return $item;
        }
        if (!isset($item['label'])) {
            throw new InvalidConfigException("The 'label' option is required.");
        }
        $encodeLabel = $item['encode'] ?? $this->encodeLabels;
        $label = $encodeLabel ? Html::encode($item['label']) : $item['label'];
        $items = ArrayHelper::getValue($item, 'items', '');  // 下拉选项
        $linkOptions = ArrayHelper::getValue($item, 'linkOptions', []);
        $url = ArrayHelper::getValue($item, 'url', '#');
        $active = $this->isItemActive($item);

        $options = ['class' => 'layui-nav-item'];
        if (is_array($items)) {
            $url = 'javascript:void(0)';
            $items = $this->isChildActive($items, $active);
            $items = $this->renderDropdown($items, $item);
        }
        // 开启了菜单高亮则添加高亮类
        if ($this->activateItems && $active) {
            Html::addCssClass($options, ['class' => 'layui-this']);
        }

        return Html::tag('li', Html::a($label, $url, $linkOptions) . $items, $options);
    }

    protected function renderDropdown($items, $item): string
    {
        if (is_string($item)) {
            return $item;
        }
        $options = ['class' => 'layui-nav-child'];
        $content = '';
        foreach ($items as $item) {
            $itemOptions = $this->isItemActive($item) ? ['class' => 'layui-this'] : [];
            if (is_string($item)) {
                $content .= Html::tag('dd', $item);
            } else {
                $encodeLabel = $item['encode'] ?? $this->encodeLabels;
                $label = $encodeLabel ? Html::encode($item['label']) : $item['label'];
                $url = ArrayHelper::getValue($item, 'url', '#');
                $content .= Html::tag('dd', Html::a($label, $url), $itemOptions);
            }
        }
        return Html::tag('dl', $content, $options);
    }

    protected function isItemActive($item): bool
    {
        if (!$this->activateItems) {
            return false;
        }
        if (isset($item['active'])) {
            return (bool)ArrayHelper::getValue($item, 'active', false);
        }
        if (isset($item['url'][0]) && is_array($item['url'])) {
            $route = $item['url'][0];
            if ($route[0] !== '/' && Yii::$app->controller) {
                $route = Yii::$app->controller->module->getUniqueId() . '/' . $route;
            }
            if (ltrim($route, '/') !== $this->route) {
                return false;
            }
            unset($item['url']['#']);
            if (count($item['url']) > 1) {
                $params = $item['url'];
                unset($params[0]);
                foreach ($params as $name => $value) {
                    if ($value !== null && (!isset($this->params[$name]) || $this->params[$name] != $value)) {
                        return false;
                    }
                }
            }

            return true;
        }
        return false;
    }

    protected function isChildActive(array $items, bool &$active): array
    {
        foreach ($items as $i => $child) {
            if (is_array($child) && !ArrayHelper::getValue($child, 'visible', true)) {
                continue;
            }
            if (is_array($child) && $this->isItemActive($child)) {
                ArrayHelper::setValue($items[$i], 'active', true);
                if ($this->activateParents) {
                    $active = true;
                }
            }
            $childItems = ArrayHelper::getValue($child, 'items');
            if (is_array($childItems)) {
                $activeParent = false;
                $items[$i]['items'] = $this->isChildActive($childItems, $activeParent);
                if ($activeParent) {
                    Html::addCssClass($items[$i]['options'], ['class' => 'layui-this']);
                    $active = true;
                }
            }
        }

        return $items;
    }

    private function getThemeClass($theme)
    {
        if (in_array($theme, ['gray','cyan','green','blue'])) {
            return 'layui-bg-' . $theme;
        }
        return '';
    }
}