<?php

declare(strict_types=1);

use Inspire\Validator\Variable;
use Inspire\Validator\JsonSchema;
use Inspire\Validator\XsdSchema;

define('APP_NAME', 'test');
include dirname(__DIR__) . '/vendor/autoload.php';

$xsd = __DIR__ . "/schemas/MDFe3.00a/mdfe_v3.00.xsd";
$xsd2 = __DIR__ . "/schemas/MDFe3.00a/mdfeModalRodoviario_v3.00.xsd";
$val = XsdSchema::validateMulti([
    // [
    //     'xsd' => $xsd,
    //     'ns' => 'http://www.portalfiscal.inf.br/mdfe',
    //     'prefix' => ''
    // ],
    [
        'xml' => '<?xml version="1.0" encoding="utf-8"?><rodo><infANTT><RNTRC>str1234</RNTRC><infCIOT><CIOT>str1234</CIOT><CPF>str1234</CPF><CNPJ>str1234</CNPJ></infCIOT><valePed><disp><CNPJForn>str1234</CNPJForn><nCompra>str1234</nCompra><vValePed>str1234</vValePed><tpValePed>01</tpValePed></disp><categCombVeic>02</categCombVeic></valePed><infContratante><xNome>str1234</xNome><CPF>str1234</CPF><CNPJ>str1234</CNPJ><idEstrangeiro>str1234</idEstrangeiro><infContrato><NroContrato>str1234</NroContrato><vContratoGlobal>str1234</vContratoGlobal></infContrato></infContratante><infPag><xNome>str1234</xNome><CPF>str1234</CPF><CNPJ>str1234</CNPJ><idEstrangeiro>str1234</idEstrangeiro><Comp><tpComp>01</tpComp><vComp>str1234</vComp><xComp>str1234</xComp></Comp><vContrato>str1234</vContrato><indAltoDesemp>1</indAltoDesemp><indPag>0</indPag><vAdiant>str1234</vAdiant><indAntecipaAdiant>1</indAntecipaAdiant><infPrazo><nParcela>str1234</nParcela><dVenc>str1234</dVenc><vParcela>str1234</vParcela></infPrazo><tpAntecip>0</tpAntecip><infBanc><codBanco>str12</codBanco><codAgencia>str1234</codAgencia><CNPJIPEF>str1234</CNPJIPEF><PIX>str1234</PIX></infBanc></infPag></infANTT><veicTracao><cInt>str1234</cInt><placa>str1234</placa><RENAVAM>str123400</RENAVAM><tara>str1234</tara><capKG>str1234</capKG><capM3>str1234</capM3><prop><CPF>str1234</CPF><CNPJ>str1234</CNPJ><RNTRC>str1234</RNTRC><xNome>str1234</xNome><tpProp>0</tpProp></prop><condutor><xNome>str1234</xNome><CPF>str1234</CPF></condutor><tpRod>01</tpRod><tpCar>00</tpCar><UF>AC</UF></veicTracao><veicReboque><cInt>str1234</cInt><placa>str1234</placa><RENAVAM>str123400</RENAVAM><tara>str1234</tara><capKG>str1234</capKG><capM3>str1234</capM3><prop><CPF>str1234</CPF><CNPJ>str1234</CNPJ><RNTRC>str1234</RNTRC><xNome>str1234</xNome><tpProp>0</tpProp></prop><tpCar>00</tpCar><UF>AC</UF></veicReboque><codAgPorto>str1234</codAgPorto><lacRodo><nLacre>str1234</nLacre></lacRodo></rodo>',
        'xsd' => $xsd2,
        'ns' => 'http://www.portalfiscal.inf.br/mdfe',
        'prefix' => 'MDFe->infMDFe->infModal->'
    ]
]);
//Get errors as it was reported
print_r(XsdSchema::getErrors());
//Get parsed errors
print_r(XsdSchema::getReadableErrors());
//Get systemMessage errors
print_r(XsdSchema::getSystemErrors());

// exit;
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
if (!JsonSchema::validateJson(json_encode([
    "name" => "teste",
    "region" => null,
    "credentials" => [
        "key" => "testkey",
        "secret" => "testsecret"
    ]
]), $schema)) {
    print_r(JsonSchema::getErrors());
    print_r(JsonSchema::getReadableErrors());
    print_r(JsonSchema::getSystemErrors());
}
