<?php
namespace Codeception\Module;

use Codeception\Coverage\Subscriber\LocalServer;
use Codeception\Exception\ConnectionException;
use Codeception\Exception\ElementNotFound;
use Codeception\Exception\MalformedLocatorException;
use Codeception\Exception\ModuleConfigException as ModuleConfigException;
use Codeception\Exception\ModuleException;
use Codeception\Exception\TestRuntimeException;
use Codeception\Lib\Interfaces\ConflictsWithModule;
use Codeception\Lib\Interfaces\ElementLocator;
use Codeception\Lib\Interfaces\MultiSession as MultiSessionInterface;
use Codeception\Lib\Interfaces\PageSourceSaver;
use Codeception\Lib\Interfaces\Remote as RemoteInterface;
use Codeception\Lib\Interfaces\RequiresPackage;
use Codeception\Lib\Interfaces\ScreenshotSaver;
use Codeception\Lib\Interfaces\SessionSnapshot;
use Codeception\Lib\Interfaces\Web as WebInterface;
use Codeception\Module as CodeceptionModule;
use Codeception\PHPUnit\Constraint\Page as PageConstraint;
use Codeception\PHPUnit\Constraint\WebDriver as WebDriverConstraint;
use Codeception\PHPUnit\Constraint\WebDriverNot as WebDriverConstraintNot;
use Codeception\Test\Descriptor;
use Codeception\Test\Interfaces\ScenarioDriven;
use Codeception\TestInterface;
use Codeception\Util\Debug;
use Codeception\Util\Locator;
use Codeception\Util\Uri;
use Facebook\WebDriver\Exception\InvalidElementStateException;
use Facebook\WebDriver\Exception\InvalidSelectorException;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\UnknownServerException;
use Facebook\WebDriver\Exception\WebDriverCurlException;
use Facebook\WebDriver\Interactions\WebDriverActions;
use Facebook\WebDriver\Remote\LocalFileDetector;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\UselessFileDetector;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverKeys;
use Facebook\WebDriver\WebDriverSelect;
use GuzzleHttp\Cookie\SetCookie;
use Symfony\Component\DomCrawler\Crawler;


class WebDriver extends CodeceptionModule implements
    WebInterface,
    RemoteInterface,
    MultiSessionInterface,
    SessionSnapshot,
    ScreenshotSaver,
    PageSourceSaver,
    ElementLocator,
    ConflictsWithModule,
    RequiresPackage
{
    protected $requiredFields = ['browser', 'url'];
    protected $config = [
        'protocol'           => 'http',
        'host'               => '127.0.0.1',
        'port'               => '4444',
        'path'               => '/wd/hub',
        'restart'            => false,
        'wait'               => 0,
        'clear_cookies'      => true,
        'window_size'        => false,
        'capabilities'       => [],
        'connection_timeout' => null,
        'request_timeout'    => null,
        'pageload_timeout'   => null,
        'http_proxy'         => null,
        'http_proxy_port'    => null,
        'ssl_proxy'          => null,
        'ssl_proxy_port'     => null,
        'debug_log_entries'  => 15,
        'log_js_errors'      => false
    ];

    protected $wd_host;
    protected $capabilities;
    protected $connectionTimeoutInMs;
    protected $requestTimeoutInMs;
    protected $test;
    protected $sessionSnapshots = [];
    protected $sessions = [];
    protected $httpProxy;
    protected $httpProxyPort;
    protected $sslProxy;
    protected $sslProxyPort;

    
    public $webDriver;

    public function _requires()
    {
        return ['Facebook\WebDriver\Remote\RemoteWebDriver' => '"facebook/webdriver": "^1.0.1"'];
    }

    public function _initialize()
    {
        $this->wd_host = sprintf('%s://%s:%s%s', $this->config['protocol'], $this->config['host'], $this->config['port'], $this->config['path']);
        $this->capabilities = $this->config['capabilities'];
        $this->capabilities[WebDriverCapabilityType::BROWSER_NAME] = $this->config['browser'];
        if ($proxy = $this->getProxy()) {
            $this->capabilities[WebDriverCapabilityType::PROXY] = $proxy;
        }
        $this->connectionTimeoutInMs = $this->config['connection_timeout'] * 1000;
        $this->requestTimeoutInMs = $this->config['request_timeout'] * 1000;
        $this->loadFirefoxProfile();
    }

    public function _conflicts()
    {
        return 'Codeception\Lib\Interfaces\Web';
    }

    public function _before(TestInterface $test)
    {
        if (!isset($this->webDriver)) {
            $this->_initializeSession();
        }
        $test->getMetadata()->setCurrent([
            'browser' => $this->config['browser'],
            'capabilities' => $this->config['capabilities']
        ]);
    }

    protected function loadFirefoxProfile()
    {
        if (!array_key_exists('firefox_profile', $this->config['capabilities'])) {
            return;
        }

        $firefox_profile = $this->config['capabilities']['firefox_profile'];
        if (file_exists($firefox_profile) === false) {
            throw new ModuleConfigException(
                __CLASS__,
                "Firefox profile does not exist under given path " . $firefox_profile
            );
        }
        // Set firefox profile as capability
        $this->capabilities['firefox_profile'] = file_get_contents($firefox_profile);
    }

    protected function initialWindowSize()
    {
        if ($this->config['window_size'] == 'maximize') {
            $this->maximizeWindow();
            return;
        }
        $size = explode('x', $this->config['window_size']);
        if (count($size) == 2) {
            $this->resizeWindow(intval($size[0]), intval($size[1]));
        }
    }

    public function _after(TestInterface $test)
    {
        if ($this->config['restart']) {
            $this->cleanWebDriver();
            return;
        }
        if ($this->config['clear_cookies'] && isset($this->webDriver)) {
            $this->webDriver->manage()->deleteAllCookies();
        }
    }

    public function _failed(TestInterface $test, $fail)
    {
        $this->debugWebDriverLogs($test);
        $filename = preg_replace('~\W~', '.', Descriptor::getTestSignature($test));
        $outputDir = codecept_output_dir();
        $this->_saveScreenshot($report = $outputDir . mb_strcut($filename, 0, 245, 'utf-8') . '.fail.png');
        $test->getMetadata()->addReport('png', $report);
        $this->_savePageSource($report = $outputDir . mb_strcut($filename, 0, 244, 'utf-8') . '.fail.html');
        $test->getMetadata()->addReport('html', $report);
        $this->debug("Screenshot and page source were saved into '$outputDir' dir");
    }

    
    public function debugWebDriverLogs(TestInterface $test = null)
    {
        if (!isset($this->webDriver)) {
            $this->debug('WebDriver::debugWebDriverLogs method has been called when webDriver is not set');
            return;
        }
        try {
            // Dump out latest Selenium logs
            $logs = $this->webDriver->manage()->getAvailableLogTypes();
            foreach ($logs as $logType) {
                $logEntries = array_slice(
                    $this->webDriver->manage()->getLog($logType),
                    -$this->config['debug_log_entries']
                );

                if (empty($logEntries)) {
                    $this->debugSection("Selenium {$logType} Logs", " EMPTY ");
                    continue;
                }
                $this->debugSection("Selenium {$logType} Logs", "\n" . $this->formatLogEntries($logEntries));

                if ($logType === 'browser' && $this->config['log_js_errors']
                    && ($test instanceof ScenarioDriven)
                ) {
                    $this->logJSErrors($test, $logEntries);
                }
            }
        } catch (\Exception $e) {
            $this->debug('Unable to retrieve Selenium logs : ' . $e->getMessage());
        }
    }

    
    protected function formatLogEntries(array $logEntries)
    {
        $formattedLogs = '';

        foreach ($logEntries as $logEntry) {
            // Timestamp is in milliseconds, but date() requires seconds.
            $time = date('H:i:s', $logEntry['timestamp'] / 1000) .
                // Append the milliseconds to the end of the time string
                '.' . ($logEntry['timestamp'] % 1000);
            $formattedLogs .= "{$time} {$logEntry['level']} - {$logEntry['message']}\n";
        }
        return $formattedLogs;
    }

    
    protected function logJSErrors(ScenarioDriven $test, array $browserLogEntries)
    {
        foreach ($browserLogEntries as $logEntry) {
            if (true === isset($logEntry['level'])
                && true === isset($logEntry['message'])
                && $this->isJSError($logEntry['level'], $logEntry['message'])
            ) {
                // Timestamp is in milliseconds, but date() requires seconds.
                $time = date('H:i:s', $logEntry['timestamp'] / 1000) .
                    // Append the milliseconds to the end of the time string
                    '.' . ($logEntry['timestamp'] % 1000);
                $test->getScenario()->comment("{$time} {$logEntry['level']} - {$logEntry['message']}");
            }
        }
    }

    
    protected function isJSError($logEntryLevel, $message)
    {
        return
        (
            ($this->isPhantom() && $logEntryLevel != 'INFO')          // phantomjs logs errors as "WARNING"
            || $logEntryLevel === 'SEVERE'                            // other browsers log errors as "SEVERE"
        )
        && strpos($message, 'ERR_PROXY_CONNECTION_FAILED') === false;  // ignore blackhole proxy
    }

    public function _afterSuite()
    {
        // this is just to make sure webDriver is cleared after suite
        $this->cleanWebDriver();
    }

    protected function cleanWebDriver()
    {
        foreach ($this->sessions as $session) {
            $this->_loadSession($session);
            try {
                $this->webDriver->quit();
            } catch (\Exception $e) {
                // Session already closed so nothing to do
            }
            unset($this->webDriver);
        }
        $this->sessions = [];
    }

    public function amOnSubdomain($subdomain)
    {
        $url = $this->config['url'];
        $url = preg_replace('~(https?:\/\/)(.*\.)(.*\.)~', "$1$3", $url); // removing current subdomain
        $url = preg_replace('~(https?:\/\/)(.*)~', "$1$subdomain.$2", $url); // inserting new
        $this->_reconfigure(['url' => $url]);
    }

    
    public function _getUrl()
    {
        if (!isset($this->config['url'])) {
            throw new ModuleConfigException(
                __CLASS__,
                "Module connection failure. The URL for client can't bre retrieved"
            );
        }
        return $this->config['url'];
    }

    protected function getProxy()
    {
        $proxyConfig = [];
        if ($this->config['http_proxy']) {
            $proxyConfig['httpProxy'] = $this->config['http_proxy'];
            if ($this->config['http_proxy_port']) {
                $proxyConfig['httpProxy'] .= ':' . $this->config['http_proxy_port'];
            }
        }
        if ($this->config['ssl_proxy']) {
            $proxyConfig['sslProxy'] = $this->config['ssl_proxy'];
            if ($this->config['ssl_proxy_port']) {
                $proxyConfig['sslProxy'] .= ':' . $this->config['ssl_proxy_port'];
            }
        }
        if (!empty($proxyConfig)) {
            $proxyConfig['proxyType'] = 'manual';
            return $proxyConfig;
        }
        return null;
    }

    
    public function _getCurrentUri()
    {
        $url = $this->webDriver->getCurrentURL();
        if ($url == 'about:blank') {
            throw new ModuleException($this, 'Current url is blank, no page was opened');
        }
        return Uri::retrieveUri($url);
    }

    public function _saveScreenshot($filename)
    {
        if (!isset($this->webDriver)) {
            $this->debug('WebDriver::_saveScreenshot method has been called when webDriver is not set');
            return;
        }
        try {
            $this->webDriver->takeScreenshot($filename);
        } catch (\Exception $e) {
            $this->debug('Unable to retrieve screenshot from Selenium : ' . $e->getMessage());
        }
    }

    public function _findElements($locator)
    {
        return $this->match($this->webDriver, $locator);
    }

    
    public function _savePageSource($filename)
    {
        if (!isset($this->webDriver)) {
            $this->debug('WebDriver::_savePageSource method has been called when webDriver is not set');
            return;
        }
        try {
            file_put_contents($filename, $this->webDriver->getPageSource());
        } catch (\Exception $e) {
            $this->debug('Unable to retrieve source page from Selenium : ' . $e->getMessage());
        }
    }

    
    public function makeScreenshot($name)
    {
        $debugDir = codecept_log_dir() . 'debug';
        if (!is_dir($debugDir)) {
            mkdir($debugDir, 0777);
        }
        $screenName = $debugDir . DIRECTORY_SEPARATOR . $name . '.png';
        $this->_saveScreenshot($screenName);
        $this->debug("Screenshot saved to $screenName");
    }

    
    public function resizeWindow($width, $height)
    {
        $this->webDriver->manage()->window()->setSize(new WebDriverDimension($width, $height));
    }

    public function seeCookie($cookie, array $params = [])
    {
        $cookies = $this->filterCookies($this->webDriver->manage()->getCookies(), $params);
        $cookies = array_map(
            function ($c) {
                return $c['name'];
            },
            $cookies
        );
        $this->debugSection('Cookies', json_encode($this->webDriver->manage()->getCookies()));
        $this->assertContains($cookie, $cookies);
    }

    public function dontSeeCookie($cookie, array $params = [])
    {
        $cookies = $this->filterCookies($this->webDriver->manage()->getCookies(), $params);
        $cookies = array_map(
            function ($c) {
                return $c['name'];
            },
            $cookies
        );
        $this->debugSection('Cookies', json_encode($this->webDriver->manage()->getCookies()));
        $this->assertNotContains($cookie, $cookies);
    }

    public function setCookie($cookie, $value, array $params = [])
    {
        $params['name'] = $cookie;
        $params['value'] = $value;
        if (isset($params['expires'])) { // PhpBrowser compatibility
            $params['expiry'] = $params['expires'];
        }
        if (!isset($params['domain'])) {
            $urlParts = parse_url($this->config['url']);
            if (isset($urlParts['host'])) {
                $params['domain'] = $urlParts['host'];
            }
        }
        $this->webDriver->manage()->addCookie($params);
        $this->debugSection('Cookies', json_encode($this->webDriver->manage()->getCookies()));
    }

    public function resetCookie($cookie, array $params = [])
    {
        $this->webDriver->manage()->deleteCookieNamed($cookie);
        $this->debugSection('Cookies', json_encode($this->webDriver->manage()->getCookies()));
    }

    public function grabCookie($cookie, array $params = [])
    {
        $params['name'] = $cookie;
        $cookies = $this->filterCookies($this->webDriver->manage()->getCookies(), $params);
        if (empty($cookies)) {
            return null;
        }
        $cookie = reset($cookies);
        return $cookie['value'];
    }

    protected function filterCookies($cookies, $params = [])
    {
        foreach (['domain', 'path', 'name'] as $filter) {
            if (!isset($params[$filter])) {
                continue;
            }
            $cookies = array_filter(
                $cookies,
                function ($item) use ($filter, $params) {
                    return $item[$filter] == $params[$filter];
                }
            );
        }
        return $cookies;
    }

    public function amOnUrl($url)
    {
        $host = Uri::retrieveHost($url);
        $this->_reconfigure(['url' => $host]);
        $this->debugSection('Host', $host);
        $this->webDriver->get($url);
    }

    public function amOnPage($page)
    {
        $url = Uri::appendPath($this->config['url'], $page);
        $this->debugSection('GET', $url);
        $this->webDriver->get($url);
    }

    public function see($text, $selector = null)
    {
        if (!$selector) {
            return $this->assertPageContains($text);
        }
        $nodes = $this->matchVisible($selector);
        $this->assertNodesContain($text, $nodes, $selector);
    }

    public function dontSee($text, $selector = null)
    {
        if (!$selector) {
            return $this->assertPageNotContains($text);
        }
        $nodes = $this->matchVisible($selector);
        $this->assertNodesNotContain($text, $nodes, $selector);
    }

    public function seeInSource($raw)
    {
        $this->assertPageSourceContains($raw);
    }

    public function dontSeeInSource($raw)
    {
        $this->assertPageSourceNotContains($raw);
    }

    
    public function seeInPageSource($text)
    {
        $this->assertThat(
            $this->webDriver->getPageSource(),
            new PageConstraint($text, $this->_getCurrentUri()),
            ''
        );
    }

    
    public function dontSeeInPageSource($text)
    {
        $this->assertThatItsNot(
            $this->webDriver->getPageSource(),
            new PageConstraint($text, $this->_getCurrentUri()),
            ''
        );
    }

    public function click($link, $context = null)
    {
        $page = $this->webDriver;
        if ($context) {
            $page = $this->matchFirstOrFail($this->webDriver, $context);
        }
        $el = $this->findClickable($page, $link);
        if (!$el) {
            try {
                $els = $this->match($page, $link);
            } catch (MalformedLocatorException $e) {
                throw new ElementNotFound("name=$link", "'$link' is invalid CSS and XPath selector and Link or Button");
            }
            $el = reset($els);
        }
        if (!$el) {
            throw new ElementNotFound($link, 'Link or Button or CSS or XPath');
        }
        $el->click();
    }

    
    protected function findClickable($page, $link)
    {
        if (is_array($link) or ($link instanceof WebDriverBy)) {
            return $this->matchFirstOrFail($page, $link);
        }

        // try to match by CSS or XPath
        try {
            $els = $this->match($page, $link, false);
            if (!empty($els)) {
                return reset($els);
            }
        } catch (MalformedLocatorException $e) {
            //ignore exception, link could still match on of the things below
        }

        $locator = Crawler::xpathLiteral(trim($link));

        // narrow
        $xpath = Locator::combine(
            ".//a[normalize-space(.)=$locator]",
            ".//button[normalize-space(.)=$locator]",
            ".//a/img[normalize-space(@alt)=$locator]/ancestor::a",
            ".//input[./@type = 'submit' or ./@type = 'image' or ./@type = 'button'][normalize-space(@value)=$locator]"
        );

        $els = $page->findElements(WebDriverBy::xpath($xpath));
        if (count($els)) {
            return reset($els);
        }

        // wide
        $xpath = Locator::combine(
            ".//a[./@href][((contains(normalize-space(string(.)), $locator)) or contains(./@title, $locator) or .//img[contains(./@alt, $locator)])]",
            ".//input[./@type = 'submit' or ./@type = 'image' or ./@type = 'button'][contains(./@value, $locator)]",
            ".//input[./@type = 'image'][contains(./@alt, $locator)]",
            ".//button[contains(normalize-space(string(.)), $locator)]",
            ".//input[./@type = 'submit' or ./@type = 'image' or ./@type = 'button'][./@name = $locator]",
            ".//button[./@name = $locator]"
        );

        $els = $page->findElements(WebDriverBy::xpath($xpath));
        if (count($els)) {
            return reset($els);
        }

        return null;
    }

    
    protected function findFields($selector)
    {
        if ($selector instanceof WebDriverElement) {
            return [$selector];
        }
        if (is_array($selector) || ($selector instanceof WebDriverBy)) {
            $fields = $this->match($this->webDriver, $selector);

            if (empty($fields)) {
                throw new ElementNotFound($selector);
            }
            return $fields;
        }

        $locator = Crawler::xpathLiteral(trim($selector));
        // by text or label
        $xpath = Locator::combine(
            // @codingStandardsIgnoreStart
            ".//*[self::input | self::textarea | self::select][not(./@type = 'submit' or ./@type = 'image' or ./@type = 'hidden')][(((./@name = $locator) or ./@id = //label[contains(normalize-space(string(.)), $locator)]/@for) or ./@placeholder = $locator)]",
            ".//label[contains(normalize-space(string(.)), $locator)]//.//*[self::input | self::textarea | self::select][not(./@type = 'submit' or ./@type = 'image' or ./@type = 'hidden')]"
            // @codingStandardsIgnoreEnd
        );
        $fields = $this->webDriver->findElements(WebDriverBy::xpath($xpath));
        if (!empty($fields)) {
            return $fields;
        }

        // by name
        $xpath = ".//*[self::input | self::textarea | self::select][@name = $locator]";
        $fields = $this->webDriver->findElements(WebDriverBy::xpath($xpath));
        if (!empty($fields)) {
            return $fields;
        }

        // try to match by CSS or XPath
        $fields = $this->match($this->webDriver, $selector, false);
        if (!empty($fields)) {
            return $fields;
        }

        throw new ElementNotFound($selector, "Field by name, label, CSS or XPath");
    }

    
    protected function findField($selector)
    {
        $arr = $this->findFields($selector);
        return reset($arr);
    }

    public function seeLink($text, $url = null)
    {
        $nodes = $this->webDriver->findElements(WebDriverBy::partialLinkText($text));
        if (empty($nodes)) {
            $this->fail("No links containing text '$text' were found in page " . $this->_getCurrentUri());
        }
        if ($url) {
            $nodes = array_filter(
                $nodes,
                function (WebDriverElement $e) use ($url) {
                    return trim($e->getAttribute('href')) == trim($url);
                }
            );
            if (empty($nodes)) {
                $this->fail("No links containing text '$text' and URL '$url' were found in page " . $this->_getCurrentUri());
            }
        }
    }

    public function dontSeeLink($text, $url = null)
    {
        $nodes = $this->webDriver->findElements(WebDriverBy::partialLinkText($text));
        if (!$url) {
            if (!empty($nodes)) {
                $this->fail("Link containing text '$text' was found in page " . $this->_getCurrentUri());
            }
            return;
        }
        $nodes = array_filter(
            $nodes,
            function (WebDriverElement $e) use ($url) {
                return trim($e->getAttribute('href')) == trim($url);
            }
        );
        if (!empty($nodes)) {
            $this->fail("Link containing text '$text' and URL '$url' was found in page " . $this->_getCurrentUri());
        }
    }

    public function seeInCurrentUrl($uri)
    {
        $this->assertContains($uri, $this->_getCurrentUri());
    }

    public function seeCurrentUrlEquals($uri)
    {
        $this->assertEquals($uri, $this->_getCurrentUri());
    }

    public function seeCurrentUrlMatches($uri)
    {
        $this->assertRegExp($uri, $this->_getCurrentUri());
    }

    public function dontSeeInCurrentUrl($uri)
    {
        $this->assertNotContains($uri, $this->_getCurrentUri());
    }

    public function dontSeeCurrentUrlEquals($uri)
    {
        $this->assertNotEquals($uri, $this->_getCurrentUri());
    }

    public function dontSeeCurrentUrlMatches($uri)
    {
        $this->assertNotRegExp($uri, $this->_getCurrentUri());
    }

    public function grabFromCurrentUrl($uri = null)
    {
        if (!$uri) {
            return $this->_getCurrentUri();
        }
        $matches = [];
        $res = preg_match($uri, $this->_getCurrentUri(), $matches);
        if (!$res) {
            $this->fail("Couldn't match $uri in " . $this->_getCurrentUri());
        }
        if (!isset($matches[1])) {
            $this->fail("Nothing to grab. A regex parameter required. Ex: '/user/(\\d+)'");
        }
        return $matches[1];
    }

    public function seeCheckboxIsChecked($checkbox)
    {
        $this->assertTrue($this->findField($checkbox)->isSelected());
    }

    public function dontSeeCheckboxIsChecked($checkbox)
    {
        $this->assertFalse($this->findField($checkbox)->isSelected());
    }

    public function seeInField($field, $value)
    {
        $els = $this->findFields($field);
        $this->assert($this->proceedSeeInField($els, $value));
    }

    public function dontSeeInField($field, $value)
    {
        $els = $this->findFields($field);
        $this->assertNot($this->proceedSeeInField($els, $value));
    }

    public function seeInFormFields($formSelector, array $params)
    {
        $this->proceedSeeInFormFields($formSelector, $params, false);
    }

    public function dontSeeInFormFields($formSelector, array $params)
    {
        $this->proceedSeeInFormFields($formSelector, $params, true);
    }

    protected function proceedSeeInFormFields($formSelector, array $params, $assertNot)
    {
        $form = $this->match($this->webDriver, $formSelector);
        if (empty($form)) {
            throw new ElementNotFound($formSelector, "Form via CSS or XPath");
        }
        $form = reset($form);
        foreach ($params as $name => $values) {
            $els = $form->findElements(WebDriverBy::name($name));
            if (empty($els)) {
                throw new ElementNotFound($name);
            }
            if (!is_array($values)) {
                $values = [$values];
            }
            foreach ($values as $value) {
                $ret = $this->proceedSeeInField($els, $value);
                if ($assertNot) {
                    $this->assertNot($ret);
                } else {
                    $this->assert($ret);
                }
            }
        }
    }

    
    protected function proceedSeeInField(array $elements, $value)
    {
        $strField = reset($elements)->getAttribute('name');
        if (reset($elements)->getTagName() === 'select') {
            $el = reset($elements);
            $elements = $el->findElements(WebDriverBy::xpath('.//option'));
            if (empty($value) && empty($elements)) {
                return ['True', true];
            }
        }

        $currentValues = [];
        if (is_bool($value)) {
            $currentValues = [false];
        }
        foreach ($elements as $el) {
            switch ($el->getTagName()) {
                case 'input':
                    if ($el->getAttribute('type') === 'radio' || $el->getAttribute('type') === 'checkbox') {
                        if ($el->getAttribute('checked')) {
                            if (is_bool($value)) {
                                $currentValues = [true];
                                break;
                            } else {
                                $currentValues[] = $el->getAttribute('value');
                            }
                        }
                    } else {
                        $currentValues[] = $el->getAttribute('value');
                    }
                    break;
                case 'option':
                    // no break we need the trim text and the value also
                    if (!$el->isSelected()) {
                        break;
                    }
                    $currentValues[] = $el->getText();
                case 'textarea':
                    // we include trimmed and real value of textarea for check
                    $currentValues[] = trim($el->getText());
                default:
                    $currentValues[] = $el->getAttribute('value'); // raw value
                    break;
            }
        }

        return [
            'Contains',
            $value,
            $currentValues,
            "Failed testing for '$value' in $strField's value: '" . implode("', '", $currentValues) . "'"
        ];
    }

    public function selectOption($select, $option)
    {
        $el = $this->findField($select);
        if ($el->getTagName() != 'select') {
            $els = $this->matchCheckables($select);
            $radio = null;
            foreach ($els as $el) {
                $radio = $this->findCheckable($el, $option, true);
                if ($radio) {
                    break;
                }
            }
            if (!$radio) {
                throw new ElementNotFound($select, "Radiobutton with value or name '$option in");
            }
            $radio->click();
            return;
        }

        $wdSelect = new WebDriverSelect($el);
        if ($wdSelect->isMultiple()) {
            $wdSelect->deselectAll();
        }
        if (!is_array($option)) {
            $option = [$option];
        }

        $matched = false;

        if (key($option) !== 'value') {
            foreach ($option as $opt) {
                try {
                    $wdSelect->selectByVisibleText($opt);
                    $matched = true;
                } catch (NoSuchElementException $e) {
                }
            }
        }

        if ($matched) {
            return;
        }

        if (key($option) !== 'text') {
            foreach ($option as $opt) {
                try {
                    $wdSelect->selectByValue($opt);
                    $matched = true;
                } catch (NoSuchElementException $e) {
                }
            }
        }

        if ($matched) {
            return;
        }

        // partially matching
        foreach ($option as $opt) {
            try {
                $optElement = $el->findElement(WebDriverBy::xpath('.//option [contains (., "' . $opt . '")]'));
                $matched = true;
                if (!$optElement->isSelected()) {
                    $optElement->click();
                }
            } catch (NoSuchElementException $e) {
                // exception treated at the end
            }
        }
        if ($matched) {
            return;
        }
        throw new ElementNotFound(json_encode($option), "Option inside $select matched by name or value");
    }

    public function _initializeSession()
    {
        try {
            $this->webDriver = RemoteWebDriver::create(
                $this->wd_host,
                $this->capabilities,
                $this->connectionTimeoutInMs,
                $this->requestTimeoutInMs,
                $this->httpProxy,
                $this->httpProxyPort
            );
            $this->sessions[] = $this->_backupSession();
            $this->webDriver->manage()->timeouts()->implicitlyWait($this->config['wait']);
            if (!is_null($this->config['pageload_timeout'])) {
                $this->webDriver->manage()->timeouts()->pageLoadTimeout($this->config['pageload_timeout']);
            }
            $this->initialWindowSize();
        } catch (WebDriverCurlException $e) {
            throw new ConnectionException("Can't connect to Webdriver at {$this->wd_host}. Please make sure that Selenium Server or PhantomJS is running.");
        }
    }

    public function _loadSession($session)
    {
        $this->webDriver = $session;
    }

    public function _backupSession()
    {
        return $this->webDriver;
    }

    public function _closeSession($webDriver)
    {
        $keys = array_keys($this->sessions, $webDriver, true);
        $key = array_shift($keys);
        try {
            $webDriver->quit();
        } catch (UnknownServerException $e) {
            // Session already closed so nothing to do
        }
        unset($this->sessions[$key]);
    }

    
    public function unselectOption($select, $option)
    {
        $el = $this->findField($select);

        $wdSelect = new WebDriverSelect($el);

        if (!is_array($option)) {
            $option = [$option];
        }

        $matched = false;

        foreach ($option as $opt) {
            try {
                $wdSelect->deselectByVisibleText($opt);
                $matched = true;
            } catch (NoSuchElementException $e) {
                // exception treated at the end
            }

            try {
                $wdSelect->deselectByValue($opt);
                $matched = true;
            } catch (NoSuchElementException $e) {
                // exception treated at the end
            }
        }

        if ($matched) {
            return;
        }
        throw new ElementNotFound(json_encode($option), "Option inside $select matched by name or value");
    }

    
    protected function findCheckable($context, $radioOrCheckbox, $byValue = false)
    {
        if ($radioOrCheckbox instanceof WebDriverElement) {
            return $radioOrCheckbox;
        }
        if (is_array($radioOrCheckbox) or ($radioOrCheckbox instanceof WebDriverBy)) {
            return $this->matchFirstOrFail($this->webDriver, $radioOrCheckbox);
        }

        $locator = Crawler::xpathLiteral($radioOrCheckbox);
        if ($context instanceof WebDriverElement && $context->getTagName() === 'input') {
            $contextType = $context->getAttribute('type');
            if (!in_array($contextType, ['checkbox', 'radio'], true)) {
                return null;
            }
            $nameLiteral = Crawler::xPathLiteral($context->getAttribute('name'));
            $typeLiteral = Crawler::xPathLiteral($contextType);
            $inputLocatorFragment = "input[@type = $typeLiteral][@name = $nameLiteral]";
            $xpath = Locator::combine(
                // @codingStandardsIgnoreStart
                "ancestor::form//{$inputLocatorFragment}[(@id = ancestor::form//label[contains(normalize-space(string(.)), $locator)]/@for) or @placeholder = $locator]",
                // @codingStandardsIgnoreEnd
                "ancestor::form//label[contains(normalize-space(string(.)), $locator)]//{$inputLocatorFragment}"
            );
            if ($byValue) {
                $xpath = Locator::combine($xpath, "ancestor::form//{$inputLocatorFragment}[@value = $locator]");
            }
        } else {
            $xpath = Locator::combine(
                // @codingStandardsIgnoreStart
                "//input[@type = 'checkbox' or @type = 'radio'][(@id = //label[contains(normalize-space(string(.)), $locator)]/@for) or @placeholder = $locator or @name = $locator]",
                // @codingStandardsIgnoreEnd
                "//label[contains(normalize-space(string(.)), $locator)]//input[@type = 'radio' or @type = 'checkbox']"
            );
            if ($byValue) {
                $xpath = Locator::combine($xpath, "//input[@type = 'checkbox' or @type = 'radio'][@value = $locator]");
            }
        }
        $els = $context->findElements(WebDriverBy::xpath($xpath));
        if (count($els)) {
            return reset($els);
        }
        $els = $context->findElements(WebDriverBy::xpath(str_replace('ancestor::form', '', $xpath)));
        if (count($els)) {
            return reset($els);
        }
        $els = $this->match($context, $radioOrCheckbox);
        if (count($els)) {
            return reset($els);
        }
        return null;
    }

    protected function matchCheckables($selector)
    {
        $els = $this->match($this->webDriver, $selector);
        if (!count($els)) {
            throw new ElementNotFound($selector, "Element containing radio by CSS or XPath");
        }
        return $els;
    }

    public function checkOption($option)
    {
        $field = $this->findCheckable($this->webDriver, $option);
        if (!$field) {
            throw new ElementNotFound($option, "Checkbox or Radio by Label or CSS or XPath");
        }
        if ($field->isSelected()) {
            return;
        }
        $field->click();
    }

    public function uncheckOption($option)
    {
        $field = $this->findCheckable($this->webDriver, $option);
        if (!$field) {
            throw new ElementNotFound($option, "Checkbox by Label or CSS or XPath");
        }
        if (!$field->isSelected()) {
            return;
        }
        $field->click();
    }

    public function fillField($field, $value)
    {
        $el = $this->findField($field);
        $el->clear();
        $el->sendKeys($value);
    }

    public function attachFile($field, $filename)
    {
        $el = $this->findField($field);
        // in order to be compatible on different OS
        $filePath = realpath(codecept_data_dir() . $filename);
        if (!is_readable($filePath)) {
            throw new \InvalidArgumentException("file not found or not readable: $filePath");
        }
        // in order for remote upload to be enabled
        $el->setFileDetector(new LocalFileDetector());

        // skip file detector for phantomjs
        if ($this->isPhantom()) {
            $el->setFileDetector(new UselessFileDetector());
        }
        $el->sendKeys($filePath);
    }

    
    public function getVisibleText()
    {
        $els = $this->webDriver->findElements(WebDriverBy::cssSelector('body'));
        if (count($els)) {
            return $els[0]->getText();
        }

        return "";
    }

    public function grabTextFrom($cssOrXPathOrRegex)
    {
        $els = $this->match($this->webDriver, $cssOrXPathOrRegex, false);
        if (count($els)) {
            return $els[0]->getText();
        }
        if (@preg_match($cssOrXPathOrRegex, $this->webDriver->getPageSource(), $matches)) {
            return $matches[1];
        }
        throw new ElementNotFound($cssOrXPathOrRegex, 'CSS or XPath or Regex');
    }

    public function grabAttributeFrom($cssOrXpath, $attribute)
    {
        $el = $this->matchFirstOrFail($this->webDriver, $cssOrXpath);
        return $el->getAttribute($attribute);
    }

    public function grabValueFrom($field)
    {
        $el = $this->findField($field);
        // value of multiple select is the value of the first selected option
        if ($el->getTagName() == 'select') {
            $select = new WebDriverSelect($el);
            return $select->getFirstSelectedOption()->getAttribute('value');
        }
        return $el->getAttribute('value');
    }

    public function grabMultiple($cssOrXpath, $attribute = null)
    {
        $els = $this->match($this->webDriver, $cssOrXpath);
        return array_map(
            function (WebDriverElement $e) use ($attribute) {
                if ($attribute) {
                    return $e->getAttribute($attribute);
                }
                return $e->getText();
            },
            $els
        );
    }


    protected function filterByAttributes($els, array $attributes)
    {
        foreach ($attributes as $attr => $value) {
            $els = array_filter(
                $els,
                function (WebDriverElement $el) use ($attr, $value) {
                    return $el->getAttribute($attr) == $value;
                }
            );
        }
        return $els;
    }

    public function seeElement($selector, $attributes = [])
    {
        $els = $this->matchVisible($selector);
        $els = $this->filterByAttributes($els, $attributes);
        $this->assertNotEmpty($els);
    }

    public function dontSeeElement($selector, $attributes = [])
    {
        $els = $this->matchVisible($selector);
        $els = $this->filterByAttributes($els, $attributes);
        $this->assertEmpty($els);
    }

    
    public function seeElementInDOM($selector, $attributes = [])
    {
        $els = $this->match($this->webDriver, $selector);
        $els = $this->filterByAttributes($els, $attributes);
        $this->assertNotEmpty($els);
    }


    
    public function dontSeeElementInDOM($selector, $attributes = [])
    {
        $els = $this->match($this->webDriver, $selector);
        $els = $this->filterByAttributes($els, $attributes);
        $this->assertEmpty($els);
    }

    public function seeNumberOfElements($selector, $expected)
    {
        $counted = count($this->matchVisible($selector));
        if (is_array($expected)) {
            list($floor, $ceil) = $expected;
            $this->assertTrue(
                $floor <= $counted && $ceil >= $counted,
                'Number of elements counted differs from expected range'
            );
        } else {
            $this->assertEquals(
                $expected,
                $counted,
                'Number of elements counted differs from expected number'
            );
        }
    }

    public function seeNumberOfElementsInDOM($selector, $expected)
    {
        $counted = count($this->match($this->webDriver, $selector));
        if (is_array($expected)) {
            list($floor, $ceil) = $expected;
            $this->assertTrue(
                $floor <= $counted && $ceil >= $counted,
                'Number of elements counted differs from expected range'
            );
        } else {
            $this->assertEquals(
                $expected,
                $counted,
                'Number of elements counted differs from expected number'
            );
        }
    }

    public function seeOptionIsSelected($selector, $optionText)
    {
        $el = $this->findField($selector);
        if ($el->getTagName() !== 'select') {
            $els = $this->matchCheckables($selector);
            foreach ($els as $k => $el) {
                $els[$k] = $this->findCheckable($el, $optionText, true);
            }
            $this->assertNotEmpty(
                array_filter(
                    $els,
                    function ($e) {
                        return $e && $e->isSelected();
                    }
                )
            );
            return;
        }
        $select = new WebDriverSelect($el);
        $this->assertNodesContain($optionText, $select->getAllSelectedOptions(), 'option');
    }

    public function dontSeeOptionIsSelected($selector, $optionText)
    {
        $el = $this->findField($selector);
        if ($el->getTagName() !== 'select') {
            $els = $this->matchCheckables($selector);
            foreach ($els as $k => $el) {
                $els[$k] = $this->findCheckable($el, $optionText, true);
            }
            $this->assertEmpty(
                array_filter(
                    $els,
                    function ($e) {
                        return $e && $e->isSelected();
                    }
                )
            );
            return;
        }
        $select = new WebDriverSelect($el);
        $this->assertNodesNotContain($optionText, $select->getAllSelectedOptions(), 'option');
    }

    public function seeInTitle($title)
    {
        $this->assertContains($title, $this->webDriver->getTitle());
    }

    public function dontSeeInTitle($title)
    {
        $this->assertNotContains($title, $this->webDriver->getTitle());
    }

    
    public function acceptPopup()
    {
        if ($this->isPhantom()) {
            throw new ModuleException($this, 'PhantomJS does not support working with popups');
        }
        $this->webDriver->switchTo()->alert()->accept();
    }

    
    public function cancelPopup()
    {
        if ($this->isPhantom()) {
            throw new ModuleException($this, 'PhantomJS does not support working with popups');
        }
        $this->webDriver->switchTo()->alert()->dismiss();
    }

    
    public function seeInPopup($text)
    {
        if ($this->isPhantom()) {
            throw new ModuleException($this, 'PhantomJS does not support working with popups');
        }
        $this->assertContains($text, $this->webDriver->switchTo()->alert()->getText());
    }

    
    public function typeInPopup($keys)
    {
        if ($this->isPhantom()) {
            throw new ModuleException($this, 'PhantomJS does not support working with popups');
        }
        $this->webDriver->switchTo()->alert()->sendKeys($keys);
    }

    
    public function reloadPage()
    {
        $this->webDriver->navigate()->refresh();
    }

    
    public function moveBack()
    {
        $this->webDriver->navigate()->back();
        $this->debug($this->_getCurrentUri());
    }

    
    public function moveForward()
    {
        $this->webDriver->navigate()->forward();
        $this->debug($this->_getCurrentUri());
    }

    protected function getSubmissionFormFieldName($name)
    {
        if (substr($name, -2) === '[]') {
            return substr($name, 0, -2);
        }
        return $name;
    }

    
    public function submitForm($selector, array $params, $button = null)
    {
        $form = $this->match($this->webDriver, $selector);
        if (empty($form)) {
            throw new ElementNotFound($selector, "Form via CSS or XPath");
        }
        $form = reset($form);

        $fields = $form->findElements(
            WebDriverBy::cssSelector('input:enabled,textarea:enabled,select:enabled,input[type=hidden]')
        );
        foreach ($fields as $field) {
            $fieldName = $this->getSubmissionFormFieldName($field->getAttribute('name'));
            if (!isset($params[$fieldName])) {
                continue;
            }
            $value = $params[$fieldName];
            if (is_array($value) && $field->getTagName() !== 'select') {
                if ($field->getAttribute('type') === 'checkbox' || $field->getAttribute('type') === 'radio') {
                    $found = false;
                    foreach ($value as $index => $val) {
                        if (!is_bool($val) && $val === $field->getAttribute('value')) {
                            array_splice($params[$fieldName], $index, 1);
                            $value = $val;
                            $found = true;
                            break;
                        }
                    }
                    if (!$found && !empty($value) && is_bool(reset($value))) {
                        $value = array_pop($params[$fieldName]);
                    }
                } else {
                    $value = array_pop($params[$fieldName]);
                }
            }

            if ($field->getAttribute('type') === 'checkbox' || $field->getAttribute('type') === 'radio') {
                if ($value === true || $value === $field->getAttribute('value')) {
                    $this->checkOption($field);
                } else {
                    $this->uncheckOption($field);
                }
            } elseif ($field->getAttribute('type') === 'button' || $field->getAttribute('type') === 'submit') {
                continue;
            } elseif ($field->getTagName() === 'select') {
                $this->selectOption($field, $value);
            } else {
                $this->fillField($field, $value);
            }
        }

        $this->debugSection(
            'Uri',
            $form->getAttribute('action') ? $form->getAttribute('action') : $this->_getCurrentUri()
        );
        $this->debugSection('Method', $form->getAttribute('method') ? $form->getAttribute('method') : 'GET');
        $this->debugSection('Parameters', json_encode($params));

        $submitted = false;
        if (!empty($button)) {
            if (is_array($button)) {
                $buttonSelector = $this->getStrictLocator($button);
            } elseif ($button instanceof WebDriverBy) {
                $buttonSelector = $button;
            } else {
                $buttonSelector = WebDriverBy::name($button);
            }

            $els = $form->findElements($buttonSelector);

            if (!empty($els)) {
                $el = reset($els);
                $el->click();
                $submitted = true;
            }
        }

        if (!$submitted) {
            $form->submit();
        }

        $this->debugSection('Page', $this->_getCurrentUri());
    }

    
    public function waitForElementChange($element, \Closure $callback, $timeout = 30)
    {
        $el = $this->matchFirstOrFail($this->webDriver, $element);
        $checker = function () use ($el, $callback) {
            return $callback($el);
        };
        $this->webDriver->wait($timeout)->until($checker);
    }

    
    public function waitForElement($element, $timeout = 10)
    {
        $condition = WebDriverExpectedCondition::presenceOfElementLocated($this->getLocator($element));
        $this->webDriver->wait($timeout)->until($condition);
    }

    
    public function waitForElementVisible($element, $timeout = 10)
    {
        $condition = WebDriverExpectedCondition::visibilityOfElementLocated($this->getLocator($element));
        $this->webDriver->wait($timeout)->until($condition);
    }

    
    public function waitForElementNotVisible($element, $timeout = 10)
    {
        $condition = WebDriverExpectedCondition::invisibilityOfElementLocated($this->getLocator($element));
        $this->webDriver->wait($timeout)->until($condition);
    }

    
    public function waitForText($text, $timeout = 10, $selector = null)
    {
        if (!$selector) {
            $condition = WebDriverExpectedCondition::textToBePresentInElement(WebDriverBy::xpath('//body'), $text);
            $this->webDriver->wait($timeout)->until($condition);
            return;
        }

        $condition = WebDriverExpectedCondition::textToBePresentInElement($this->getLocator($selector), $text);
        $this->webDriver->wait($timeout)->until($condition);
    }

    
    public function wait($timeout)
    {
        if ($timeout >= 1000) {
            throw new TestRuntimeException(
                "
                Waiting for more then 1000 seconds: 16.6667 mins\n
                Please note that wait method accepts number of seconds as parameter."
            );
        }
        sleep($timeout);
    }

    
    public function executeInSelenium(\Closure $function)
    {
        return $function($this->webDriver);
    }

    
    public function switchToWindow($name = null)
    {
        $this->webDriver->switchTo()->window($name);
    }

    
    public function switchToIFrame($name = null)
    {
        if (is_null($name)) {
            $this->webDriver->switchTo()->defaultContent();
        } else {
            $this->webDriver->switchTo()->frame($name);
        }
    }

    
    public function waitForJS($script, $timeout = 5)
    {
        $condition = function ($wd) use ($script) {
            return $wd->executeScript($script);
        };
        $this->webDriver->wait($timeout)->until($condition);
    }

    
    public function executeJS($script)
    {
        return $this->webDriver->executeScript($script);
    }

    
    public function maximizeWindow()
    {
        $this->webDriver->manage()->window()->maximize();
    }

    
    public function dragAndDrop($source, $target)
    {
        $snodes = $this->matchFirstOrFail($this->webDriver, $source);
        $tnodes = $this->matchFirstOrFail($this->webDriver, $target);

        $action = new WebDriverActions($this->webDriver);
        $action->dragAndDrop($snodes, $tnodes)->perform();
    }

    
    public function moveMouseOver($cssOrXPath = null, $offsetX = null, $offsetY = null)
    {
        $where = null;
        if (null !== $cssOrXPath) {
            $el = $this->matchFirstOrFail($this->webDriver, $cssOrXPath);
            $where = $el->getCoordinates();
        }

        $this->webDriver->getMouse()->mouseMove($where, $offsetX, $offsetY);
    }

    
    public function clickWithLeftButton($cssOrXPath = null, $offsetX = null, $offsetY = null)
    {
        $this->moveMouseOver($cssOrXPath, $offsetX, $offsetY);
        $this->webDriver->getMouse()->click();
    }

    
    public function clickWithRightButton($cssOrXPath = null, $offsetX = null, $offsetY = null)
    {
        $this->moveMouseOver($cssOrXPath, $offsetX, $offsetY);
        $this->webDriver->getMouse()->contextClick();
    }

    
    public function pauseExecution()
    {
        Debug::pause();
    }

    
    public function doubleClick($cssOrXPath)
    {
        $el = $this->matchFirstOrFail($this->webDriver, $cssOrXPath);
        $this->webDriver->getMouse()->doubleClick($el->getCoordinates());
    }

    
    protected function match($page, $selector, $throwMalformed = true)
    {
        if (is_array($selector)) {
            try {
                return $page->findElements($this->getStrictLocator($selector));
            } catch (InvalidSelectorException $e) {
                throw new MalformedLocatorException(key($selector) . ' => ' . reset($selector), "Strict locator");
            } catch (InvalidElementStateException $e) {
                if ($this->isPhantom() and $e->getResults()['status'] == 12) {
                    throw new MalformedLocatorException(
                        key($selector) . ' => ' . reset($selector),
                        "Strict locator ".$e->getCode()
                    );
                }
            }
        }
        if ($selector instanceof WebDriverBy) {
            try {
                return $page->findElements($selector);
            } catch (InvalidSelectorException $e) {
                throw new MalformedLocatorException(
                    sprintf(
                        "WebDriverBy::%s('%s')",
                        $selector->getMechanism(),
                        $selector->getValue()
                    ),
                    'WebDriver'
                );
            }
        }
        $isValidLocator = false;
        $nodes = [];
        try {
            if (Locator::isID($selector)) {
                $isValidLocator = true;
                $nodes = $page->findElements(WebDriverBy::id(substr($selector, 1)));
            }
            if (Locator::isClass($selector)) {
                $isValidLocator = true;
                $nodes = $page->findElements(WebDriverBy::className(substr($selector, 1)));
            }
            if (empty($nodes) and Locator::isCSS($selector)) {
                $isValidLocator = true;
                try {
                    $nodes = $page->findElements(WebDriverBy::cssSelector($selector));
                } catch (InvalidElementStateException $e) {
                    $nodes = $page->findElements(WebDriverBy::linkText($selector));
                }
            }
            if (empty($nodes) and Locator::isXPath($selector)) {
                $isValidLocator = true;
                $nodes = $page->findElements(WebDriverBy::xpath($selector));
            }
        } catch (InvalidSelectorException $e) {
            throw new MalformedLocatorException($selector);
        }
        if (!$isValidLocator and $throwMalformed) {
            throw new MalformedLocatorException($selector);
        }
        return $nodes;
    }

    
    protected function getStrictLocator(array $by)
    {
        $type = key($by);
        $locator = $by[$type];
        switch ($type) {
            case 'id':
                return WebDriverBy::id($locator);
            case 'name':
                return WebDriverBy::name($locator);
            case 'css':
                return WebDriverBy::cssSelector($locator);
            case 'xpath':
                return WebDriverBy::xpath($locator);
            case 'link':
                return WebDriverBy::linkText($locator);
            case 'class':
                return WebDriverBy::className($locator);
            default:
                throw new MalformedLocatorException(
                    "$by => $locator",
                    "Strict locator can be either xpath, css, id, link, class, name: "
                );
        }
    }

    
    protected function matchFirstOrFail($page, $selector)
    {
        $els = $this->match($page, $selector);
        if (!count($els)) {
            throw new ElementNotFound($selector, "CSS or XPath");
        }
        return reset($els);
    }

    
    public function pressKey($element, $char)
    {
        $el = $this->matchFirstOrFail($this->webDriver, $element);
        $args = func_get_args();
        array_shift($args);
        $keys = [];
        foreach ($args as $key) {
            $keys[] = $this->convertKeyModifier($key);
        }
        $el->sendKeys($keys);
    }

    protected function convertKeyModifier($keys)
    {
        if (!is_array($keys)) {
            return $keys;
        }
        if (!isset($keys[1])) {
            return $keys;
        }
        list($modifier, $key) = $keys;

        switch ($modifier) {
            case 'ctrl':
            case 'control':
                return [WebDriverKeys::CONTROL, $key];
            case 'alt':
                return [WebDriverKeys::ALT, $key];
            case 'shift':
                return [WebDriverKeys::SHIFT, $key];
            case 'meta':
                return [WebDriverKeys::META, $key];
        }
        return $keys;
    }

    protected function assertNodesContain($text, $nodes, $selector = null)
    {
        $this->assertThat($nodes, new WebDriverConstraint($text, $this->_getCurrentUri()), $selector);
    }

    protected function assertNodesNotContain($text, $nodes, $selector = null)
    {
        $this->assertThat($nodes, new WebDriverConstraintNot($text, $this->_getCurrentUri()), $selector);
    }

    protected function assertPageContains($needle, $message = '')
    {
        $this->assertThat(
            htmlspecialchars_decode($this->getVisibleText()),
            new PageConstraint($needle, $this->_getCurrentUri()),
            $message
        );
    }

    protected function assertPageNotContains($needle, $message = '')
    {
        $this->assertThatItsNot(
            htmlspecialchars_decode($this->getVisibleText()),
            new PageConstraint($needle, $this->_getCurrentUri()),
            $message
        );
    }

    protected function assertPageSourceContains($needle, $message = '')
    {
        $this->assertThat(
            $this->webDriver->getPageSource(),
            new PageConstraint($needle, $this->_getCurrentUri()),
            $message
        );
    }

    protected function assertPageSourceNotContains($needle, $message = '')
    {
        $this->assertThatItsNot(
            $this->webDriver->getPageSource(),
            new PageConstraint($needle, $this->_getCurrentUri()),
            $message
        );
    }

    
    public function appendField($field, $value)
    {
        $el = $this->findField($field);

        switch ($el->getTagName()) {
            //Multiple select
            case "select":
                $matched = false;
                $wdSelect = new WebDriverSelect($el);
                try {
                    $wdSelect->selectByVisibleText($value);
                    $matched = true;
                } catch (NoSuchElementException $e) {
                    // exception treated at the end
                }

                try {
                    $wdSelect->selectByValue($value);
                    $matched = true;
                } catch (NoSuchElementException $e) {
                    // exception treated at the end
                }
                if ($matched) {
                    return;
                }

                throw new ElementNotFound(json_encode($value), "Option inside $field matched by name or value");
            case "textarea":
                $el->sendKeys($value);
                return;
            case "div": //allows for content editable divs
                $el->sendKeys(WebDriverKeys::END);
                $el->sendKeys($value);
                return;
            //Text, Checkbox, Radio
            case "input":
                $type = $el->getAttribute('type');

                if ($type == 'checkbox') {
                    //Find by value or css,id,xpath
                    $field = $this->findCheckable($this->webDriver, $value, true);
                    if (!$field) {
                        throw new ElementNotFound($value, "Checkbox or Radio by Label or CSS or XPath");
                    }
                    if ($field->isSelected()) {
                        return;
                    }
                    $field->click();
                    return;
                } elseif ($type == 'radio') {
                    $this->selectOption($field, $value);
                    return;
                } else {
                    $el->sendKeys($value);
                    return;
                }
        }

        throw new ElementNotFound($field, "Field by name, label, CSS or XPath");
    }

    
    protected function matchVisible($selector)
    {
        $els = $this->match($this->webDriver, $selector);
        $nodes = array_filter(
            $els,
            function (WebDriverElement $el) {
                return $el->isDisplayed();
            }
        );
        return $nodes;
    }

    
    protected function getLocator($selector)
    {
        if ($selector instanceof WebDriverBy) {
            return $selector;
        }
        if (is_array($selector)) {
            return $this->getStrictLocator($selector);
        }
        if (Locator::isID($selector)) {
            return WebDriverBy::id(substr($selector, 1));
        }
        if (Locator::isCSS($selector)) {
            return WebDriverBy::cssSelector($selector);
        }
        if (Locator::isXPath($selector)) {
            return WebDriverBy::xpath($selector);
        }
        throw new \InvalidArgumentException("Only CSS or XPath allowed");
    }

    
    public function saveSessionSnapshot($name)
    {
        $this->sessionSnapshots[$name] = [];

        foreach ($this->webDriver->manage()->getCookies() as $cookie) {
            if (in_array(trim($cookie['name']), [LocalServer::COVERAGE_COOKIE, LocalServer::COVERAGE_COOKIE])) {
                continue;
            }

            if ($this->cookieDomainMatchesConfigUrl($cookie)) {
                $this->sessionSnapshots[$name][] = $cookie;
            }
        }

        $this->debugSection('Snapshot', "Saved \"$name\" session snapshot");
    }

    
    public function loadSessionSnapshot($name)
    {
        if (!isset($this->sessionSnapshots[$name])) {
            return false;
        }
        foreach ($this->sessionSnapshots[$name] as $cookie) {
            $this->webDriver->manage()->addCookie($cookie);
        }
        $this->debugSection('Snapshot', "Restored \"$name\" session snapshot");
        return true;
    }

    
    private function cookieDomainMatchesConfigUrl(array $cookie)
    {
        if (!array_key_exists('domain', $cookie)) {
            return true;
        }

        $setCookie = new SetCookie();
        $setCookie->setDomain($cookie['domain']);

        return $setCookie->matchesDomain(parse_url($this->config['url'], PHP_URL_HOST));
    }

    
    protected function isPhantom()
    {
        return strpos($this->config['browser'], 'phantom') === 0;
    }

    
    public function scrollTo($selector, $offsetX = null, $offsetY = null)
    {
        $el = $this->matchFirstOrFail($this->webDriver, $selector);
        $x = $el->getLocation()->getX() + $offsetX;
        $y = $el->getLocation()->getY() + $offsetY;
        $this->webDriver->executeScript("window.scrollTo($x, $y)");
    }

    
    public function openNewTab()
    {
        $this->executeJS("window.open('about:blank','_blank');");
        $this->switchToNextTab();
    }

    
    public function closeTab()
    {
        $prevTab = $this->getRelativeTabHandle(-1);
        $this->webDriver->close();
        $this->webDriver->switchTo()->window($prevTab);
    }

    
    public function switchToNextTab($offset = 1)
    {
        $tab = $this->getRelativeTabHandle($offset);
        $this->webDriver->switchTo()->window($tab);
    }

    
    public function switchToPreviousTab($offset = 1)
    {
        $this->switchToNextTab(0 - $offset);
    }

    protected function getRelativeTabHandle($offset)
    {
        if ($this->isPhantom()) {
            throw new ModuleException($this, "PhantomJS doesn't support tab actions");
        }
        $handle = $this->webDriver->getWindowHandle();
        $handles = $this->webDriver->getWindowHandles();
        $idx = array_search($handle, $handles);
        return $handles[($idx + $offset) % count($handles)];
    }


}
