<?php
declare(strict_types = 1);
use Inspire\Validator\Variable;
use Inspire\Validator\JsonSchema;
use Inspire\Validator\XsdSchema;

define('APP_NAME', 'test');
include dirname(__DIR__) . '/vendor/autoload.php';

$xsd = "/home/aalves/eclipse-workspace/validator/tests/schemas/MDFe3.00a/mdfe_v3.00.xsd";
$xsd2 = "/home/aalves/eclipse-workspace/validator/tests/schemas/MDFe3.00a/mdfeModalRodoviario_v3.00.xsd";
$val = XsdSchema::validateMulti([
    [
        'xml' => '<?xml version="1.0" encoding="utf-8"?><MDFe xmlns="http://www.portalfiscal.inf.br/mdfe"><infMDFe Id="MDFeXXXXXXXXXXXXXXXXXXXXXXX" versao="3.00"><ide><cUF>35</cUF><tpAmb>2</tpAmb><tpEmit>1</tpEmit><mod>58</mod><serie>1</serie><nMDF>2</nMDF><cMDF>00000002</cMDF><cDV>6</cDV><modal>1</modal><dhEmi>2017-08-15T08:08:51-03:00</dhEmi><tpEmis>1</tpEmis><procEmi>0</procEmi><verProc>1.0.0</verProc><UFIni>SP</UFIni><UFFim>MT</UFFim><infMunCarrega><cMunCarrega>3547809</cMunCarrega><xMunCarrega>Santo André</xMunCarrega></infMunCarrega><infPercurso><UFPer>MS</UFPer></infPercurso></ide><emit><CNPJ>XXXXXXXXXXXXXXXX</CNPJ><IE>XXXXXXXXXXXXXXXXXXXXXX</IE><xNome>XXXXXXXXXXXXXXXXXXXXXXXX</xNome><enderEmit><xLgr>XXXXXXXXXXXXXXXXXXXX</xLgr><nro>XXX</nro><xBairro>XXXXXXXXXXXXXXX</xBairro><cMun>3550308</cMun><xMun>Sao Paulo</xMun><CEP>XXXXXXXXXXX</CEP><UF>SP</UF><email>XXXXXXXX</email></enderEmit></emit><infModal versaoModal="3.00"><rodo><infANTT><RNTRC>00000000</RNTRC><infContratante><CNPJ>XXXXXXXXXXX</CNPJ></infContratante></infANTT><veicTracao><placa>XXXXX</placa><tara>8600</tara><condutor><xNome>XXXXXX</xNome><CPF>XXXXX</CPF></condutor><tpRod>01</tpRod><tpCar>02</tpCar><UF>SP</UF></veicTracao></rodo></infModal><infDoc><infMunDescarga><cMunDescarga>5107602</cMunDescarga><xMunDescarga>Rondonópolis</xMunDescarga><infCTe><chCTe>XXXXXXXXXXXXXXXXXXXXXXXXXX</chCTe></infCTe></infMunDescarga></infDoc><seg><infResp><respSeg>1</respSeg><CNPJ>00000000000000</CNPJ></infResp><infSeg><xSeg>XXXXXXXXXXXXXXXXXXXXX</xSeg><CNPJ>XXXXXXXXXXXXXXXXXXX</CNPJ></infSeg><nApol>XXXXXXXXXXXXXXXXXXX</nApol><nAver>0000</nAver></seg><tot><qCTe>1</qCTe><vCarga>199516.96</vCarga><cUnid>01</cUnid><qCarga>11115</qCarga></tot></infMDFe><Signature xmlns="http://www.w3.org/2000/09/xmldsig#"><SignedInfo><CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/><SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/><Reference URI="#MDFe35170812195067000108580010000000021000000026"><Transforms><Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/><Transform Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/></Transforms><DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/><DigestValue>XXXXXXXXXXXXXXXXXXXXXXXXXXXXX</DigestValue></Reference></SignedInfo><SignatureValue>XXXXXXXXXXXXXXXXXXXXXXXXXXX</SignatureValue><KeyInfo><X509Data><X509Certificate>XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX</X509Certificate></X509Data></KeyInfo></Signature></MDFe>',
        'xsd' => $xsd,
        'ns' => 'http://www.portalfiscal.inf.br/mdfe',
        'prefix' => ''
    ],
    [
        'xml' => '<?xml version="1.0" encoding="utf-8"?><rodo><infANTT><RNTRC>00000000</RNTRC><infContratante><CNPJ>XXXXXXXXXXX</CNPJ></infContratante></infANTT><veicTracao><placa>XXXXX</placa><tara>8600</tara><condutor><xNome>XXXXXX</xNome><CPF>XXXXX</CPF></condutor><tpRod>01</tpRod><tpCar>02</tpCar><UF>SP</UF></veicTracao></rodo>',
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
if (! JsonSchema::validateJson(json_encode([
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



