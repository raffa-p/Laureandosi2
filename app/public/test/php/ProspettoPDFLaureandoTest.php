<?php

require_once(dirname(__FILE__) . '/../../classi/ProspettoPDFLaureando.php');

class ProspettoPDFLaureandoTest
{
    public ProspettoPDFLaureando $prospetto;
    private array $esito;

    public function __construct($m, $CdL, $data, $path)
    {
        $this->prospetto = new ProspettoPDFLaureando($m, $CdL, $data, $path);
    }

    public function prelievoValori(): void
    {
        $this->esito["media-pesata"] = $this->prospetto->getStudente()->getMedia();
        $this->esito["crediti-media"] = $this->prospetto->getStudente()->getCFU();
        $this->esito["crediti_curriculari_conseguiti"] = $this->prospetto->getStudente()->getCreditiTot();
        if ($this->prospetto->getStudente()->getCdL() == "T. Ing. Informatica") {
            $this->esito["media-inf"] = $this->prospetto->getStudente()->getMediaInf();
            $this->esito["bonus"] = $this->prospetto->getStudente()->getBonus();
        }
    }

    public function getEsito(): array
    {
        return $this->esito;
    }
}