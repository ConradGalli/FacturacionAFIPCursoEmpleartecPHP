<?php
/**
 * AfiPHP
 *
 * PHP Version 5.4
 *
 * @copyright 2015 Guillermo O. "Tordek" Freschi
 * @license MIT
 */

namespace Tordek\AfiPHP\Auth;

/**
 * Clase simple para realizar autenticación mediante el webservice WSAA de la
 * AFIP.
 *
 * Debido a limitaciones de `openssl_pkcs7_sign`, usa el disco para generar
 * firmas criptográficas.
 *
 * Usa {@see \DomDocument} para validar el *schema* XML de la solicitud.
 *
 * @todo Hacer que se pueda configurar la URL del WS.
 */
class WSAAAuth implements AuthService
{
    const URL_PRODUCCION = "https://wsaa.afip.gov.ar/ws/services/LoginCms?wsdl";
    const URL_HOMOLOGACION = "https://wsaahomo.afip.gov.ar/ws/services/LoginCms?wsdl";

    private $certificate_file;
    private $private_key;

    private $tra_schema_location;

    /**
     * @var \SoapClient|null Instancia SoapClient para llamadas SOAP. Lazy-loaded.
     *
     * No acceder directamente; usar getClient()
     */
    private $client = null;

    /**
     * Constructor.
     *
     * @param string $certificate_file Dirección del archivo de certificado.
     * @param array $private_key Array clave privada, en formato [dirección, passphrase].
     * @param \SoapClient Instancia de cliente WS, para testing.
     */
    public function __construct($certificate_file, $private_key, $client = null)
    {
        $this->certificate_file = $certificate_file;
        $this->private_key = $private_key;
        $this->client = $client;

        $this->tra_schema_location = __DIR__ . "/schemata/LoginTicketRequest.xsd";
    }

    /**
     * Solicita credenciales mediante el servicio WSAA de la AFIP.
     */
    public function getCredenciales($servicio)
    {
        $login_ticket_request = $this->buildLoginTicketRequest($servicio);
        $signed_login_request = $this->signLoginTicketRequest($login_ticket_request);
        $raw_credenciales = $this->requestAuth($signed_login_request);
        return $this->parseCredenciales($raw_credenciales);
    }

    private function getClient()
    {
        if ($this->client === null) {
            //$this->client = new \SoapClient(WSAAAuth::URL_PRODUCCION);
            $this->client = new \SoapClient(WSAAAuth::URL_HOMOLOGACION);
        }

        return $this->client;
    }

    private function buildLoginTicketRequest($servicio)
    {
        $parameters = array(
            'uniqueId' => rand(),
            'generationTime' => new \DateTimeImmutable("now - 20 hours"),
            'expirationTime' => new \DateTimeImmutable("now + 20 hours"),
        );

        $xml = new \DOMDocument("1.0", "utf-8");

        $root = $xml->appendChild($xml->createElement("loginTicketRequest"));
        $versionAttribute = $xml->createAttribute("version");
        $versionAttribute->value = "1.0";
        $root->appendChild($versionAttribute);

        $header = $root->appendChild($xml->createElement("header"));

        $header->appendChild(
            $xml->createElement("uniqueId", $parameters['uniqueId'])
        );
        $header->appendChild(
            $xml->createElement(
                "generationTime",
                $parameters['generationTime']->format(\DateTime::W3C)
            )
        );
        $header->appendChild(
            $xml->createElement(
                "expirationTime",
                $parameters['expirationTime']->format(\DateTime::W3C)
            )
        );

        $root->appendChild($xml->createElement("service", $servicio));

        assert($xml->schemaValidate($this->tra_schema_location));

        return $xml->saveXml();
    }

    /**
     * Función para firmar la request con los certificados. Usa el disco como
     * almacén temporal.
     */
    private function signLoginTicketRequest($login_ticket_request)
    {
        $tra_file = tempnam("temp/", "LoginRequest.xml");
        $tra_cms_file = tempnam("temp/", "LoginRequest.xml.cms");

        file_put_contents($tra_file, $login_ticket_request);

        $rc = openssl_pkcs7_sign(
            $tra_file,
            $tra_cms_file,
            $this->certificate_file,
            $this->private_key,
            [],
            0
        );

        if ($rc === false) {
            return false;
        }

        $tra_cms = file_get_contents($tra_cms_file);

        // Destruir archivos temporales
        unlink($tra_file);
        unlink($tra_cms_file);

        // Descartar encabezados MIME
        $tra_cms = preg_replace("/^(.*\n){5}/", "", $tra_cms);

        return $tra_cms;
    }

    private function requestAuth($tra_cms)
    {
        return $this->getClient()->LoginCMS(array('in0' => $tra_cms));
    }

    private function parseCredenciales($raw_credenciales)
    {
        $response_xml = new \DOMDocument();
        $rc = $response_xml->loadXml($raw_credenciales->loginCmsReturn);

        if ($rc === false) {
            return false;
        }

        return new Credenciales(
            $response_xml->getElementsByTagName("token")[0]->nodeValue,
            $response_xml->getElementsByTagName("sign")[0]->nodeValue,
            new \DateTimeImmutable(
                $response_xml->getElementsByTagName("generationTime")[0]->nodeValue
            ),
            new \DateTimeImmutable(
                $response_xml->getElementsByTagName("expirationTime")[0]->nodeValue
            )
        );
    }
}
