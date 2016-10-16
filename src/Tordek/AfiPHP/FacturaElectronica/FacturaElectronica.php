<?php

namespace Tordek\AfiPHP\FacturaElectronica;

class FacturaElectronica
{
    const CONCEPTO_PRODUCTOS = 1;
    const CONCEPTO_SERVICIOS = 2;
    const CONCEPTO_PRODUCTOS_Y_SERVICIOS = 3;

    private $auth_service;
    private $client;
    private $cuit;

    public function __construct($auth_service, $cuit)
    {
        $this->auth_service = $auth_service;
        $this->cuit = $cuit;

        /* TEMPFIX: Este servicio solo funciona sobre TLSv1.0, así que hay
           que habilitarlo explícitamente.
        */

        $context = stream_context_create(
            ['ssl' =>
                ['crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT |
                                    STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
                ],
            ]);

        // @todo: Por parám. Testing and... stuff.
        //$this->client = new \SoapClient("https://servicios1.afip.gov.ar/wsfev1/service.asmx?wsdl", //PRODUCCION
        $this->client = new \SoapClient("https://wswhomo.afip.gov.ar/wsfev1/service.asmx?wsdl", //HOMOLOGACION
                                        ['stream_context' => $context]);
    }

    public function FECompUltimoAutorizado($punto_de_venta, $tipo)
    {
        $request = [
            'PtoVta' => $punto_de_venta,
            'CbteTipo' => $tipo,
        ];

        $request = $this->addAuth($request);

        return $this->client->FECompUltimoAutorizado($request);
    }

    public function FEParamGetTiposCbte()
    {
        $request = [];

        $request = $this->addAuth($request);

        return $this->client->FEParamGetTiposCbte($request);
    }

    public function FEParamGetTiposTributos()
    {
        $request = [];

        $request = $this->addAuth($request);

        return $this->client->FEParamGetTiposTributos($request);
    }

    public function FEParamGetTiposIva()
    {
        $request = [];

        $request = $this->addAuth($request);

        return $this->client->FEParamGetTiposIva($request);
    }

    public function FEParamGetTiposMonedas()
    {
        $request = [];

        $request = $this->addAuth($request);

        return $this->client->FEParamGetTiposMonedas($request);
    }

    public function FEParamGetTiposDoc()
    {
        $request = [];

        $request = $this->addAuth($request);

        return $this->client->FEParamGetTiposDoc($request);
    }

    public function FEParamGetTiposOpcional()
    {
        $request = [];

        $request = $this->addAuth($request);

        return $this->client->FEParamGetTiposOpcional($request);
    }

    private function addAuth($request)
    {
        return array_merge($request, $this->getAuthBlock());
    }

    private function getAuthBlock()
    {
        $credenciales = $this->auth_service->getCredenciales("wsfe");

        return [
            'Auth' => [
                'Token' => $credenciales->getToken(),
                'Sign' => $credenciales->getSign(),
                'Cuit' => $this->cuit
            ]
        ];
    }

    public function getUltimoNumero($punto_de_venta, $tipo)
    {
        return $this->FECompUltimoAutorizado($punto_de_venta, $tipo)->FECompUltimoAutorizadoResult->CbteNro;
    }

    private function buildCabeceraCaeRequest($comprobantes)
    {
        $punto_de_venta = $comprobantes[0]->getPuntoDeVenta();
        $tipo = $comprobantes[0]->getTipo();

        foreach ($comprobantes as $comprobante) {
            if ($comprobante->getPuntoDeVenta() != $punto_de_venta) {
                throw new \InvalidArgumentException(
                    "Todos los comprobante de un lote deben tener el mismo punto de venta."
                );
            }

            if ($comprobantes->getTipo() != $tipo) {
                throw new \InvalidArgumentException("Todos los comprobante de un lote deben ser del mismo tipo.");
            }
        }

        return [
            'CantReg' => count($comprobantes),
            'PtoVta' => $punto_de_venta,
            'CbteTipo' => $tipo,
        ];
    }


    private function comprobanteToFeDetReq($comprobante)
    {
        $fe_det_req = [
            'Concepto' => $comprobante->getConcepto(),
            'DocTipo' => $comprobante->getDocumento()[0],
            'DocNro' => $comprobante->getDocumento()[1],
            'CbteDesde' => $comprobante->getNumeroDesde(),
            'CbteHasta' => $comprobante->getNumeroHasta(),
            'CbteFch' => $comprobante->getFecha()->format("Ymd"),
            'ImpTotConc' => $comprobante->getImpTotConc(),
            'ImpTotal' => $comprobante->getImpTotal(),
            'ImpNeto' => $comprobante->getImpNeto(),
            'ImpOpEx' => $comprobante->getImpOpEx(),
            'ImpTrib' => $comprobante->getImpTrib(),
            'ImpIVA' => $comprobante->getImpIva(),
            'MonId' => $comprobante->getMoneda()[0],
            'MonCotiz' => $comprobante->getMoneda()[1],
        ];

        $alics = $comprobante->getIva();
        if ($alics) {
            $fe_det_req['Iva'] = ['AlicIva' => $alics];
        }

        $tributos = $comprobante->getTributos();
        if ($tributos) {
            $fe_det_req['Tributos'] = ['Tributo' => $tributos];
        }

        return $fe_det_req;
    }

    private function buildCaeRequest($lote)
    {
        $comprobantes = $lote->getComprobantes();

        $fecabreq = [
            'PtoVta' => $lote->getPuntoDeVenta(),
            'CbteTipo' => $lote->getTipo(),
            'CantReg' => count($comprobantes)
        ];

        $fecaedetrequests = [];
        foreach ($comprobantes as $comprobante) {
            $fecaedetrequests[] = $this->comprobanteToFeDetReq($comprobante);
        }

        return ['FeCAEReq' => [
            'FeCabReq' => $fecabreq,
            'FeDetReq' => ['FECAEDetRequest' => $fecaedetrequests]
        ]];
    }

    public function solicitarCae(LoteComprobantes $comprobantes)
    {
        $request = $this->buildCaeRequest($comprobantes);
        $request = $this->addAuth($request);

        return $this->client->FECAESolicitar($request);
    }
}
