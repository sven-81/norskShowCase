<?php

declare(strict_types=1);

namespace norsk\api\app\config;

use norsk\api\app\identityAccessManagement\AuthenticationAlgorithm;
use norsk\api\app\identityAccessManagement\AuthenticationKey;
use norsk\api\app\identityAccessManagement\JwtAudience;
use norsk\api\app\identityAccessManagement\JwtConfig;
use norsk\api\app\identityAccessManagement\JwtSubject;
use norsk\api\app\logging\AppLoggerConfig;
use norsk\api\app\response\Url;
use norsk\api\user\Pepper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(AppConfig::class)]
class AppConfigTest extends TestCase
{
    private const string APP_CONFIG_PATH = __DIR__ . '/../resources/appConfig.ini';
    private const string FAKE_CONFIG_PATH = __DIR__ . '/../resources/fakeConfig.ini';
    private const string DEBUG_CONFIG_PATH = __DIR__ . '/../resources/debugConfig.ini';
    private const int E_ERROR_E_PARSE = 5;
    private const int E_ALL = 32767;

    private AppConfig $config;


    protected function setUp(): void
    {
        $this->config = AppConfig::fromPath(Path::fromString(self::APP_CONFIG_PATH));
    }


    /**
     * @return string[][]
     */
    public static function provideSecondLevelKeys(): array
    {
        $configArray = self::getConfigArray();

        return [
            'path' => [
                self::configWithRemovedKey($configArray, 'logs', 'path'),
                'path',
            ],
            'displayErrorDetails' => [
                self::configWithRemovedKey($configArray, 'logs', 'displayErrorDetails'),
                'displayErrorDetails',
            ],
            'logErrors' => [
                self::configWithRemovedKey($configArray, 'logs', 'logErrors'),
                'logErrors',
            ],
            'logErrorDetails' => [
                self::configWithRemovedKey($configArray, 'logs', 'logErrorDetails'),
                'logErrorDetails',
            ],
            'method' => [
                self::configWithRemovedKey($configArray, 'auth', 'method'),
                'method',
            ],
            'key' => [
                self::configWithRemovedKey($configArray, 'auth', 'key'),
                'key',
            ],
            'sub' => [
                self::configWithRemovedKey($configArray, 'auth', 'sub'),
                'sub',
            ],
            'aud' => [
                self::configWithRemovedKey($configArray, 'auth', 'aud'),
                'aud',
            ],
            'addition' => [
                self::configWithRemovedKey($configArray, 'auth', 'addition'),
                'addition',
            ],
            'originUrl' => [
                self::configWithRemovedKey($configArray, 'auth', 'originUrl'),
                'originUrl',
            ],
        ];
    }


    private static function getConfigArray(): array
    {
        $random = bin2hex(random_bytes(32));

        return [
            'logs' => [
                'path' => 'x',
                'displayErrorDetails' => 'x',
                'logErrors' => 'x',
                'logErrorDetails' => 'x',
            ],
            'auth' => [
                'method' => 'HS256',
                'key' => 'x',
                'sub' => 'x',
                'aud' => 'x',
                'addition' => $random,
                'originUrl' => 'x',
            ],
        ];
    }


    private static function configWithRemovedKey(
        array $configArray,
        string $firstConfigKey,
        string $subConfigKey
    ): string {
        unset($configArray[$firstConfigKey][$subConfigKey]);

        $config = '';

        foreach ($configArray as $key => $value) {
            $config .= "[$key]\r";

            foreach ($value as $subKey => $subValue) {
                $config .= "$subKey = $subValue\r";
            }

            $config .= "\r";
        }

        return $config;
    }


    /**
     * @return string[][]
     */
    public static function provideSecondLevelValues(): array
    {
        $configArray = self::getConfigArray();

        return [
            'path' => [
                self::configWithRemovedValue($configArray, 'logs', 'path'),
                'path',
            ],
            'displayErrorDetails' => [
                self::configWithRemovedValue($configArray, 'logs', 'displayErrorDetails'),
                'displayErrorDetails',
            ],
            'logErrors' => [
                self::configWithRemovedValue($configArray, 'logs', 'logErrors'),
                'logErrors',
            ],
            'logErrorDetails' => [
                self::configWithRemovedValue($configArray, 'logs', 'logErrorDetails'),
                'logErrorDetails',
            ],
            'method' => [
                self::configWithRemovedValue($configArray, 'auth', 'method'),
                'method',
            ],
            'key' => [
                self::configWithRemovedValue($configArray, 'auth', 'key'),
                'key',
            ],
            'sub' => [
                self::configWithRemovedValue($configArray, 'auth', 'sub'),
                'sub',
            ],
            'aud' => [
                self::configWithRemovedValue($configArray, 'auth', 'aud'),
                'aud',
            ],
            'addition' => [
                self::configWithRemovedValue($configArray, 'auth', 'addition'),
                'addition',
            ],
            'originUrl' => [
                self::configWithRemovedValue($configArray, 'auth', 'originUrl'),
                'originUrl',
            ],
        ];
    }


    private static function configWithRemovedValue(
        array $configArray,
        string $firstConfigKey,
        string $subConfigKey
    ): string {
        $configArray[$firstConfigKey][$subConfigKey] = '';

        $config = '';

        foreach ($configArray as $key => $value) {
            $config .= "[$key]\r";

            foreach ($value as $subKey => $subValue) {
                $config .= "$subKey = $subValue\r";
            }

            $config .= "\r";
        }

        return $config;
    }


    public static function provideFirstLevelKeys(): array
    {
        return [
            'logs' => ["[empty]\rempty=x\r[auth]\rmethod=x", 'logs'],
            'auth' => ["[logs]\rpath=s\r[empty]\rmethod=x", 'auth'],
        ];
    }


    /**
     * @return string[][]
     */
    public static function provideFirstLevelValues(): array
    {
        return [
            0 => ["[logs]\r[auth]\rfo=o", 'logs'],
            1 => ["[logs]\rfo=o\r[auth]\r", 'auth'],
        ];
    }


    public function testCannotReadConfig(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot read file:');

        AppConfig::fromPath(Path::fromString('/foo'));
    }


    public function testCanCreateFromPath(): void
    {
        self::assertInstanceOf(AppConfig::class, $this->config);
    }


    public function testCanGetLogPath(): void
    {
        self::assertSame(
            realpath(
                __DIR__ . '/../../../../logs/'
            ),
            realpath($this->config->getLogPath()->asString())
        );
    }


    public function testCanGetJwtCredentials(): void
    {
        self::assertEquals(
            JwtConfig::fromCredentials(
                AuthenticationKey::by('abc123'),
                AuthenticationAlgorithm::by('HS512'),
                JwtSubject::by('norsk app'),
                JwtAudience::by('Norsk Client')
            ),
            $this->config->getJwtConfig()
        );
    }


    public function testCenGetPepper(): void
    {
        self::assertEquals(
            Pepper::by('iffBYerIwfYhu05X08m6GvN4wo7a6yqL'),
            $this->config->getPepper()
        );
    }


    public function testCenGetUrl(): void
    {
        self::assertEquals(
            Url::by('http://url'),
            $this->config->getUrl()
        );
    }


    public function testCenGetAppLoggerConfig(): void
    {
        self::assertEquals(
            AppLoggerConfig::by(Path::fromString('/app/api/src/app/config/../../../logs'), true, true, true),
            $this->config->getAppLoggerConfig()
        );
    }


    #[DataProvider('provideFirstLevelKeys')]
    public function testThrowsExceptionIfConfigFirstLevelKeyIsMissing(string $key, string $name): void
    {
        $this->createTempConfig($key);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Key is not set: ' . $name);

        AppConfig::fromPath(Path::fromString(self::FAKE_CONFIG_PATH));
    }


    private function createTempConfig(string $content): void
    {
        file_put_contents(self::FAKE_CONFIG_PATH, $content);
    }


    #[DataProvider('provideFirstLevelValues')]
    public function testThrowsExceptionIfConfigFirstLevelKeyIsEmpty(string $key, string $name): void
    {
        $this->createTempConfig($key);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Key is empty: ' . $name);

        AppConfig::fromPath(Path::fromString(self::FAKE_CONFIG_PATH));
    }


    #[DataProvider('provideSecondLevelKeys')]
    public function testThrowsExceptionIfConfigParamKeyIsMissing(string $configWithoutKey, string $name): void
    {
        $this->createTempConfig($configWithoutKey);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Key is not set: ' . $name);

        AppConfig::fromPath(Path::fromString(self::FAKE_CONFIG_PATH));
    }


    #[DataProvider('provideSecondLevelValues')]
    public function testThrowsExceptionIfConfigParamValueIsEmpty(string $key, string $name): void
    {
        $this->createTempConfig($key);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Key is empty: ' . $name);

        AppConfig::fromPath(Path::fromString(self::FAKE_CONFIG_PATH));
    }


    public function testCanSetErrorLevelToProductionLevelWithoutWarnings(): void
    {
        $this->setLevelToAllForTests();
        AppConfig::fromPath(Path::fromString(self::APP_CONFIG_PATH));
        $productionCodeLevel = error_reporting();
        $this->setLevelToAllForTests();

        $this->assertSame(self::E_ERROR_E_PARSE, $productionCodeLevel, 'E_ERROR | E_PARSE aktiv');
    }


    public function testDoestNotSetErrorLevelToProductionLevelWithoutWarningsIfDebugIsInConfig(): void
    {
        $this->setLevelToAllForTests();
        AppConfig::fromPath(Path::fromString(self::DEBUG_CONFIG_PATH));
        $productionCodeLevel = error_reporting();
        $this->setLevelToAllForTests();

        $this->assertSame(self::E_ALL, $productionCodeLevel, 'E_ALL aktiv');
    }


    private function setLevelToAllForTests(): void
    {
        error_reporting(E_ALL);
    }


    protected function tearDown(): void
    {
        if (file_exists(self::FAKE_CONFIG_PATH)) {
            unlink(self::FAKE_CONFIG_PATH);
        }
    }
}
