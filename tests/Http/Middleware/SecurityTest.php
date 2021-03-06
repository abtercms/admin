<?php

declare(strict_types=1);

namespace AbterPhp\Admin\Http\Middleware;

use AbterPhp\Admin\Config\Routes as RoutesConfig;
use AbterPhp\Framework\Constant\Env;
use AbterPhp\Framework\Environments\Environment;
use AbterPhp\Framework\Exception\Security as SecurityException;
use Opulence\Cache\ICacheBridge;
use Opulence\Http\Requests\Request;
use Opulence\Http\Responses\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SecurityTest extends TestCase
{
    /** @var Security - System Under Test */
    protected Security $sut;

    /** @var MockObject|ICacheBridge */
    protected $cacheBridgeMock;

    /** @var MockObject|RoutesConfig */
    protected $routesConfigMock;

    public function setUp(): void
    {
        $this->cacheBridgeMock  = $this->createMock(ICacheBridge::class);
        $this->routesConfigMock = $this->createMock(RoutesConfig::class);

        $this->sut = new Security($this->cacheBridgeMock, $this->routesConfigMock);
    }

    public function testHandleRunsChecksIfNoEnvironmentNameIsSet()
    {
        Environment::setVar(Env::ENV_NAME, Environment::PRODUCTION);

        $this->cacheBridgeMock->expects($this->once())->method('has')->willReturn(true);

        $requestStub  = new Request([], [], [], [], [], [], null);
        $responseStub = new Response();

        $next = function () use ($responseStub) {
            return $responseStub;
        };

        $actualResult = $this->sut->handle($requestStub, $next);

        $this->assertSame($responseStub, $actualResult);
    }

    public function testHandleSkipsChecksIfNotInProduction()
    {
        Environment::setVar(Env::ENV_NAME, Environment::STAGING);

        $this->cacheBridgeMock->expects($this->never())->method('has');

        $env          = [
            Env::ENV_NAME => Environment::STAGING,
        ];
        $requestStub  = new Request([], [], [], [], [], $env, null);
        $responseStub = new Response();


        $next = function () use ($responseStub) {
            return $responseStub;
        };

        $actualResult = $this->sut->handle($requestStub, $next);

        $this->assertSame($responseStub, $actualResult);
    }

    public function testHandleRunsChecksIfInProduction()
    {
        Environment::setVar(Env::ENV_NAME, Environment::PRODUCTION);

        $this->cacheBridgeMock->expects($this->once())->method('has')->willReturn(true);

        $env          = [
            Env::ENV_NAME => Environment::PRODUCTION,
        ];
        $requestStub  = new Request([], [], [], [], [], $env, null);
        $responseStub = new Response();

        $next = function () use ($responseStub) {
            return $responseStub;
        };

        $actualResult = $this->sut->handle($requestStub, $next);

        $this->assertSame($responseStub, $actualResult);
    }

    /**
     * @return string[][]
     */
    public function checksThrowSecurityExceptionProvider(): array
    {
        return [
            [Security::TEST_LOGIN_PATH, '/bar', '/baz', 'quix'],
            ['/foo', Security::TEST_ADMIN_BASE_PATH, '/baz', 'quix'],
            ['/foo', '/bar', Security::TEST_API_BASE_PATH, 'quix'],
            ['/foo', '/bar', '/baz', Security::TEST_OAUTH2_PRIVATE_KEY_PASSWORD],
        ];
    }

    /**
     * @dataProvider checksThrowSecurityExceptionProvider
     *
     * @param string $loginPath
     * @param string $adminBasePath
     * @param string $apiBasePath
     * @param string $oauth2PrivateKeyPassword
     */
    public function testHandleChecksThrowSecurityExceptionOnFailure(
        string $loginPath,
        string $adminBasePath,
        string $apiBasePath,
        string $oauth2PrivateKeyPassword
    ) {
        Environment::setVar(Env::ENV_NAME, Environment::PRODUCTION);

        $this->expectException(SecurityException::class);

        $this->routesConfigMock->expects($this->any())->method('getLoginPath')->willReturn($loginPath);
        $this->routesConfigMock->expects($this->any())->method('getAdminBasePath')->willReturn($adminBasePath);
        $this->routesConfigMock->expects($this->any())->method('getApiBasePath')->willReturn($apiBasePath);

        $this->cacheBridgeMock->expects($this->once())->method('has')->willReturn(false);

        $env          = [
            Env::ENV_NAME                    => Environment::PRODUCTION,
            Env::OAUTH2_PRIVATE_KEY_PASSWORD => $oauth2PrivateKeyPassword,
        ];
        $requestStub  = new Request([], [], [], [], [], $env, null);
        $responseStub = new Response();

        $next = function () use ($responseStub) {
            return $responseStub;
        };

        $this->sut->handle($requestStub, $next);
    }

    public function testHandleSetsSessionIfChecksWereRun()
    {
        $loginPath                = '/foo';
        $adminBasePath            = '/bar';
        $apiBasePath              = '/baz';
        $oauth2PrivateKeyPassword = 'quix';

        $this->routesConfigMock->expects($this->any())->method('getLoginPath')->willReturn($loginPath);
        $this->routesConfigMock->expects($this->any())->method('getAdminBasePath')->willReturn($adminBasePath);
        $this->routesConfigMock->expects($this->any())->method('getApiBasePath')->willReturn($apiBasePath);

        $this->cacheBridgeMock->expects($this->any())->method('has')->willReturn(false);
        $this->cacheBridgeMock->expects($this->once())->method('set')->willReturn(true);

        $env          = [
            Env::ENV_NAME                    => Environment::PRODUCTION,
            Env::OAUTH2_PRIVATE_KEY_PASSWORD => $oauth2PrivateKeyPassword,
        ];
        $requestStub  = new Request([], [], [], [], [], $env, null);
        $responseStub = new Response();

        $next = function () use ($responseStub) {
            return $responseStub;
        };

        $actualResult = $this->sut->handle($requestStub, $next);

        $this->assertSame($responseStub, $actualResult);
    }
}
