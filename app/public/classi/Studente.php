<?php

require_once(dirname(__FILE__) . "/GestioneCarrieraStudente.php");
require_once(dirname(__FILE__) . "/Esame.php");
require_once(dirname(__FILE__) . "/CdL.php");
require_once(dirname(__FILE__) . "/FiltroEsami.php");

class Studente
{
    private int $matricola;
    private string|null $CdL;
    private array|null $anagrafica;
    private array|null $carriera;

    public function __construct($m, $CdL)
    {
        $this->matricola = $m;
        $this->CdL = $CdL;
        $this->anagrafica = $this->setAnagrafica();
        $this->carriera = $this->setCarriera();
    }

    /**
     * @return array|null
     */
    private function setAnagrafica(): ?array
    {
        return GestioneCarrieraStudente::restituisciAnagraficaStudente($this->matricola);
    }

    /**
     * Prepara la carriera del laureando.
     * Istanzia un oggetto Esame per ogni esame del laureando.
     * Ritorna un array contenente: [0] -> inizio e fine carriere; [1] -> esami.
     * @return array|null
     */
    private function setCarriera(): ?array
    {
        try {
            $ARRAYdocument = GestioneCarrieraStudente::restituisciCarrieraStudente($this->matricola);
            if ($ARRAYdocument["Esami"]["Esame"] == null) {
                throw new Exception("CdL sbagliato");
            }
        } catch (Exception) {
            return null;
        }
        $infoEcarriera = [];
        $infoEcarriera[0][0] = "";
        $carriera = [];

        foreach ($ARRAYdocument["Esami"]["Esame"] as $esame) {
            $nuovoEsame = new Esame($esame, $this->CdL);
            $carriera[] = $nuovoEsame;
            if ($infoEcarriera[0][0] == "") {
                $infoEcarriera[0][0] = $esame["INIZIO_CARRIERA"];
                $infoEcarriera[0][1] = $esame["DATA_CHIUSURA"];
            }
        }
        $infoEcarriera[1] = $carriera;
        usort($infoEcarriera[1], function ($a, $b): bool {
            return DateTime::createFromFormat('d/m/Y', $a->getData()) > DateTime::createFromFormat(
                    'd/m/Y',
                    $b->getData()
                );
        });
        return $infoEcarriera;
    }

    public function getAnagrafica(): ?array
    {
        return $this->anagrafica;
    }

    public function getCarriera(): ?array
    {
        return $this->carriera;
    }

    public function getCdL(): string
    {
        return $this->CdL;
    }

    public function getMatricola(): int
    {
        return $this->matricola;
    }

    public function getCreditiTot(): int
    {
        return $this->calcoloCreditiTot();
    }

    /**
     * Calcola i CFU conseguiti dallo studente
     * @return int
     */
    private function calcoloCreditiTot(): int
    {
        $somma = 0;
        $c = $this->carriera;
        foreach ($c[1] as $esame) {
            if (!in_array($esame->getNome(), FiltroEsami::getEsamiNonCdL($this->CdL))) {
                $somma += (int)$esame->getCrediti();
            }
        }
        return $somma;
    }

    public function getMedia(): float
    {
        return $this->calcoloMedia();
    }

    /**
     * @return float
     */
    private function calcoloMedia(): float
    {
        if ($this->calcoloCFU() == 0) {
            return 0;
        }
        $media_ = 0;
        $c = $this->carriera;
        foreach ($c[1] as $esame) {
            if ($esame->getMedia()) {
                $media_ += ((int)$esame->getVoto() * (int)$esame->getCrediti());
            }
        }
        return round($media_ / $this->calcoloCFU(), 3);
    }

    /**
     * Calcola i CFU conseguiti dallo studente che fanno media
     * @return int
     */
    private function calcoloCFU(): int
    {
        $somma = 0;
        $c = $this->carriera;
        foreach ($c[1] as $esame) {
            if ($esame->getMedia()) {
                $somma += $esame->getCrediti();
            }
        }
        return $somma;
    }

    public function getCFU(): int
    {
        return $this->calcoloCFU();
    }

    public function getCreditiPrevisti(): int
    {
        return CdL::creditiPrevisti($this->CdL);
    }

    public function getFormula(): string
    {
        return CdL::trovaFormula($this->CdL);
    }

    public function getSimulazioni(): array
    {
        return $this->calcoloSimulazioni();
    }

    /**
     * Calcola le simulazioni dei voti di laurea
     * @return array
     */
    private function calcoloSimulazioni(): array
    {
        $parametriESimulazioni = [];
        $parametri = [];
        $formula = CdL::trovaFormula($this->CdL);
        $Tmin = CdL::getParametri($this->CdL)["Tmin"];
        $Tmax = CdL::getParametri($this->CdL)["Tmax"];
        $Tstep = CdL::getParametri($this->CdL)["Tstep"];
        $Cmin = CdL::getParametri($this->CdL)["Cmin"];
        $Cmax = CdL::getParametri($this->CdL)["Cmax"];
        $Cstep = CdL::getParametri($this->CdL)["Cstep"];
        $parametri[0] = $Tmin;
        $parametri[1] = $Cmin;
        $parametri[2] = ($Tmin == 0) ? $Cstep : $Tstep;
        $parametri[3] = ($Tmin == 0) ? $Cmax : $Tmax;

        $parametriESimulazioni[0] = $parametri;

        $risCompleti = [];
        if ($Tmin != 0) {
            for ($T = $Tmin; $T <= $Tmax; $T += $Tstep) {
                $risultato = 0;
                $formula_ = str_replace(['M', 'CFU', 'C'], [$this->calcoloMedia(), $this->calcoloCFU(), 0], $formula);
                $formula_ = str_replace('T', $T, $formula_);
                eval('$risultato = ' . $formula_ . ';');
                $risCompleti[$T - $Tmin] = round($risultato, 3);
            }
            $parametriESimulazioni[1] = $risCompleti;
        } else {
            for ($C = $Cmin; $C <= $Cmax; $C += $Cstep) {
                $risultato = 0;
                $formula_ = str_replace(['M', 'CFU', 'T'], [$this->calcoloMedia(), $this->calcoloCFU(), 0], $formula);
                $formula_ = str_replace('C', $C, $formula_);
                eval('$risultato = ' . $formula_ . ';');
                $risCompleti[$C - $Cmin] = round($risultato, 3);
            }
            $parametriESimulazioni[1] = $risCompleti;
        }
        return $parametriESimulazioni;
    }

    public function getMessaggio(): string
    {
        return CdL::trovaMessaggio($this->CdL);
    }

    public function getEmail(): string
    {
        return $this->anagrafica['email'];
    }
}