<?php

/*
 * This file is part of the overtrue/socialite.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

use Overtrue\Socialite\Providers\WeChatProvider as RealWeChatProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class WechatProviderTest extends TestCase
{
    public function testWeChatProviderHasCorrectlyRedirectResponse()
    {
        $response = (new WeChatProvider(Request::create('foo'), [
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect' => 'http://localhost/socialite/callback.php',
        ]))->redirect();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertStringStartsWith('https://open.weixin.qq.com/connect/qrconnect', $response->getTargetUrl());
        $this->assertRegExp('/redirect_uri=http%3A%2F%2Flocalhost%2Fsocialite%2Fcallback.php/', $response->getTargetUrl());
    }

    public function testWeChatProviderTokenUrlAndRequestFields()
    {
        $provider = new WeChatProvider(Request::create('foo'), [
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect' => 'http://localhost/socialite/callback.php',
        ]);

        $this->assertSame('https://api.weixin.qq.com/sns/oauth2/access_token', $provider->tokenUrl());
        $this->assertSame([
            'appid' => 'client_id',
            'secret' => 'client_secret',
            'code' => 'iloveyou',
            'grant_type' => 'authorization_code',
        ], $provider->tokenFields('iloveyou'));

        $this->assertSame([
            'appid' => 'client_id',
            'redirect_uri' => 'http://localhost/socialite/callback.php',
            'response_type' => 'code',
            'scope' => 'snsapi_login',
            'state' => 'wechat-state',
            'connect_redirect' => 1,
        ], $provider->codeFields('wechat-state'));
    }

    public function testOpenPlatformComponent()
    {
        $provider = new WeChatProvider(Request::create('foo'), [
            'client_id' => 'client_id',
            'client_secret' => null,
            'redirect' => 'redirect-url',
        ]);
        $provider->component(new WeChatComponent());
        $this->assertSame([
            'appid' => 'client_id',
            'redirect_uri' => 'redirect-url',
            'response_type' => 'code',
            'scope' => 'snsapi_base',
            'state' => 'state',
            'connect_redirect' => 1,
            'component_appid' => 'component-app-id',
        ], $provider->codeFields('state'));

        $this->assertSame([
            'appid' => 'client_id',
            'component_appid' => 'component-app-id',
            'component_access_token' => 'token',
            'code' => 'simcode',
            'grant_type' => 'authorization_code',
        ], $provider->tokenFields('simcode'));

        $this->assertSame('https://api.weixin.qq.com/sns/oauth2/component/access_token', $provider->tokenUrl());
    }

    public function testOpenPlatformComponentWithCustomParameters()
    {
        $provider = new WeChatProvider(Request::create('foo'), [
            'client_id' => 'client_id',
            'client_secret' => null,
            'redirect' => 'redirect-url',
        ]);
        $provider->component(new WeChatComponent());
        $provider->with(['foo' => 'bar']);

        $fields = $provider->codeFields('wechat-state');

        $this->assertArrayHasKey('foo', $fields);
        $this->assertSame('bar', $fields['foo']);
    }
}

trait ProviderTrait
{
    public function tokenUrl()
    {
        return $this->getTokenUrl();
    }

    public function tokenFields($code)
    {
        return $this->getTokenFields($code);
    }

    public function codeFields($state = null)
    {
        return $this->getCodeFields($state);
    }
}

class WeChatProvider extends RealWeChatProvider
{
    use ProviderTrait;
}

class WeChatComponent implements \Overtrue\Socialite\WeChatComponentInterface
{
    public function getAppId()
    {
        return 'component-app-id';
    }

    public function getToken()
    {
        return 'token';
    }
}
