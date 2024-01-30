<?php

require_once(dirname(__FILE__) . '/../../classi/GestoreCreazioneProspetti.php');

class GestoreCreazioneProspettiTest
{
    public GestoreCreazioneProspetti $prospetto;
    private string $esito;

    public function __construct($m, $CdL, $data, $path)
    {
        $this->prospetto = new GestoreCreazioneProspetti($m, $CdL, $data, $path);
    }

    public function prelievoValori(): void
    {
        $this->esito = match ($this->prospetto->generaProspetti()) {
            1 => "ProspettoPDFLaureando non creato",
            2 => "ProspettoPDFCommissione non creato",
            3 => "Indice non creato",
            4 => "Prospetti non concatenati",
            default => "Niente da segnalare",
        };
    }

    public function getEsito(): string
    {
        return $this->esito;
    }
}