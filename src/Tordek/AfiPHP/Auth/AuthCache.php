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
 * Cache para servicios de autenticación. Usa un almacén
 * {@link CredencialesStorage} para solicitar credenciales solo cuando es
 * necesario.
 */
class AuthCache implements AuthService
{
    private $auth_service;
    private $storage_service;

    /**
     * Constructor.
     *
     * @param AuthService $auth_service El servicio de autenticación real.
     * @param Storage\CredencialesStorage $storage_service Un almacén para las credenciales.
     */
    public function __construct(
        $auth_service,
        $storage_service = null
    ) {
        if ($auth_service === null) {
            throw new \InvalidArgumentException('$auth_service cannot be null');
        }

        if ($storage_service === null) {
            $storage_service = new Storage\CredencialesMemoryStorage();
        }

        $this->auth_service = $auth_service;
        $this->storage_service = $storage_service;
    }

    public function getCredenciales($servicio)
    {
        $credenciales = $this->storage_service->loadCredenciales($servicio);

        if ($credenciales !== null && $this->credencialesExpired($credenciales)) {
            $credenciales = null;
        }

        if ($credenciales === null) {
            $credenciales = $this->auth_service->getCredenciales($servicio);
            $this->storage_service->saveCredenciales($servicio, $credenciales);
        }

        return $credenciales;
    }

    private function credencialesExpired($credenciales)
    {
        $now = new \DateTimeImmutable();

        return $now >= $credenciales->getExpirationTime();
    }
}
