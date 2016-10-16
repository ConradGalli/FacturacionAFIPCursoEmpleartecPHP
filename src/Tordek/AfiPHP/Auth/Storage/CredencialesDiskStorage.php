<?php
/**
 * AfiPHP
 *
 * PHP Version 5.4
 *
 * @copyright 2015 Guillermo O. "Tordek" Freschi
 * @license MIT
 */

namespace Tordek\AfiPHP\Auth\Storage;

/**
 * Almacén simple de credenciales a disco.
 *
 * @todo: Poner cosas en un directorio configurable.
 */
class CredencialesDiskStorage implements CredencialesStorage
{
    private $serializer;
    private $base_dir;

    /**
     * Constructor
     *
     * @param Serializer [$serializer] Un serializador y parser de credenciales.
     */
    public function __construct($base_dir, $serializer = null)
    {
        if ($serializer === null) {
            $serializer = new CredencialesXmlSerializer();
        }

        if (!is_dir($base_dir)) {
            throw new \Exception("`$base_dir` no es un directorio.");
        }

        $this->serializer = $serializer;
        $this->base_dir = $base_dir;
    }

    public function loadCredenciales($name)
    {
        $filename = $this->getFilename($name);

        if (!file_exists($filename)) {
            return null;
        }

        $raw_credenciales = file_get_contents($filename);

        return $this->serializer->parse($raw_credenciales);
    }

    public function saveCredenciales($name, $contents)
    {
        $raw_credenciales = $this->serializer->serialize($contents);

        return file_put_contents($this->getFilename($name), $raw_credenciales);
    }

    /**
     * @todo: Deberia retornar algo útil.
     */
    public function getFilename($name)
    {
        return $this->base_dir . DIRECTORY_SEPARATOR . $name;
    }
}
