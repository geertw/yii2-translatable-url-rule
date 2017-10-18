[![Latest Stable Version](https://img.shields.io/packagist/v/geertw/yii2-translatable-url-rule.svg)](https://packagist.org/packages/geertw/yii2-translatable-url-rule)
[![Total Downloads](https://img.shields.io/packagist/dt/geertw/yii2-translatable-url-rule.svg)](https://packagist.org/packages/geertw/yii2-translatable-url-rule)
[![License](https://img.shields.io/packagist/l/geertw/yii2-translatable-url-rule.svg)](https://packagist.org/packages/geertw/yii2-translatable-url-rule)

Yii2 TranslatableUrlRule
========================

A custom [URL rule class](http://www.yiiframework.com/doc-2.0/yii-web-urlruleinterface.html) for [Yii 2](http://www.yiiframework.com/) which allows for translated URL rules.

This extension allows you to write URL rules per language. For example, you can have `signup` for `en`, `aanmelden` for `nl` and `registrieren` for `de`.
Because this extension uses [normal Yii2 UrlRule](http://www.yiiframework.com/doc-2.0/yii-web-urlrule.html) objects to build language-specific URL rules, you still have all power that comes with normal Yii URL rules, including URL parameters and regular expressions.

The current language is determined by the `Yii::$app->language` parameter. The value of this parameter is used to create and to parse URLs.

This extension does **not** set the current language parameter. Use [codemix/yii2-localeurls](https://github.com/codemix/yii2-localeurls) for that.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require geertw/yii2-translatable-url-rule
```

or add

```
"geertw/yii2-translatable-url-rule": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Update your URL rules to use this class and set the patterns per language.

```php
<?
$rules = [
    [
        'class' => 'geertw\Yii2\TranslatableUrlRule\TranslatableUrlRule',
        'patterns' => [
            'en' => '/signup',
            'nl' => '/aanmelden',
            'de' => '/registrieren',
        ],
        'route' => 'user/signup',
    ],
    [
        'class' => 'geertw\Yii2\TranslatableUrlRule\TranslatableUrlRule',
        'patterns' => [
            'en' => '/user/<username>',
            'nl' => '/gebruiker/<username>',
            'de' => '/benutzer/<username>',
        ],
        'route' => 'user/view',
    ],
];
?>
```

The `route` parameter remains the same for all rules. `patterns` is an array of all patterns, the key must equal the Yii language identifier.
If there is no pattern for a language, the first configured pattern will be used.

Set `forceRuleSearch` to `true` to force searching in all rule patterns.

You may omit the `class` configuration in your URL rules when you configure a [ruleConfig](http://www.yiiframework.com/doc-2.0/yii-web-urlmanager.html#$ruleConfig-detail) in UrlManager:

```php
<?
return [
    'components' => [
        'urlManager'   => [
            'ruleConfig' => [
                'class' => 'geertw\Yii2\TranslatableUrlRule\TranslatableUrlRule'
            ],
            'rules' => $rules,
            // Additional UrlManager configuration
        ],
    ];
?>
```

## Language switcher example

Due to the way this library works, you need to specify **two** language parameters when creating URLs for routes in another language.

The following two code snippets allow you to create a simple dropdown which allows users to select alternative languages for the current route:

Create a widget like this:

```php
<?php

namespace frontend\components;

use Yii;
use yii\bootstrap\Dropdown;

class LanguageSwitcher extends Dropdown {
    public $langLabels;

    private $isError;

    public function init() {
        $route = Yii::$app->controller->route;
        $params = $_GET;
        $this->isError = $route === Yii::$app->errorHandler->errorAction;

        array_unshift($params, '/' . $route);

        foreach (Yii::$app->urlManager->languages as $language) {
            $isWildcard = substr($language, -2) === '-*';
            if ($isWildcard) {
                $language = substr($language, 0, 2);
            }
            $params['language'] = $language;
            $params['url-language'] = $language;
            $this->items[] = [
                'label' => $this->label($language),
                'url'   => $params,
            ];
        }
        parent::init();
    }

    public function run() {
        // Only show this widget if we're not on the error page
        if ($this->isError) {
            return '';
        } else {
            return parent::run();
        }
    }

    public function label($code) {
        return isset($this->langLabels[$code]) ? $this->langLabels[$code] : null;
    }
}
```

Insert the following code in your view code:

```php
    <?= LanguageSwitcher::widget([
        'options'    => ['class' => 'pull-right'],
        'langLabels' => [
            'de' => 'German',
            'en' => 'English',
            'nl' => 'Nederlands',
        ],
    ]) ?>
```

You might recognize this example, as it is largely based on the example for [yii2-localeurls](https://github.com/codemix/yii2-localeurls).
