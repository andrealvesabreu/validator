<?php
declare(strict_types = 1);
use Inspire\Validator\Variable;
use Inspire\Validator\JsonSchema;

define('APP_NAME', 'test');
include dirname(__DIR__) . '/vendor/autoload.php';
var_dump(Variable::base64()->validate('cmVzcGVjdCE='));
var_dump(Variable::base64()->validate('cmVzcGVjdCE'));
var_dump(Variable::email()->validate('test@email.com'));
var_dump(Variable::startsWith('lorem')->validate('lorem ipsum'));

$schema = <<<DATA
{
    "type": "object",
    "required": [
        "region",
        "credentials",
        "name"
    ],
    "properties": {
        "name": {
            "type": "string",
            "minLength": 3,
            "maxLength": 60,
            "pattern": "^[^\\\s]*$"
        },
        "region": {
            "type": "string",
            "minLength": 3,
            "maxLength": 60
        },
        "version": {
            "type": [
                "string",
                "null"
            ]
        },
        "credentials": {
            "type": "object",
            "required": [
                "key",
                "secret"
            ],
            "properties": {
                "key": {
                    "type": "string",
                    "minLength": 16,
                    "maxLength": 128,
                    "pattern": "^[^\\\s]*$"
                },
                "secret": {
                    "type": "string",
                    "minLength": 32,
                    "maxLength": 128,
                    "pattern": "^[^\\\s]*$"
                }
            }
        }
    }
}
DATA;
var_dump($schema);
if (! JsonSchema::validateJson(json_encode([
    "name" => "teste",
    "region" => null,
    "credentials" => [
        "key" => "testkey",
        "secret" => "testsecret"
    ]
]), $schema)) {
    var_dump(JsonSchema::getReadableErrors());
}



