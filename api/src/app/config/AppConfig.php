<?php

declare(strict_types=1);

namespace norsk\api\app\config;

use norsk\api\app\config\exceptions\KeyHasEmptyValueException;
use norsk\api\app\config\exceptions\KeyIsNotSetException;
use norsk\api\app\identityAccessManagement\AuthenticationAlgorithm;
use norsk\api\app\identityAccessManagement\AuthenticationKey;
use norsk\api\app\identityAccessManagement\JwtAudience;
use norsk\api\app\identityAccessManagement\JwtConfig;
use norsk\api\app\identityAccessManagement\JwtSubject;
use norsk\api\app\logging\AppLoggerConfig;
use norsk\api\app\response\ResponseCode;
use norsk\api\app\response\Url;
use norsk\api\user\Pepper;

class AppConfig
{
    private const string API_ROOT = '/../../../';
    private const string ERROR = 'error';
    private const string LOGS = 'logs';
    private const string AUTH = 'auth';
    private const array LOGS_SECOND_LEVEL = ['path', 'displayErrorDetails', 'logErrors', 'logErrorDetails'];
    private const array AUTH_SECOND_LEVEL = ['method', 'key', 'sub', 'aud', 'addition', 'originUrl'];


    private function __construct(
        private readonly AppLoggerConfig $appLoggerConfig,
        private readonly JwtConfig $jwtConfig,
        private readonly Pepper $pepper,
        private readonly Url $url,
    ) {
    }


    public static function fromPath(Path $configPath): self
    {
        $file = File::fromPath($configPath);
        $configItems = $file->parseIniFile()->asArray();
        self::ensureConfigIsValid($configItems);

        self::displayErrorForDebug($configItems);

        return new self(
            self::readLoggerConfig($configItems[self::LOGS]),
            self::readJwtConfig($configItems[self::AUTH]),
            Pepper::by($configItems[self::AUTH]['addition']),
            Url::by($configItems[self::AUTH]['originUrl']),
        );
    }


    private static function ensureConfigIsValid(array $configItems): void
    {
        $expectedKeys = [
            self::LOGS => self::LOGS_SECOND_LEVEL,
            self::AUTH => self::AUTH_SECOND_LEVEL,
        ];

        self::ensureKeysExist($configItems, $expectedKeys);
        self::ensureKeyHasValue($configItems, $expectedKeys);
        self::ensureParamsExist($configItems, $expectedKeys);
    }


    private static function ensureKeysExist(array $configItems, array $expectedKeys): void
    {
        foreach (array_keys($expectedKeys) as $configKey) {
            self::ensureKeyIsSet($configKey, $configItems);
        }
    }


    private static function ensureKeyIsSet(string|int|bool|null $param, array $array): void
    {
        if (!array_key_exists($param, $array)) {
            throw new KeyIsNotSetException(
                'Key is not set: ' . $param,
                ResponseCode::unprocessable->value
            );
        }
    }


    private static function ensureKeyHasValue(array $configItems, array $expectedKeys): void
    {
        foreach (array_keys($expectedKeys) as $configKey) {
            if (count($configItems[$configKey]) === 0) {
                throw new KeyHasEmptyValueException(
                    'Key is empty: ' . $configKey,
                    ResponseCode::unprocessable->value
                );
            }
        }
    }


    private static function ensureParamsExist(array $configItems, array $expectedKeys): void
    {
        foreach ($expectedKeys as $configKey => $params) {
            foreach ($params as $param) {
                self::ensureKeyIsSet($param, $configItems[$configKey]);
                self::ensureKeyIsNotEmpty($configItems[$configKey][$param], $param);
            }
        }
    }


    private static function displayErrorForDebug(array $configItems): void
    {
        if (
            self::noDebugEntryInConfig($configItems)
            || self::debugEntryIsTurnedOffByFalse(
                $configItems[self::ERROR]['debug']
            )
        ) {
            error_reporting(E_ERROR | E_PARSE);
        }
    }


    private static function ensureKeyIsNotEmpty(string|int|bool|null $configItems, string $param): void
    {
        if ($configItems === '') {
            throw new KeyHasEmptyValueException(
                'Key is empty: ' . $param,
                ResponseCode::unprocessable->value
            );
        }
    }


    private static function readLoggerConfig(array $logConfigItems): AppLoggerConfig
    {
        $path = Path::fromString(__DIR__ . self::API_ROOT . $logConfigItems['path']);
        $displayErrorDetails = $logConfigItems['displayErrorDetails'];
        $logErrors = $logConfigItems['logErrors'];
        $logErrorDetails = $logConfigItems['logErrorDetails'];

        return AppLoggerConfig::by($path, (bool)$displayErrorDetails, (bool)$logErrors, (bool)$logErrorDetails);
    }


    private static function readJwtConfig(array $auth): JwtConfig
    {
        $key = AuthenticationKey::by($auth['key']);
        $method = AuthenticationAlgorithm::by($auth['method']);
        $subject = JwtSubject::by($auth['sub']);
        $audience = JwtAudience::by($auth['aud']);

        return JwtConfig::fromCredentials($key, $method, $subject, $audience);
    }


    private static function noDebugEntryInConfig(array $configItems): bool
    {
        return !isset($configItems[self::ERROR]['debug']);
    }


    private static function debugEntryIsTurnedOffByFalse(int|string|bool $debug): bool
    {
        return $debug !== true;
    }


    public function getLogPath(): Path
    {
        return $this->appLoggerConfig->getPath();
    }


    public function getJwtConfig(): JwtConfig
    {
        return $this->jwtConfig;
    }


    public function getPepper(): Pepper
    {
        return $this->pepper;
    }


    public function getUrl(): Url
    {
        return $this->url;
    }


    public function getAppLoggerConfig(): AppLoggerConfig
    {
        return $this->appLoggerConfig;
    }
}
