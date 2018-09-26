<?php

namespace geertw\Yii2\TranslatableUrlRule;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\BaseObject;
use yii\web\Request;
use yii\web\UrlManager;
use yii\web\UrlRule;
use yii\web\UrlRuleInterface;

/**
 * Class TranslatableUrlRule
 * @package geertw\Yii2\TranslatableUrlRule
 */
class TranslatableUrlRule extends BaseObject implements UrlRuleInterface {

    /**
     * @var string[] URL patterns. Key is language ID
     */
    public $patterns;

    /**
     * @var string Route
     */
    public $route;

    /**
     * @var UrlRule[] Conventional URL rule objects, key is language ID
     */
    protected $rules;

    /**
     * @var string Parameter for determining which language to use (instead of app language)
     */
    public $languageParam = 'url-language';

    /**
     * @var bool If to force search in all language rules
     */
    public $forceRuleSearch = false;

    /**
     * Initialize TranslatableUrlRule
     * @throws InvalidConfigException
     */
    public function init() {
        parent::init();

        if ($this->patterns === null || !is_array($this->patterns) || count($this->patterns) == 0) {
            throw new InvalidConfigException('TranslatableUrlRule::patterns must be set.');
        }

        if ($this->route === null) {
            throw new InvalidConfigException('TranslatableUrlRule::route must be set.');
        }

        $this->updateRules();
    }

    /**
     * Update URL rules per language
     */
    protected function updateRules() {
        foreach ($this->patterns as $language => $pattern) {
            $this->rules[$language] = new UrlRule(['pattern' => $pattern, 'route' => $this->route]);
        }
    }

    /**
     * Parses the given request and returns the corresponding route and parameters.
     * @param UrlManager $manager the URL manager
     * @param Request $request    the request component
     * @return array|bool the parsing result. The route and the parameters are returned as an array.
     *                            If false, it means this rule cannot be used to parse this path info.
     */
    public function parseRequest($manager, $request) {
        $this->updateRules();

        $language = Yii::$app->language;

        if (isset($this->rules[$language])) {
            $rule = $this->rules[$language];
        } else {
            // Fall back to first rule:
            $rule = array_values($this->rules)[0];
        }

        $result = $rule->parseRequest($manager, $request);

        if (!$result && $this->forceRuleSearch) {
            return $this->parseRequestForAllRules($manager, $request, $rule);
        }

        return $result;
    }

    /**
     * User can change language by typing in URL. Normal case will return 404, but
     * we can force the search in all language rules and redirect user to desired page.
     * 
     * @param UrlManager $manager the URL manager
     * @param Request $request    the request component
     * @param UrlRule $parsedRule Url Rule Already parsed
     * 
     * @return array|bool
     */
    protected function parseRequestForAllRules($manager, $request, $parsedRule) {
        foreach ($this->rules as $rule) {
            if ($rule->name == $parsedRule->name) {
                continue;
            }

            $result = $rule->parseRequest($manager, $request);

            if ($result) {
                list($route, $params) = $result;
                array_unshift($params, $route);

                Yii::$app->response->redirect($params);
                Yii::$app->end();
            }
        }

        return false;
    }

    /**
     * Creates a URL according to the given route and parameters.
     * @param UrlManager $manager the URL manager
     * @param string $route       the route. It should not have slashes at the beginning or the end.
     * @param array $params       the parameters
     * @return string|bool the created URL, or false if this rule cannot be used for creating this URL.
     */
    public function createUrl($manager, $route, $params) {
        $language = Yii::$app->language;

        if (isset($params[$this->languageParam])) {
            $language = $params[$this->languageParam];
            unset($params[$this->languageParam]);
        }

        if (isset($this->rules[$language])) {
            $rule = $this->rules[$language];
        } else {
            // Fall back to first rule:
            $rule = array_values($this->rules)[0];
        }

        return $rule->createUrl($manager, $route, $params);
    }

}
