<?php

namespace Tordek\AfiPHP\FacturaElectronica;

class ComprobanteBuilder
{
    private $concepto;
    private $neto;

    private $documento = [Comprobante::DOCUMENTO_OTRO, '0'];
    private $numero_desde;
    private $numero_hasta;

    private $fecha = null;
    private $fecha_servicio_desde = null;
    private $fecha_servicio_hasta = null;
    private $fecha_vencimiento_pago = null;
    private $moneda = Comprobante::MONEDA_PESOS;
    private $comprobantes_asociados = null;
    private $impuestos;
    private $iva = [];
    private $impIva = 0;
    private $impOpEx = 0;
    private $impTotConc = 0;
    private $tributos = [];
    private $impTrib = 0;
    private $opcionales;
    private $cantidad = 1;

    public function concepto($concepto)
    {
        $this->concepto = $concepto;
        return $this;
    }

    public function documento($tipoDocumento, $numeroDocumento)
    {
        $this->documento = [$tipoDocumento, $numeroDocumento];
        return $this;
    }

    public function numeroDesde($numero_desde)
    {
        $this->numero_desde = $numero_desde;
        return $this;
    }

    public function numeroHasta($numero_hasta)
    {
        $this->numero_hasta = $numero_hasta;
        return $this;
    }

    public function cantidad($cantidad)
    {
        $this->cantidad = $cantidad;
        return $this;
    }

    public function getCantidad()
    {
        return $this->cantidad;
    }

    public function neto($neto)
    {
        $this->neto = $neto;
        return $this;
    }

    public function exento($impOpEx)
    {
        $this->impOpEx = $impOpEx;
        return $this;
    }

    public function noGravado($impTotConc)
    {
        $this->impTotConc = $impTotConc;
        return $this;
    }

    public function fecha($fecha)
    {
        $this->fecha = $fecha;
        return $this;
    }

    public function fechaServicioDesde($fecha_servicio_desde)
    {
        $this->fecha_servicio_desde = $fecha_servicio_desde;
        return $this;
    }

    public function fechaServicioHasta($fecha_servicio_hasta)
    {
        $this->fecha_servicio_hasta = $fecha_servicio_hasta;
        return $this;
    }

    public function fechaVencimientoPago($fecha_vencimiento_pago)
    {
        $this->fecha_vencimiento_pago = $fecha_vencimiento_pago;
        return $this;
    }

    public function moneda($moneda)
    {
        $this->moneda = $moneda;
        return $this;
    }

    public function comprobantesAsociados($comprobantes_asociados)
    {
        $this->comprobantes_asociados = $comprobantes_asociados;
        return $this;
    }

    public function impuestos($impuestos)
    {
        $this->impuestos = $impuestos;
        return $this;
    }

    public function iva($tipo, $base)
    {
        $importe = $base * Comprobante::VALORES_IVA[$tipo];

        $this->iva[] = [
            'Id' => $tipo,
            'BaseImp' => $base,
            'Importe' => $importe,
        ];

        $this->impIva += $importe;

        return $this;
    }

    public function tributo($tipo, $base, $alic, $desc = null)
    {
        $importe = $base * $alic;

        $tributo = [
            'Id' => $tipo,
            'BaseImp' => $base,
            'Alic' => $alic,
            'Importe' => $importe,
        ];

        if ($desc !== null) {
            $tributo['Desc'] = $desc;
        }

        $this->tributos[] = $tributo;

        $this->impTrib += $importe;

        return $this;
    }

    public function opcionales($opcionales)
    {
        $this->opcionales = $opcionales;
        return $this;
    }

    public function build()
    {
        if ($this->fecha === null) {
            $this->fecha = new \DateTimeImmutable();
        }

        return new Comprobante(
            $this->concepto,
            $this->documento,
            $this->neto,
            $this->numero_desde,
            $this->numero_hasta,
            $this->fecha,
            $this->fecha_servicio_desde,
            $this->fecha_servicio_hasta,
            $this->fecha_vencimiento_pago,
            $this->moneda,
            $this->comprobantes_asociados,
            $this->impuestos,
            $this->tributos,
            $this->iva,
            $this->impIva,
            $this->impTotConc,
            $this->impOpEx,
            $this->impTrib,
            $this->opcionales,
            $this->cantidad
        );
    }
}
