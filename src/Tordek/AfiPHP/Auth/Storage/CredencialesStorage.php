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

interface CredencialesStorage {
    public function loadCredenciales($name);

    public function saveCredenciales($name, $contents);
}
