<?php

require_once(dirname(__FILE__) . '/../lib/fpdf184/fpdf.php');
require_once(dirname(__FILE__) . '/../lib/FPDI-2.5.0/src/autoload.php');
require_once(dirname(__FILE__) . '/Studente.php');
require_once(dirname(__FILE__) . '/StudenteIngInf.php');
require_once(dirname(__FILE__) . "/CdL.php");


class ProspettoPDFLaureando
{

    private Studente $studente;
    private $data;
    private int $maxY;
    private string $path;

    public function __construct($m, $CdL, $d, $p = ".")
    {
        $this->studente = ($CdL == "T. Ing. Informatica") ? new StudenteIngInf($m, $CdL) : new Studente($m, $CdL);
        $this->data = strtotime($d);
        $this->data = date('Y-m-d', $this->data);
        $this->path = $p;
    }

    /**
     * @return Studente
     */
    public function getStudente(): Studente
    {
        return $this->studente;
    }

    /**
     * @return int
     */
    public function getMaxY(): int
    {
        return $this->maxY ?? 0;
    }

    /**
     * Crea il pdf per il laureando senza simulazioni.
     * @return FPDF|null
     */
    public function generaProspettoPDFLaureando(): ?FPDF
    {
        $carriera = $this->studente->getCarriera();
        $anagrafica = $this->studente->getAnagrafica();
        if (is_null($carriera) || is_null($anagrafica)) {
            return null;
        }
        // creo il pdf
        $prospetto = new FPDF();
        $prospetto->SetTopMargin(2);
        $prospetto->SetAutoPageBreak(true, 2);
        $prospetto->AddPage();

        // titolo e sottotitolo
        $prospetto->SetFont("Arial", "", 13);
        $prospetto->Cell(0, 7, $this->studente->getCdL(), 0, 1, "C");
        $prospetto->Cell(0, 7, "CARRIERA E SIMULAZIONE DEL VOTO DI LAUREA", 0, 2, "C");
        $prospetto->Ln(2);


        // anagrafica
        $prospetto->SetFont("Arial", "", 10);

        $prospetto->Cell(60, 7, "Matricola:", 'LT');
        $prospetto->Cell(0, 7, $this->studente->getMatricola(), 'RT');
        $prospetto->Ln(5);
        $prospetto->Cell(60, 7, "Nome:", 'L');
        $prospetto->Cell(0, 7, $anagrafica['Nome'], 'R');
        $prospetto->Ln(5);
        $prospetto->Cell(60, 7, "Cognome:", 'L');
        $prospetto->Cell(0, 7, $anagrafica['Cognome'], 'R');
        $prospetto->Ln(5);
        $prospetto->Cell(60, 7, "Email:", 'L');
        $prospetto->Cell(0, 7, $anagrafica['email'], 'R');
        $prospetto->Ln(5);
        if ($this->studente->getCdL() == "T. Ing. Informatica") {
            $prospetto->Cell(60, 7, "Data:", 'L');
            $prospetto->Cell(0, 7, $this->data, 'R');
            $prospetto->Ln(5);
            $prospetto->Cell(60, 7, "Bonus:", 'LB');
            $prospetto->Cell(0, 7, $this->studente->getBonus(), 'RB');
            $prospetto->Ln(9);
        } else {
            $prospetto->Cell(60, 7, "Data:", 'LB');
            $prospetto->Cell(0, 7, $this->data, 'RB');
            $prospetto->Ln(9);
        }

        $offset = ($this->studente->getCdL() == "T. Ing. Informatica") ? 0 : 13;
        $width = [138 + $offset, 13];

        // carriera -> intestazione
        $prospetto->SetFont("Arial", "", 11);
        $prospetto->Cell($width[0], 6, "ESAMI", 1, 0, 'C');
        $prospetto->Cell($width[1], 6, "CFU", 1, 0, 'C');
        $prospetto->Cell($width[1], 6, "VOT", 1, 0, 'C');
        $prospetto->Cell($width[1], 6, "MED", 1, 0, 'C');
        if ($this->studente->getCdL() == "T. Ing. Informatica") {
            $prospetto->Cell($width[1], 6, "INF", 1, 0, 'C');
        }
        $prospetto->Ln();

        $prospetto->SetFont("Arial", "", 10);

        // carriera -> esami
        foreach ($carriera[1] as $esame) {
            if (!Esame::inProspetto($esame->getNome(), $this->studente->getCdL()) || $esame->getNome() == "true") {
                continue;
            }

            $prospetto->Cell($width[0], 5, $esame->getNome(), '1');
            $prospetto->Cell($width[1], 5, $esame->getCrediti(), '1', 0, 'C');
            $prospetto->Cell($width[1], 5, $esame->getVoto(), '1', 0, 'C');
            if ($esame->getMedia()) {
                $prospetto->Cell($width[1], 5, "x", '1', 0, 'C');
            } else {
                $prospetto->Cell($width[1], 5, "", '1', 0, 'C');
            }
            if ($this->studente->getCdL() == "T. Ing. Informatica") {
                if ($esame->getInf()) {
                    $prospetto->Cell($width[1], 5, "x", '1', 0, 'C');
                } else {
                    $prospetto->Cell($width[1], 5, "", '1', 0, 'C');
                }
            }
            $prospetto->Ln();
        }
        $prospetto->Ln(3);

        // dati calcolati
        $prospetto->Cell(60, 7, "Media pesata:", 'LT');
        $prospetto->Cell(0, 7, $this->studente->getMedia(), 'TR');
        $prospetto->Ln(5);
        $prospetto->Cell(60, 7, "Crediti che fanno media (CFU):", 'L');
        $prospetto->Cell(0, 7, $this->studente->getCFU(), 'R');
        $prospetto->Ln(5);
        $prospetto->Cell(60, 7, "Crediti curriculari conseguiti:", 'L');
        $prospetto->Cell(0, 7, $this->studente->getCreditiTot() . "/" . $this->studente->getCreditiPrevisti(), 'R');
        $prospetto->Ln(5);
        if ($this->studente->getCdL() == "T. Ing. Informatica") {
            $prospetto->Cell(60, 7, "Voto di tesi (T):", 'L');
            $prospetto->Cell(0, 7, "0", 'R');
            $prospetto->Ln(5);
            $prospetto->Cell(60, 7, "Formula calcolo voto di laurea:", 'L');
            $prospetto->Cell(0, 7, $this->studente->getFormula(), 'R');
            $prospetto->Ln(5);
            $prospetto->Cell(60, 7, "Media pesata esami INF:", 'LB');
            $prospetto->Cell(0, 7, $this->studente->getMediaInf(), 'RB');
            $prospetto->Ln(5);
        } else {
            $prospetto->Cell(60, 7, "Formula calcolo voto di laurea:", 'LB');
            $prospetto->Cell(0, 7, $this->studente->getFormula(), 'RB');
            $prospetto->Ln(5);
        }

        $this->maxY = $prospetto->GetY();

        $nomeCorto = CdL::getCdLcorto($this->studente->getCdL());
        $prospetto->Output(
            dirname(__FILE__) . "/../" . $this->path . "/prospetti/" . $nomeCorto . "/" . $this->studente->getMatricola(
            ) . "_laureando.pdf",
            'F'
        );

        return $prospetto;
    }

}