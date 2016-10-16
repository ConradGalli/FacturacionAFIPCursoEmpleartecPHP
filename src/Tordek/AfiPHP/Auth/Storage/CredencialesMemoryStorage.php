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
 * Almacén simple a RAM.
 */
class CredencialesMemoryStorage implements CredencialesStorage
{
    private $credenciales = [];

    public function loadCredenciales($name)
    {
        if (!array_key_exists($name, $this->credenciales)) {
            return null;
        }

        return $this->credenciales[$name];
    }

    public function saveCredenciales($name, $contents)
    {
        $this->credenciales[$name] = $contents;
    }
}
