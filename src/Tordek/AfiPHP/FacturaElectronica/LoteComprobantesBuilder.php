<?php

namespace Tordek\AfiPHP\FacturaElectronica;

class LoteComprobantesBuilder
{
    private $punto_de_venta;
    private $tipo;
    private $comprobantes = [];

    public function __construct($punto_de_venta, $tipo)
    {
        $this->punto_de_venta = $punto_de_venta;
        $this->tipo = $tipo;
    }

    public function build()
    {
        $comprobantes = [];

        foreach ($this->comprobantes as $comprobante) {
            $comprobantes[] = $comprobante->build();
        }

        return new LoteComprobantes(
            $this->tipo,
            $this->punto_de_venta,
            $comprobantes
        );
    }

    public function nuevoComprobante()
    {
        $factura = new ComprobanteBuilder();

        $this->comprobantes[] = $factura;

        return $factura;
    }

    public function asignarNumeros($ultimo_numero)
    {
        foreach ($this->comprobantes as $comprobante) {
            $cantidad = $comprobante->getCantidad();

            $comprobante
                ->numeroDesde($ultimo_numero + 1)
                ->numeroHasta($ultimo_numero + $cantidad);

            $ultimo_numero += $cantidad;
        }
    }
}
