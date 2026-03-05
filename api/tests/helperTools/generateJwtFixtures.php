<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;

// Key muss identisch zu AuthenticationKey::by(...)->asBase64String() sein
$rawKey = 'ABCDEFGHIJKLMnopqrstuvwxyz0123456789abcdefghijklmnopqrstuvwxyz01';
$key = base64_encode($rawKey);
$algorithm = 'HS512';

$outputDir = __DIR__ . '/../unit/user/infrastructure/resources/jwt/';

echo "Key (base64): " . $key . PHP_EOL;
echo "Key length bits: " . (strlen($key) * 8) . PHP_EOL;

// ---- validToken.jwt (HS256, fester Timestamp – Europe/Berlin 2024-09-27 19:41:52 = UTC 17:41:52) ----
$hs256Key = base64_encode($rawKey);
$iat = (new DateTimeImmutable('2024-09-27 19:41:52', new DateTimeZone('Europe/Berlin')))->getTimestamp();
$validToken = JWT::encode([
    'sub' => 'norsk app',
    'aud' => 'Norsk Client',
    'iat' => $iat,
    'exp' => $iat + 7200,
    'scope' => 'is:user',
    'nickname' => 'testuser',
], $hs256Key, 'HS256');
file_put_contents($outputDir . 'validToken.jwt', $validToken);
echo "validToken.jwt written (iat=$iat)\n";

// ---- validUserTokenWithTime.jwt ----
$validUserToken = JWT::encode([
    'sub' => 'norsk app',
    'aud' => 'Norsk Client',
    'iat' => 1727466112,
    'exp' => 9999999999,
    'scope' => 'is:user',
    'nickname' => 'klaus',
], $key, $algorithm);
file_put_contents($outputDir . 'validUserTokenWithTime.jwt', $validUserToken);
echo "validUserTokenWithTime.jwt written\n";

// ---- validManagerTokenWithTime.jwt ----
$validManagerToken = JWT::encode([
    'sub' => 'norsk app',
    'aud' => 'Norsk Client',
    'iat' => 1727466112,
    'exp' => 9999999999,
    'scope' => 'is:manager',
    'nickname' => 'heinz',
], $key, $algorithm);
file_put_contents($outputDir . 'validManagerTokenWithTime.jwt', $validManagerToken);
echo "validManagerTokenWithTime.jwt written\n";

// ---- validUnknownUserTokenWithTime.jwt ----
$validUnknownUserToken = JWT::encode([
    'sub' => 'norsk app',
    'aud' => 'Norsk Client',
    'iat' => 1727466112,
    'exp' => 9999999999,
    'scope' => 'is:unknown',
    'nickname' => 'unknown',
], $key, $algorithm);
file_put_contents($outputDir . 'validUnknownUserTokenWithTime.jwt', $validUnknownUserToken);
echo "validUnknownUserTokenWithTime.jwt written\n";

// ---- validClientTokenWithTime.jwt ----
$validClientToken = JWT::encode([
    'sub' => 'norsk app',
    'aud' => 'Norsk Client',
    'iat' => 1727466112,
    'exp' => 9999999999,
    'scope' => 'is:user',
    'nickname' => 'norsk client-old',
], $key, $algorithm);
file_put_contents($outputDir . 'validClientTokenWithTime.jwt', $validClientToken);
echo "validClientTokenWithTime.jwt written\n";

// ---- invalidTokenNotFromApp.jwt (sub ist falsch) ----
$invalidNotFromApp = JWT::encode([
    'sub' => 'not norsk app',
    'aud' => 'Norsk Client',
    'iat' => 1727466112,
    'exp' => 9999999999,
    'scope' => 'is:user',
    'nickname' => 'hacker',
], $key, $algorithm);
file_put_contents($outputDir . 'invalidTokenNotFromApp.jwt', $invalidNotFromApp);
echo "invalidTokenNotFromApp.jwt written\n";

// ---- invalidTokenWithManipulatedClient.jwt (aud ist falsch) ----
$invalidManipulatedClient = JWT::encode([
    'sub' => 'norsk app',
    'aud' => 'Not Norsk Client',
    'iat' => 1727466112,
    'exp' => 9999999999,
    'scope' => 'is:user',
    'nickname' => 'hacker',
], $key, $algorithm);
file_put_contents($outputDir . 'invalidTokenWithManipulatedClient.jwt', $invalidManipulatedClient);
echo "invalidTokenWithManipulatedClient.jwt written\n";

// ---- invalidExpired.jwt ----
$invalidExpired = JWT::encode([
    'sub' => 'norsk app',
    'aud' => 'Norsk Client',
    'iat' => 1000000000,
    'exp' => 1000003600,
    'scope' => 'is:user',
    'nickname' => 'expired',
], $key, $algorithm);
file_put_contents($outputDir . 'invalidExpired.jwt', $invalidExpired);
echo "invalidExpired.jwt written\n";

// ---- invalidNotValidYet.jwt (nbf in der Zukunft) ----
$invalidNotValidYet = JWT::encode([
    'sub' => 'norsk app',
    'aud' => 'Norsk Client',
    'iat' => 9999999990,
    'nbf' => 9999999990,
    'exp' => 9999999999,
    'scope' => 'is:user',
    'nickname' => 'future',
], $key, $algorithm);
file_put_contents($outputDir . 'invalidNotValidYet.jwt', $invalidNotValidYet);
echo "invalidNotValidYet.jwt written\n";

// ---- invalidSignature.jwt (falscher Key) ----
$wrongKey = base64_encode('WRONGKEYABCDEFGHIJKLMnopqrstuvwxyz0123456789abcdefghijklmnopqrs');
$invalidSignature = JWT::encode([
    'sub' => 'norsk app',
    'aud' => 'Norsk Client',
    'iat' => 1727466112,
    'exp' => 9999999999,
    'scope' => 'is:user',
    'nickname' => 'wrongsig',
], $wrongKey, $algorithm);
file_put_contents($outputDir . 'invalidSignature.jwt', $invalidSignature);
echo "invalidSignature.jwt written\n";

// ---- invalid.jwt (strukturell valides JWT-Format, aber komplett falscher Inhalt/Signatur) ----
// Muss JsonWebToken::fromString() passieren, aber JWT::decode() mit einer RuntimeException fehlschlagen
$invalidHeader = rtrim(base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS512'])), '=');
$invalidPayload = rtrim(base64_encode(json_encode(['sub' => 'evil'])), '=');
$invalidSig = rtrim(base64_encode('invalidsignaturethatisnotvalid1234567890ab'), '=');
file_put_contents($outputDir . 'invalid.jwt', $invalidHeader . '.' . $invalidPayload . '.' . $invalidSig);
echo "invalid.jwt written\n";

// ---- invalidGeneral.jwt (unsupporteter Algorithmus -> default-Branch in JwtManagement) ----
// Dieses Token hat einen Algorithmus-Header der von der Firebase-Library nicht unterstützt wird.
// JWT::decode wirft UnexpectedValueException('Algorithm not supported') -> default-Branch
$unsupportedAlgHeader = rtrim(strtr(base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'RS999'])), '+/', '-_'), '=');
$generalPayload = rtrim(strtr(base64_encode(json_encode(['sub' => 'test'])), '+/', '-_'), '=');
$generalSig = rtrim(strtr(base64_encode(str_repeat('x', 32)), '+/', '-_'), '=');
file_put_contents($outputDir . 'invalidGeneral.jwt', $unsupportedAlgHeader . '.' . $generalPayload . '.' . $generalSig);
echo "invalidGeneral.jwt written\n";

echo "\nDone! All fixtures written to: " . $outputDir . PHP_EOL;

