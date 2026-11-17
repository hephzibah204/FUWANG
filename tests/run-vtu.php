<?php
// Windows sandbox fallback: direct autoload and the same isolated test settings.
require dirname(__DIR__).'/vendor/autoload.php';
foreach (['APP_ENV'=>'testing','APP_KEY'=>'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=','BCRYPT_ROUNDS'=>'4','CACHE_STORE'=>'array','DB_CONNECTION'=>'sqlite','DB_DATABASE'=>':memory:','MAIL_MAILER'=>'array','QUEUE_CONNECTION'=>'sync','SESSION_DRIVER'=>'array','DATASUB247_API_KEY'=>'','DATASUB247_USERNAME'=>'','DATASUB247_PASSWORD'=>''] as $k=>$v) {putenv($k.'='.$v); $_ENV[$k]=$v; $_SERVER[$k]=$v;}
$paths=array_slice($argv,1) ?: ['tests/Unit/VtuProviderStatusTest.php','tests/Unit/VtuHubServiceTest.php','tests/Feature/VtuPurchaseSafetyTest.php','tests/Feature/VtuElectricityValidationTest.php'];
exit((new PHPUnit\TextUI\Application)->run(array_merge(['phpunit','--no-configuration','--do-not-cache-result'],$paths)));
