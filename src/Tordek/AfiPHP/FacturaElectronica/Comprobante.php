<?php

namespace Tordek\AfiPHP\FacturaElectronica;

/**
 * Comprobante fiscal.
 */
class Comprobante
{
    const CONCEPTO_PRODUCTOS = 1;
    const CONCEPTO_SERVICIOS = 2;
    const CONCEPTO_PRODUCTOS_Y_SERVICIOS = 3;

    const MONEDA_PESOS = ["PES", "1.00"];
    const DOCUMENTO_OTRO = "99";
    const DOCUMENTO_CUIT = "80";
    const DOCUMENTO_DNI = "96";

    const TIPO_FACTURA_A = 1;
    const TIPO_FACTURA_B = 6;
    const TIPO_FACTURA_C = 11;

    const TIPO_IVA_0 = 0;
    const TIPO_IVA_10_5 = 4;
    const TIPO_IVA_21 = 5;
    const TIPO_IVA_27 = 6;
    const TIPO_IVA_5 = 8;
    const TIPO_IVA_2_5 = 9;

    const VALORES_IVA = [
        self::TIPO_IVA_0 => 0,
        self::TIPO_IVA_10_5 => 0.105,
        self::TIPO_IVA_21 => 0.21,
        self::TIPO_IVA_27 => 0.27,
        self::TIPO_IVA_5 => 0.05,
        self::TIPO_IVA_2_5 => 0.025,
    ];

    const IMPUESTO_NACIONAL = 1;
    const IMPUESTO_PROVINCIAL = 2;
    const IMPUESTO_MUNICIPAL = 3;
    const IMPUESTO_INTERNO = 4;
    const IMPUESTO_OTRO = 99;

    private $concepto;
    private $documento;
    private $impNeto;
    private $numero_desde;
    private $numero_hasta;
    private $fecha;
    private $fecha_servicio_desde;
    private $fecha_servicio_hasta;
    private $fecha_vencimiento_pago;
    private $moneda;
    private $comprobantes_asociados;
    private $impuestos;
    private $tributos;
    private $iva;
    private $impIva;
    private $impTotConc;
    private $impOpEx;
    private $impTrib;
    private $opcionales;

    public function __construct(
        $concepto,
        $documento,
        $impNeto,
        $numero_desde,
        $numero_hasta,
        $fecha,
        $fecha_servicio_desde,
        $fecha_servicio_hasta,
        $fecha_vencimiento_pago,
        $moneda,
        $comprobantes_asociados,
        $impuestos,
        $tributos,
        $iva,
        $impIva,
        $impTotConc,
        $impOpEx,
        $impTrib,
        $opcionales,
        $cantidad
    ) {
        $this->concepto = $concepto;
        $this->documento = $documento;
        $this->impNeto = $impNeto;
        $this->numero_desde = $numero_desde;
        $this->numero_hasta = $numero_hasta;
        $this->fecha = $fecha;
        $this->fecha_servicio_desde = $fecha_servicio_desde;
        $this->fecha_servicio_hasta = $fecha_servicio_hasta;
        $this->fecha_vencimiento_pago = $fecha_vencimiento_pago;
        $this->moneda = $moneda;
        $this->comprobantes_asociados = $comprobantes_asociados;
        $this->impuestos = $impuestos;
        $this->tributos = $tributos;
        $this->iva = $iva;
        $this->impIva = $impIva;
        $this->impTotConc = $impTotConc;
        $this->impOpEx = $impOpEx;
        $this->impTrib = $impTrib;
        $this->opcionales = $opcionales;
        $this->cantidad = $cantidad;
    }

    public function getConcepto()
    {
        return $this->concepto;
    }

    public function getDocumento()
    {
        return $this->documento;
    }

    public function getImpNeto()
    {
        return $this->impNeto;
    }

    public function getNumeroDesde()
    {
        return $this->numero_desde;
    }

    public function getNumeroHasta()
    {
        return $this->numero_hasta;
    }

    public function getFecha()
    {
        return $this->fecha;
    }

    public function getFechaServicioDesde()
    {
        return $this->fecha_servicio_desde;
    }

    public function getFechaServicioHasta()
    {
        return $this->fecha_servicio_hasta;
    }

    public function getFechaVencimientoPago()
    {
        return $this->fecha_vencimiento_pago;
    }

    public function getMoneda()
    {
        return $this->moneda;
    }

    public function getComprobantesAsociados()
    {
        return $this->comprobantes_asociados;
    }

    public function getImpuestos()
    {
        return $this->impuestos;
    }

    public function getTributos()
    {
        return $this->tributos;
    }

    public function getIva()
    {
        return $this->iva;
    }

    public function getImpIva()
    {
        return $this->impIva;
    }

    public function getImpTotConc()
    {
        return $this->impTotConc;
    }

    public function getImpOpEx()
    {
        return $this->impOpEx;
    }

    public function getImpTrib()
    {
        return $this->impTrib;
    }

    public function getOpcionales()
    {
        return $this->opcionales;
    }

    public function getImpTotal()
    {
        return $this->impNeto
             + $this->impTotConc
             + $this->impOpEx
             + $this->impIva
             + $this->impTrib;
    }
}
