<?php

namespace Tordek\AfiPHP\FacturaElectronica;

class LoteComprobantes
{
    private $tipo;
    private $punto_de_venta;
    private $comprobantes;

    public function __construct($tipo, $punto_de_venta, $comprobantes)
    {
        $this->tipo = $tipo;
        $this->punto_de_venta = $punto_de_venta;
        $this->comprobantes = $comprobantes;
    }

    public function getTipo()
    {
        return $this->tipo;
    }

    public function getPuntoDeVenta()
    {
        return $this->punto_de_venta;
    }

    public function getComprobantes()
    {
        return $this->comprobantes;
    }
}
