<?php

class StudenteIngInf extends Studente
{

    private string $bonus;
    private int $CFUinf;
    private float $mediaInf;

    public function __construct($m, $CdL)
    {
        parent::__construct($m, $CdL);
        $this->bonus = $this->setBonus();
        if ($this->bonus == "SI") {
            $this->EsamePeggiore();
        }
        $this->CFUinf = $this->setCFUinf();
        $this->mediaInf = $this->setMediaInf();
    }

    /**
     * @return string
     */
    private function setBonus(): string
    {
        $c = $this->getCarriera();
        $inizioCarriera = $c[0][0];
        $laureaEffettiva = $c[0][1];

        $dataBonus = DateTime::createFromFormat('d/m/Y', $inizioCarriera)
            ->modify('+4 years')
            ->modify('last day of May');
        $laureaEffettiva = DateTime::createFromFormat('d/m/Y', $laureaEffettiva);
        return ($dataBonus > $laureaEffettiva) ? "SI" : "NO";
    }

    /**
     * Trova l'esame peggiore che deve essere tolto in caso di bonus dal calcolo della media
     * @return void
     */
    private function EsamePeggiore(): void
    {
        if (!$this->bonus) {
            return;
        }
        $c = $this->getCarriera();
        $peggiore = ["Nome" => "", "Voto" => "", "Crediti" => ""];
        foreach ($c[1] as $esame) {
            if (!$esame->getMedia()) {
                continue;
            }
            if ($peggiore["Nome"] == "") {
                $peggiore["Nome"] = $esame->getNome();
                $peggiore["Voto"] = $esame->getVoto();
                $peggiore["Crediti"] = $esame->getCrediti();
            } else {
                if (($esame->getVoto() < $peggiore["Voto"] && $esame->getVoto() != 0) || ($esame->getVoto(
                        ) == $peggiore["Voto"] && $esame->getCrediti() > $peggiore["Crediti"])) {
                    $peggiore["Nome"] = $esame->getNome();
                    $peggiore["Voto"] = $esame->getVoto();
                    $peggiore["Crediti"] = $esame->getCrediti();
                }
            }
        }
        foreach ($c[1] as $esame) {
            if ($esame->getNome() == $peggiore["Nome"]) {
                $esame->setMediaPeggiore($peggiore["Nome"]);
                break;
            }
        }
    }

    /**
     * Calcola i CFU totali degli esami informatici
     * @return float
     */
    private function setCFUinf(): float
    {
        $c = $this->getCarriera();
        $somma = 0;
        foreach ($c[1] as $esame) {
            if ($esame->getInf()) {
                $somma += $esame->getCrediti();
            }
        }
        return $somma;
    }

    /**
     * Calcola la media degli esami informatici
     * @return float
     */
    private function setMediaInf(): float
    {
        $media_ = 0;
        $c = $this->getCarriera();
        foreach ($c[1] as $esame) {
            if ($esame->getMedia() && $esame->getInf()) {
                $media_ += ((double)$esame->getVoto() * (double)$esame->getCrediti());
            }
        }
        return round($media_ / $this->CFUinf, 3);
    }

    public function getMediaInf(): float
    {
        return $this->mediaInf;
    }

    public function getBonus(): string
    {
        return $this->bonus;
    }
}