Yii2 TranslatableUrlRule
========================

A custom [URL rule class](http://www.yiiframework.com/doc-2.0/yii-web-urlruleinterface.html) for [Yii 2](http://www.yiiframework.com/) which allows for translated URL rules.

This extension allows you to write URL rules per language. For example, you can have `signup` for `en`, `aanmelden` for `nl` and `registrieren` for `de`.
Because this extension uses [normal Yii2 UrlRule](http://www.yiiframework.com/doc-2.0/yii-web-urlrule.html) objects to build language-specific URL rules, you still have all power that comes with normal Yii URL rules, including URL parameters and regular expressions.

The current language is determined by the `Yii::$app->language` parameter. The value of this parameter is used to create and to parse URLs.

This extension does **not** set the current language parameter. There are excellent plugins for that, like [codemix/yii2-localeurls](https://github.com/codemix/yii2-localeurls).

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
        'class' => '\geertw\Yii2\TranslatableUrlRule\TranslatableUrlRule',
        'patterns' => [
            'en' => '/signup',
            'nl' => '/aanmelden',
            'de' => '/registrieren',
        ],
        'route' => 'user/signup',
    ],
    [
        'class' => '\geertw\Yii2\TranslatableUrlRule\TranslatableUrlRule',
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

You may omit the `class` configuration in your URL rules when you configure a [ruleConfig](http://www.yiiframework.com/doc-2.0/yii-web-urlmanager.html#$ruleConfig-detail) in UrlManager:

```php
<?
return [
    'components' => [
        'urlManager'   => [
            'ruleConfig' => [
                'class' => '\geertw\Yii2\TranslatableUrlRule\TranslatableUrlRule'
            ],
            'rules' => $rules,
            // Additional UrlManager configuration
        ],
    ];
?>
```
