<?php
namespace Codeception\Module;

use Codeception\Exception\ModuleException as ModuleException;
use Codeception\Exception\ModuleConfigException as ModuleConfigException;
use Codeception\Lib\Driver\Facebook as FacebookDriver;
use Codeception\Lib\Interfaces\DependsOnModule;
use Codeception\Lib\Interfaces\RequiresPackage;
use Codeception\Module as BaseModule;


class Facebook extends BaseModule implements DependsOnModule, RequiresPackage
{
    protected $requiredFields = ['app_id', 'secret'];

    
    protected $facebook;

    
    protected $testUser = [];

    
    protected $browserModule;

    protected $dependencyMessage = <<<EOF
Example configuring PhpBrowser
--
modules
    enabled:
        - Facebook:
            depends: PhpBrowser
            app_id: 412345678901234
            secret: ccb79c1b0fdff54e4f7c928bf233aea5
            test_user:
                name: FacebookGuy
                locale: uk_UA
                permissions: [email, publish_stream]
EOF;

    public function _requires()
    {
        return ['Facebook\Facebook' => '"facebook/graph-sdk": "~5.3"'];
    }

    public function _depends()
    {
        return ['Codeception\Module\PhpBrowser' => $this->dependencyMessage];
    }

    public function _inject(PhpBrowser $browserModule)
    {
        $this->browserModule = $browserModule;
    }

    protected function deleteTestUser()
    {
        if (array_key_exists('id', $this->testUser)) {
            // make api-call for test user deletion
            $this->facebook->deleteTestUser($this->testUser['id']);
            $this->testUser = [];
        }
    }

    public function _initialize()
    {
        if (!array_key_exists('test_user', $this->config)) {
            $this->config['test_user'] = [
                'permissions' => [],
                'name' => 'Codeception Testuser'
            ];
        } elseif (!array_key_exists('permissions', $this->config['test_user'])) {
            $this->config['test_user']['permissions'] = [];
        } elseif (!array_key_exists('name', $this->config['test_user'])) {
            $this->config['test_user']['name'] = "codeception testuser";
        }

        $this->facebook = new FacebookDriver(
            [
                'app_id' => $this->config['app_id'],
                'secret' => $this->config['secret'],
            ],
            function ($title, $message) {
                if (version_compare(PHP_VERSION, '5.4', '>=')) {
                    $this->debugSection($title, $message);
                }
            }
        );
    }

    public function _afterSuite()
    {
        $this->deleteTestUser();
    }

    
    public function haveFacebookTestUserAccount($renew = false)
    {
        if ($renew) {
            $this->deleteTestUser();
        }

        // make api-call for test user creation only if it's not yet created
        if (!array_key_exists('id', $this->testUser)) {
            $this->testUser = $this->facebook->createTestUser(
                $this->config['test_user']['name'],
                $this->config['test_user']['permissions']
            );
        }
    }

    
    public function haveTestUserLoggedInOnFacebook()
    {
        if (!array_key_exists('id', $this->testUser)) {
            throw new ModuleException(
                __CLASS__,
                'Facebook test user was not found. Did you forget to create one?'
            );
        }

        $callbackUrl = $this->browserModule->_getUrl();
        $this->browserModule->amOnUrl('https://facebook.com/login');
        $this->browserModule->submitForm('#login_form', [
            'email' => $this->grabFacebookTestUserEmail(),
            'pass' => $this->grabFacebookTestUserPassword()
        ]);
        // if login in successful we are back on login screen:
        $this->browserModule->dontSeeInCurrentUrl('/login');
        $this->browserModule->amOnUrl($callbackUrl);
    }

    
    public function grabFacebookTestUserAccessToken()
    {
        return $this->testUser['access_token'];
    }

    
    public function grabFacebookTestUserId()
    {
        return $this->testUser['id'];
    }

    
    public function grabFacebookTestUserEmail()
    {
        return $this->testUser['email'];
    }

    
    public function grabFacebookTestUserLoginUrl()
    {
        return $this->testUser['login_url'];
    }

    public function grabFacebookTestUserPassword()
    {
        return $this->testUser['password'];
    }

    
    public function grabFacebookTestUserName()
    {
        if (!array_key_exists('profile', $this->testUser)) {
            $this->testUser['profile'] = $this->facebook->getTestUserInfo($this->grabFacebookTestUserAccessToken());
        }

        return $this->testUser['profile']['name'];
    }

    
    public function postToFacebookAsTestUser($params)
    {
        $this->facebook->sendPostToFacebook($this->grabFacebookTestUserAccessToken(), $params);
    }

    
    public function seePostOnFacebookWithAttachedPlace($placeId)
    {
        $token = $this->grabFacebookTestUserAccessToken();
        $this->debugSection('Access Token', $token);
        $place = $this->facebook->getVisitedPlaceTagForTestUser($placeId, $token);
        $this->assertEquals($placeId, $place['id'], "The place was not found on facebook page");
    }

    
    public function seePostOnFacebookWithMessage($message)
    {
        $posts = $this->facebook->getLastPostsForTestUser($this->grabFacebookTestUserAccessToken());
        $facebook_post_message = '';
        $this->assertNotEquals($message, $facebook_post_message, "You can not test for an empty message post");
        if ($posts['data']) {
            foreach ($posts['data'] as $post) {
                if (array_key_exists('message', $post) && ($post['message'] == $message)) {
                    $facebook_post_message = $post['message'];
                }
            }
        }
        $this->assertEquals($message, $facebook_post_message, "The post message was not found on facebook page");
    }
}
