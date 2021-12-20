<?php
declare(strict_types = 1);
namespace Inspire\Validator;

use Inspire\Core\System\Message;
use Inspire\Core\System\SystemMessage;

/**
 * Description of JsonSchema
 *
 * @author aalves
 */
class XsdSchema
{

    /**
     * Language to use on messages
     *
     * @var string
     */
    private static string $lang = \Inspire\Core\System\Language::EN_US;

    /**
     * Define a prefix to set readable errors
     *
     * @var string
     */
    private static string $prefixErrors = '';

    /**
     *
     * @var array
     */
    private static array $readable_messages = [
        \Inspire\Core\System\Language::PT_BR => [
            'minItems' => "O grupo '?' deve ter no mínimo ? elementos, mas apenas ? foram informados.",
            'maxItems' => "O grupo '?' deve ter no máximo ? elementos, mas foram informados ?.",
            'length' => "O campo '?' deve ter exatamente ? caracteres, mas foram informados ?.",
            'minLength' => "O campo '?' deve ter no mínimo ? caracteres, mas apenas ? foram informados.",
            'maxLength' => "O campo '?' deve ter no máximo ? caracteres, mas foram informados ?.",
            'format' => "O campo '?' deve ser preenchido no formato '?'.",
            'minExclusive' => "O valor do campo '?' deve ser maior do que ?. Você informou ?.",
            'minInclusive' => "O valor do campo '?' deve ser maior ou igual a ?. Você informou ?.",
            'maxExclusive' => "O valor do campo '?' deve ser menor do que ?. Você informou ?.",
            'maxInclusive' => "O valor do campo '?' deve ser menor ou igual a ?. Você informou ?.",
            'pattern' => "O valor do campo '?' deve obedecer a E.R. ?, mas foi preenchido com ?.",
            'patternAttr' => "O valor do atributo '?' do campo '?' deve obedecer a E.R. ?, mas foi preenchido com ?.",
            'required' => "O campo '?' é obrigatório.",
            'missing' => "Falta o elemento '?' no campo '?'.",
            'requiredAttr' => "O atributo '?' do campo '?' é obrigatório.",
            'allowed' => "O campo '?' é não é permitido.",
            'allowedAttr' => "O atributo '?' do campo '?' não é permitido.",
            'type' => "O campo '?' deve ser um de [?], mas foi informado '?'.",
            'unexpected' => "O campo '?' não é esperado. Esperado é um de: ?.",
            'enum' => "O campo '?' deve ser preenchido com um dos seguintes valores: ?, mas foi preenchido com ?.",
            'oneOf' => "O campo '?' não corresponde a nenhum dos esquemas disponíveis.",
            'noMatching' => "Não há regras de validação o elemento '?'.",
            'fixed' => "O campo '?' deve ser preenchido o valor fixo '?', mas foi informado '?'"
        ],
        \Inspire\Core\System\Language::EN_US => [
            'minItems' => "The group '?' must have at least ? elements, but only ? was informed.",
            'maxItems' => "The group '?' must have at most ? elements, but ? was informed.",
            'length' => "The field '?' must have exactly ? characters, but ? was informed.",
            'minLength' => "The field '?' must have at least ? characters, but only ? was informed.",
            'maxLength' => "The field '?' must have at most ? characters, but ? was informed.",
            'format' => "The field '?' must be filled in '?' format.",
            'minExclusive' => "The value of the field '?' must be greater than ?. ? was informed.",
            'minInclusive' => "The value of the field '?' must be greater or equal to ?. ? was informed.",
            'maxExclusive' => "The value of the field '?' must be less than ?. ? was informed.",
            'maxInclusive' => "The value of the field '?' must be less or equal to ?. ? was informed.",
            'pattern' => "The value of the field '?' must match E.R. ?, but was filled with ?.",
            'patternAttr' => "The value of the attibute '?' of the field '?' must match E.R. ?, but was filled with ?.",
            'required' => "The field '?' is required.",
            'missing' => "The element '?' is missing in field '?'.",
            'requiredAttr' => "The attribute '?' of the field '?' is required.",
            'allowed' => "The field '?' is not allowed.",
            'allowedAttr' => "The attribute '?' of the field '?' is not allowed.",
            'type' => "The field '?' must be one of [?], but it was informed '?'.",
            'unexpected' => "The field '?' is unexpected. Expected is one of: ?.",
            'enum' => "The field '?' must be filled with one of the following values: ?, but was filled with ?.",
            'oneOf' => "The field '?' does not match any of the available schemes.",
            'noMatching' => "There is no validation rule for '?' field.",
            'fixed' => "The field '?' must be filled with the fixed value of '?', but '?' was filled."
        ]
    ];

    /**
     * List of errors
     *
     * @var array
     */
    private static array $errors = [];

    /**
     * List of errors in human readable format
     *
     * @var array
     */
    private static array $readableErrors = [];

    /**
     * List of errors with SystemMessage
     *
     * @var array
     */
    private static array $systemErrors = [];

    /**
     *
     * @var \DOMXPath
     */
    private static ?\DOMXPath $xpath = null;

    /**
     * Parse errors of \LibXMLError
     *
     * @param \LibXMLError $error
     */
    private static function libxmlParseError(\LibXMLError $error, string $ns = null)
    {
        $aMatches = [];
        /**
         * Getting element and namespace
         */
        self::$errors[] = "{$error->code} - {$error->message}";
        preg_match_all('/\'([^\']*)\'/', trim($error->message), $aMatches);
        $element = $aMatches[1][0];
        $namespace = ltrim(strtok($aMatches[1][0], "}"), '{');
        if ($ns !== null) {
            $namespace = trim($ns);
        }
        $element = strtok("}");

        $typeError = null;
        switch ($error->level) {
            /**
             * Level warning
             */
            case LIBXML_ERR_WARNING:
                $typeError = Message::MSG_WARNING;
                break;
            /**
             * Level error
             */
            case LIBXML_ERR_ERROR:
                $typeError = Message::MSG_ERROR;
                break;
            /**
             * Level critical, unrecoverable error
             */
            case LIBXML_ERR_FATAL:
                $typeError = Message::MSG_CRITICAL;
                break;
        }

        /**
         * Register namespace
         */
        self::$xpath->registerNamespace('xsdsc', $namespace);
        $set = '';
        $attr = false;
        // Error message composition
        $errMessage = null;
        $fieldMessage = null;
        $ruleMessage = null;
        switch ($error->code) {
            /**
             * No matching global
             */
            case 1845:
                $nodes = self::$xpath->query("//xsdsc:{$element}");
                $fieldMessage = self::getPath($nodes[0]->getNodePath());
                $ruleMessage = 'length';
                $errMessage = preg_replace([
                    "/\?/"
                ], //
                [
                    $fieldMessage
                ], //
                self::$readable_messages[self::$lang]['noMatching'], //
                1);
                break;
            /**
             * exactly length
             */
            case 1830:
                $nodes = self::$xpath->query("//xsdsc:{$element}[string-length() = {$aMatches[1][2]}]");
                $fieldMessage = self::getPath($nodes[0]->getNodePath());
                $ruleMessage = 'length';
                $errMessage = preg_replace([
                    "/\?/",
                    "/\?/",
                    "/\?/"
                ], //
                [
                    $fieldMessage,
                    implode(', ', array_slice($aMatches[1], 3)),
                    $aMatches[1][2]
                ], //
                self::$readable_messages[self::$lang]['length'], //
                1);
                break;
            /**
             * minLength
             * maxLength
             */
            case 1831:
            case 1832:
                if (isset($aMatches[1][4])) {
                    $nodes = self::$xpath->query("//xsdsc:{$element}");
                    $set = $aMatches[1][3];
                    $attr = $aMatches[1][1];
                } else {
                    $nodes = self::$xpath->query("//xsdsc:{$element}[string-length() = {$aMatches[1][2]}]");
                    $set = $aMatches[1][2];
                }
                $fieldMessage = self::getPath($nodes[0]->getNodePath());
                $ruleMessage = $aMatches[1][1];
                $errMessage = preg_replace([
                    "/\?/",
                    "/\?/",
                    "/\?/"
                ], //
                [
                    $fieldMessage,
                    $aMatches[1][3],
                    $aMatches[1][2]
                ], //
                self::$readable_messages[self::$lang][$aMatches[1][1]], //
                1);
                break;
            /**
             * minInclusive
             * maxInclusive
             * minExclusive
             * maxExclusive
             */
            case 1833:
            case 1834:
            case 1835:
            case 1836:
                $nodes = self::$xpath->query("//xsdsc:{$element}[.=\"{$aMatches[1][2]}\"]");
                $fieldMessage = self::getPath($nodes[0]->getNodePath());
                $ruleMessage = $aMatches[1][1];
                $errMessage = preg_replace([
                    "/\?/",
                    "/\?/",
                    "/\?/"
                ], //
                [
                    $fieldMessage,
                    implode(', ', array_slice($aMatches[1], 3)),
                    $aMatches[1][2]
                ], //
                self::$readable_messages[self::$lang][$aMatches[1][1]], //
                1);
                break;
            /**
             * Patterns
             */
            case 1839:
                if (isset($aMatches[1][4])) {
                    $nodes = self::$xpath->query("//xsdsc:{$element}");
                    $set = $aMatches[1][3];
                    $attr = $aMatches[1][1];
                } else {
                    $nodes = self::$xpath->query("//xsdsc:{$element}[.=\"{$aMatches[1][2]}\"]");
                    $set = $aMatches[1][2];
                }
                $fieldMessage = self::getPath($nodes[0]->getNodePath());
                $ruleMessage = 'pattern';
                if ($attr) {
                    $errMessage = preg_replace([
                        "/\?/",
                        "/\?/",
                        "/\?/",
                        "/\?/"
                    ], //
                    [
                        $attr,
                        $fieldMessage,
                        end($aMatches[1]),
                        $set
                    ], //
                    self::$readable_messages[self::$lang]['patternAttr'], //
                    1);
                } else {
                    $errMessage = preg_replace([
                        "/\?/",
                        "/\?/",
                        "/\?/"
                    ], //
                    [
                        $fieldMessage,
                        end($aMatches[1]),
                        $set
                    ], //
                    self::$readable_messages[self::$lang]['pattern'], //
                    1);
                }
                break;
            /**
             * Enumerations
             */
            case 1840:
                $nodes = self::$xpath->query("//xsdsc:{$element}[.=\"{$aMatches[1][2]}\"]");
                $fieldMessage = self::getPath($nodes[0]->getNodePath());
                $ruleMessage = 'enum';
                $errMessage = preg_replace([
                    "/\?/",
                    "/\?/",
                    "/\?/"
                ], //
                [
                    $fieldMessage,
                    implode(', ', array_slice($aMatches[1], 3)),
                    $aMatches[1][2]
                ], //
                self::$readable_messages[self::$lang]['enum'], //
                1);
                break;
            /**
             * Fixed value
             */
            case 1858:
                print_r($aMatches);
                $nodes = self::$xpath->query("//xsdsc:{$element}");
                $fieldMessage = self::getPath($nodes[0]->getNodePath());
                $ruleMessage = 'fixed';
                $errMessage = preg_replace([
                    "/\?/",
                    "/\?/",
                    "/\?/"
                ], //
                [
                    $fieldMessage,
                    $aMatches[1][2],
                    $aMatches[1][1]
                ], //
                self::$readable_messages[self::$lang]['fixed'], //
                1);
                break;
            /**
             * not allowed
             */
            case 1866:
                $nodes = self::$xpath->query("//xsdsc:{$element}");
                $fieldMessage = self::getPath($nodes[0]->getNodePath());
                $ruleMessage = 'allowed';
                if (isset($aMatches[1])) {
                    $errMessage = preg_replace([
                        "/\?/",
                        "/\?/"
                    ], //
                    [
                        $aMatches[1][1],
                        $fieldMessage
                    ], //
                    self::$readable_messages[self::$lang]['allowedAttr'], //
                    1);
                } else {
                    $errMessage = preg_replace([
                        "/\?/"
                    ], //
                    [
                        $fieldMessage
                    ], //
                    self::$readable_messages[self::$lang]['allowed'], //
                    1);
                }
                break;
            /**
             * not required
             */
            case 1868:
                $nodes = self::$xpath->query("//xsdsc:{$element}");
                $fieldMessage = self::getPath($nodes[0]->getNodePath());
                $ruleMessage = 'required';
                if (isset($aMatches[1])) {
                    $errMessage = preg_replace([
                        "/\?/",
                        "/\?/"
                    ], //
                    [
                        $aMatches[1][1],
                        $fieldMessage
                    ], //
                    self::$readable_messages[self::$lang]['requiredAttr'], //
                    1);
                } else {
                    $errMessage = preg_replace([
                        "/\?/"
                    ], //
                    [
                        $fieldMessage
                    ], //
                    self::$readable_messages[self::$lang]['required'], //
                    1);
                }
                break;
            /**
             * Unexpected element
             */
            case 1871:
                $nodes = self::$xpath->query("//xsdsc:{$element}");
                $matchExpect = [];
                preg_match_all('!\(([^\)]+)\)!', $error->message, $matchExpect);
                if (is_array($matchExpect[0])) {
                    $matchExpect = end($matchExpect);
                }
                $fieldMessage = self::getPath($nodes[0]->getNodePath());
                if (strpos($error->message, 'Missing child')) {
                    $ruleMessage = 'missing';
                    $errMessage = preg_replace([
                        "/\?/",
                        "/\?/"
                    ], //
                    [
                        trim(str_replace("{{$namespace}}", '', end($matchExpect))),
                        $fieldMessage
                    ], //
                    self::$readable_messages[self::$lang]['missing'], //
                    1);
                } else {
                    $ruleMessage = 'unexpected';
                    $errMessage = preg_replace([
                        "/\?/",
                        "/\?/"
                    ], //
                    [
                        $fieldMessage,
                        str_replace("{{$namespace}}", '', end($matchExpect))
                    ], //
                    self::$readable_messages[self::$lang]['unexpected'], //
                    1);
                }
                break;
        }
        if ($errMessage !== null) {
            self::$readableErrors[] = $errMessage;
            $sysErr = new SystemMessage($errMessage, //
            (string) $error->code, //
            $typeError, //
            false);
            $sysErr->setExtra([
                'field' => $fieldMessage,
                'rule' => $ruleMessage
            ]);
            self::$systemErrors[] = $sysErr;
        }
    }

    /**
     * Get named path based on element XPath
     *
     * @param string $string
     * @param array $path
     * @return string
     */
    private static function getPath(string $string, array $path = []): string
    {
        $nodes = self::$xpath->query($string);
        if ($nodes && $nodes->length > 0) {
            $path[] = $nodes->item(0)->nodeName;
            $pathIn = explode('/', $string);
            array_pop($pathIn);
            if (! empty($pathIn)) {
                return self::getPath(implode('/', $pathIn), $path);
            }
        }
        return self::$prefixErrors . implode('->', array_reverse($path));
    }

    /**
     * Validate XML with XSD file
     *
     * @param string $xml
     * @param string $xsdPath
     * @param string $rootNS
     * @throws \Exception
     * @return bool
     */
    public static function validate(string $xml, string $xsdPath, ?string $rootNS = null, ?string $prefixError = null): bool
    {
        self::$prefixErrors = $prefixError ?? '';
        /**
         * Check if XSD file exists
         */
        $xsdPath = strtolower(substr($xsdPath, - 3)) == 'xsd' ? $xsdPath : "{$xsdPath}.xsd";
        if (! file_exists($xsdPath) || ! is_readable($xsdPath)) {
            throw new \Exception("XSD file is not readable or not exists.");
        }
        /**
         * Check if $xml is a valid XML string
         */
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        simplexml_load_string($xml);
        if (! empty(libxml_get_errors())) {
            throw new \Exception("You must provide a valid XML.");
        }
        /**
         * Change root namespace, if $rootNS is not null
         */
        if ($rootNS !== null) {
            $xml = trim(preg_replace("/<\?xml.+?\?>/", "", $xml));
            $ref = strtok($xml, '>');
            $new = preg_replace('~[\s]+xmlns=[\'"].+?[\'"]~i', '', $ref) . " xmlns=\"{$rootNS}\">";
            $xml = $new . substr($xml, strlen($ref) + 1);
        }
        /**
         * Load document to validate
         *
         * @var \DOMDocument $domXml
         */
        $domXml = new \DOMDocument();
        $domXml->loadXML($xml);
        self::$xpath = new \DOMXpath($domXml);
        if (! $domXml->schemaValidate($xsdPath)) {
            $errors = libxml_get_errors();
            foreach ($errors as $error) {
                self::libxmlParseError($error, $rootNS);
            }
            libxml_clear_errors();
        }
        return empty(self::$errors);
    }

    /**
     * Validate multiple XMLs
     *
     * @param array $data
     * @return bool
     */
    public static function validateMulti(array $data): bool
    {
        foreach ($data as $x) {
            if (isset($x['xml']) && isset($x['xsd'])) {
                self::validate($x['xml'], $x['xsd'], $x['ns'] ?? null, $x['prefix'] ?? null);
            }
        }
        return empty(self::$errors);
    }

    /**
     * Returns errors as validator fills
     *
     * @return array|null
     */
    public static function getErrors(): ?array
    {
        return is_array(self::$errors) && ! empty(self::$errors) ? self::$errors : null;
    }

    /**
     * Return readable errors
     *
     * @return array|NULL
     */
    public static function getReadableErrors(): ?array
    {
        return is_array(self::$readableErrors) && ! empty(self::$readableErrors) ? self::$readableErrors : null;
    }

    /**
     * Return system errors
     *
     * @return array|NULL
     */
    public static function getSystemErrors(): ?array
    {
        return is_array(self::$systemErrors) && ! empty(self::$systemErrors) ? self::$systemErrors : null;
    }

    /**
     * Check if errors data are filled
     *
     * @return bool
     */
    public static function hasErrors(): bool
    {
        return is_array(self::$errors) && ! empty(self::$errors);
    }
}