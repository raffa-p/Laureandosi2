<?php

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfReader\PdfReaderException;

require_once(dirname(__FILE__) . '/../lib/fpdf184/fpdf.php');
require_once(dirname(__FILE__) . '/../lib/FPDI-2.5.0/src/autoload.php');
require_once(dirname(__FILE__) . '/Studente.php');
require_once(dirname(__FILE__) . '/StudenteIngInf.php');
require_once(dirname(__FILE__) . "/CdL.php");

class ProspettoPDFCommissione
{
    private Studente $studente;
    private int $startY;
    private string $path;

    public function __construct($s, $y, $p = ".")
    {
        $this->studente = $s;
        $this->startY = $y;
        $this->path = $p;
    }

    /**
     * Crea il prospetto singolo per la commissione a partire dal prospetto senza simulazione.
     * Può essere invocato solo se esiste già il pdf senza simulazioni.
     * @return Fpdi|null
     */
    public function generaProspettoPDFCommissione(): ?Fpdi
    {
        $pdf = new setasign\Fpdi\Fpdi();
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 1);
        try {
            $nomeCorto = CdL::getCdLcorto($this->studente->getCdL());
            if (!file_exists(
                dirname(
                    __FILE__
                ) . '/../' . $this->path . '/prospetti/' . $nomeCorto . '/' . $this->studente->getMatricola(
                ) . '_laureando.pdf'
            )) {
                throw new Exception();
            }
            $pdf->setSourceFile(
                dirname(
                    __FILE__
                ) . '/../' . $this->path . '/prospetti/' . $nomeCorto . '/' . $this->studente->getMatricola(
                ) . '_laureando.pdf'
            );
            $tplIdx = $pdf->importPage(1);
        } catch (PdfParserException|PdfReaderException|Exception) {
            return null;
        }
        $pdf->useImportedPage($tplIdx, 0, 0, 210);


        $pdf->SetFont("Arial", "", 12);
        // simulazione voto di laurea
        $pdf->SetY($this->startY + 5);
        $pdf->Cell(0, 7, "SIMULAZIONE DI VOTO DI LAUREA", 1, 0, 'C');
        $pdf->Ln();

        $pdf->SetFont("Arial", "", 10);

        $paramEsim = $this->studente->getSimulazioni();
        $T = $paramEsim[0][0];
        $C = $paramEsim[0][1];
        $T_C_step = $paramEsim[0][2];
        $T_C_max = $paramEsim[0][3];

        $maxY = 0;

        if ($T != 0 && $T_C_max - $T > 7) {
            $pdf->Cell(47.5, 6, "VOTO TESI (T)", 1, 0, 'C');
            $pdf->Cell(47.5, 6, "VOTO LAUREA", 1, 0, 'C');
            $tempX = $pdf->GetX();
            $pdf->Cell(47.5, 6, "VOTO TESI (T)", 1, 0, 'C');
            $pdf->Cell(0, 6, "VOTO LAUREA", 1, 1, 'C');
            $tempY = $pdf->GetY();
            for ($i = 0; $i <= ($T_C_max - $T) / 2; $i += $T_C_step) {
                $pdf->Cell(47.5, 6, $i + $T, 1, 0, 'C');
                $pdf->Cell(47.5, 6, $paramEsim[1][$i], 1, 0, 'C');
                $pdf->Ln();
                $maxY = $pdf->GetY();
            }
            $pdf->SetY($tempY);
            for ($i = (($T_C_max - $T) / 2) + 1; $i <= $T_C_max - $T; $i += $T_C_step) {
                $pdf->SetX($tempX);
                $pdf->Cell(47.5, 6, $i + $T, 1, 0, 'C');
                $pdf->Cell(47.5, 6, $paramEsim[1][$i], 1, 0, 'C');
                $pdf->Ln();
            }
        } else {
            if ($T != 0) {
                $pdf->Cell(95, 6, "VOTO TESI (T)", 1, 0, 'C');
                $pdf->Cell(95, 6, "VOTO LAUREA", 1, 1, 'C');
                for ($i = 0; $i <= $T_C_max - $T; $i += $T_C_step) {
                    $pdf->Cell(95, 6, $i + $T, 1, 0, 'C');
                    $pdf->Cell(95, 6, $paramEsim[1][$i], 1, 0, 'C');
                    $pdf->Ln();
                }
            } else {
                if ($C != 0 && $T_C_max - $C > 7) {
                    $pdf->Cell(47.5, 6, "VOTO COMMISSIONE (C)", 1, 0, 'C');
                    $pdf->Cell(47.5, 6, "VOTO LAUREA", 1, 0, 'C');
                    $tempX = $pdf->GetX();
                    $pdf->Cell(47.5, 6, "VOTO COMMISSIONE (C)", 1, 0, 'C');
                    $pdf->Cell(47.5, 6, "VOTO LAUREA", 1, 1, 'C');
                    $tempY = $pdf->GetY();
                    for ($i = 0; $i <= ($T_C_max - $C) / 2; $i += $T_C_step) {
                        $pdf->Cell(47.5, 6, $i + $C, 1, 0, 'C');
                        $pdf->Cell(47.5, 6, $paramEsim[1][$i], 1, 0, 'C');
                        $pdf->Ln();
                        $maxY = $pdf->GetY();
                    }
                    $pdf->SetY($tempY);
                    for ($i = (($T_C_max - $C) / 2) + 1; $i <= $T_C_max - $C; $i += $T_C_step) {
                        $pdf->SetX($tempX);
                        $pdf->Cell(47.5, 6, $i + $C, 1, 0, 'C');
                        $pdf->Cell(47.5, 6, $paramEsim[1][$i], 1, 0, 'C');
                        $pdf->Ln();
                    }
                } else {
                    $pdf->Cell(95, 6, "VOTO COMMISSIONE (C)", 1, 0, 'C');
                    $pdf->Cell(95, 6, "VOTO LAUREA", 1, 1, 'C');
                    for ($i = 0; $i <= $T_C_max - $C; $i += $T_C_step) {
                        $pdf->Cell(95, 6, $i + $C, 1, 0, 'C');
                        $pdf->Cell(95, 6, $paramEsim[1][$i], 1, 0, 'C');
                        $pdf->Ln();
                    }
                }
            }
        }

        if ($maxY != 0) {
            $pdf->SetY($maxY);
        }
        $pdf->Ln();
        $pdf->MultiCell(0, 6, $this->studente->getMessaggio());

        return $pdf;
    }
}