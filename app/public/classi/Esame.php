<?php

require_once(dirname(__FILE__) . '/FiltroEsami.php');

class Esame
{
    protected bool $media;
    private string $nome;
    private string $crediti;
    private string $voto;
    private string $data;
    private string $sovrann;
    private bool $informatico;
    private string $CdL;

    public function __construct($esami, $CdL)
    {
        $this->nome = is_array($esami['DES']) ? implode(', ', $esami['DES']) : $esami['DES'];
        $this->crediti = is_array($esami['PESO']) ? implode(', ', $esami['PESO']) : $esami['PESO'];
        $this->voto = (is_null(
            is_array($esami['VOTO']) ? implode(', ', $esami['VOTO']) : $esami['VOTO']
        ) ? 0 : (is_array($esami['VOTO']) ? implode(', ', $esami['VOTO']) : $esami['VOTO']));
        $this->sovrann = is_array($esami['SOVRAN_FLG']) ? implode(', ', $esami['SOVRAN_FLG']) : $esami['SOVRAN_FLG'];
        $this->data = is_array($esami['DATA_ESAME']) ? implode(', ', $esami['DATA_ESAME']) : $esami['DATA_ESAME'];
        $this->CdL = $CdL;
        $this->informatico = $this->controllaInformatico();
        $this->media = $this->setMedia();
    }

    /**
     * Controlla se l'esame è presente nella lista di quelli informatici
     * @return bool
     */
    private function controllaInformatico(): bool
    {
        $fileConf = json_decode(
            file_get_contents(dirname(__FILE__) . '/../fileDiConfigurazione/esamiInformatici.json'),
            true
        );
        if (in_array($this->getNome(), $fileConf)) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getNome(): string
    {
        return $this->nome;
    }

    public static function inProspetto($nome, $CdL): bool
    {
        if (in_array($nome, FiltroEsami::getEsamiNonCdL($CdL))) {
            return false;
        }
        return true;
    }

    /**
     * Controlla se si tratta dell'esame peggiore (voto più basso).
     * Viene invocato solo se lo studente ha diritto al bonus.
     * @param string $peggiore
     * @return void
     */
    public function setMediaPeggiore(string $peggiore = ""): void
    {
        if ($this->getNome() == $peggiore) {
            $this->media = false;
        }
    }

    /**
     * @return bool
     */
    public function getInf(): bool
    {
        return $this->informatico;
    }

    /**
     * @return int
     */
    public function getCrediti(): int
    {
        return (int)$this->crediti;
    }

    /**
     * @return string
     */
    public function getVoto(): string
    {
        return ($this->voto == "30  e lode") ? 33 : $this->voto;
    }

    /**
     * @return bool
     */
    public function getMedia(): bool
    {
        return $this->media;
    }

    /**
     * Setta la variabile $media.
     * @return bool
     */
    protected function setMedia(): bool
    {
        if ($this->sovrann) {
            return false;
        }
        if (in_array($this->nome, FiltroEsami::getEsamiNonAvg($this->CdL)) || in_array(
                $this->nome,
                FiltroEsami::getEsamiNonCdL($this->CdL)
            )) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

}