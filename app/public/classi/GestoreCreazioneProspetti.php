<?php

require_once(dirname(__FILE__) . '/ProspettoPDFLaureando.php');
require_once(dirname(__FILE__) . '/ProspettoPDFCommissione.php');
require_once(dirname(__FILE__) . '/../lib/pdf-merger-master/src/PDFMerger/PDFMerger.php');
require_once(dirname(__FILE__) . '/../lib/FPDI-2.5.0/src/Fpdi.php');
require_once(dirname(__FILE__) . '/../lib/fpdf184/fpdf.php');


class GestoreCreazioneProspetti
{
    private string|array $elencoMatricole;
    private string $CdL;
    private string $data;
    private string $path;

    public function __construct($m = "", $c = "", $d = "", $p = ".")
    {
        $this->elencoMatricole = ($m != "") ? array_map(
            'intval',
            preg_split('/\s+/', $m)
        ) : "";
        $this->CdL = $c;
        $this->data = $d;
        $this->path = $p;
    }

    /**
     * Istanzia un oggetto ProspettoPDFLaureando e produce il pdf senza simulazione.
     * Istanzia un oggetto ProspettoPDFCommissione e produce il pdf con la simulazione.
     * Prospetti salvati nella cartella prospetti.
     * Crea l'indice della prima pagina per il prospetto della commissione.
     * Concatena i prospetti con l'indice come prima pagina.
     * Crea un file coda_prospetti.json per tenere traccia dei prospetti creati.
     * Elimina il file di indice prodotto.
     * @return int
     */
    public function generaProspetti(): int
    {
        $infoLaureandi[0]["numero"] = 1;
        $nomeCorto = CdL::getCdLcorto($this->CdL);
        $this->preSave($nomeCorto);
        try {
            for ($i = 0; $i < count($this->elencoMatricole); $i++) {
                $prospettoLaureando = new ProspettoPDFLaureando(
                    $this->elencoMatricole[$i],
                    $this->CdL,
                    $this->data,
                    $this->path
                );
                $pdfL = $prospettoLaureando->generaProspettoPDFLaureando();
                if (is_null($pdfL)) {
                    throw new Exception("", 1);
                }
                $prospettoCommissione = new ProspettoPDFCommissione(
                    $prospettoLaureando->getStudente(),
                    $prospettoLaureando->getMaxY(),
                    $this->path
                );
                $pdfC = $prospettoCommissione->generaProspettoPDFCommissione();
                if (is_null($pdfC)) {
                    throw new Exception("", 2);
                }
                $this->elencoMatricole[$i] = preg_replace('/\s+/', '', $this->elencoMatricole[$i]);
                $pdfC->Output(
                    dirname(
                        __FILE__
                    ) . "/../" . $this->path . "/prospetti/" . $nomeCorto . "/" . $this->elencoMatricole[$i] . "_laureando_sim.pdf",
                    'F'
                );

                $infoLaureandi[$i + 1]['Matricola'] = $prospettoLaureando->getStudente()->getMatricola();
                $infoLaureandi[$i + 1]['Cognome'] = $prospettoLaureando->getStudente()->getAnagrafica()['Cognome'];
                $infoLaureandi[$i + 1]['Nome'] = $prospettoLaureando->getStudente()->getAnagrafica()['Nome'];
                $infoLaureandi[$i + 1]['email'] = $prospettoLaureando->getStudente()->getEmail();
                $infoLaureandi[$i + 1]['CdL'] = $prospettoLaureando->getStudente()->getCdL();
                $infoLaureandi[$i + 1]['Data'] = $this->data;
            }
            if ($this->creaIndice($infoLaureandi) != 0) {
                throw new Exception("", 3);
            }

            if ($this->concatenaProspetti($nomeCorto) != 0) {
                throw new Exception("", 4);
            }

            $this->setCodaProspetti($infoLaureandi);
            $this->eliminaFileIntermedi();
        } catch (Exception $e) {
            if ($e->getCode() == 1) {
                return 1;
            }
            if ($e->getCode() == 2) {
                return 2;
            }
            if ($e->getCode() == 3) {
                return 3;
            }
            if ($e->getCode() == 4) {
                return 4;
            }
        }
        return 00;
    }

    private function preSave($corto): void
    {
        if (!$this->controllaDirectory($corto)) {
            mkdir(dirname(__FILE__) . "/../" . $this->path . "/prospetti/" . $corto);
        }
    }

    private function controllaDirectory($corto): bool
    {
        if (!is_dir(dirname(__FILE__) . "/../" . $this->path . "/prospetti/" . $corto)) {
            return false;
        }
        return true;
    }

    /**
     * A partire da un array fornito in input crea un pdf con l'indice dei laureandi
     * @param $infoLaureandi
     * @return int
     */
    private function creaIndice($infoLaureandi): int
    {
        try {
            $indice = new FPDF();
            $indice->AddPage();
            $indice->SetFont("Arial", "", 13);
            $indice->Cell(0, 7, $this->CdL, 0, 1, "C");
            $indice->SetFont("Arial", "", 11.5);
            $indice->Cell(
                0,
                7,
                "LAUREANDOSI 2 - Progettazione: r.prota@studenti.unipi.it, Amministrazione: nome.cognome.@unipi.it",
                0,
                1,
                "C"
            );
            $indice->SetFont("Arial", "", 13);
            $indice->Cell(0, 13, "LISTA LAUREANDI", 0, 1, "C");
            $indice->SetFont("Arial", "", 12);
            $indice->Cell(47.5, 7, "COGNOME", 1, 0, "C");
            $indice->Cell(47.5, 7, "NOME", 1, 0, "C");
            $indice->Cell(47.5, 7, "CDL", 1, 0, "C");
            $indice->Cell(47.5, 7, "VOTO LAUREA", 1, 0, "C");
            $indice->Ln();
            $indice->SetFont("Arial", "", 10);

            for ($i = 1; $i < count($infoLaureandi); $i++) {
                $indice->Cell(47.5, 5, $infoLaureandi[$i]['Cognome'], 1, 0, "C");
                $indice->Cell(47.5, 5, $infoLaureandi[$i]['Nome'], 1, 0, "C");
                $indice->Cell(47.5, 5, "", 1, 0, "C");
                $indice->Cell(47.5, 5, "   /110", 1, 0, "C");
                $indice->Ln();
            }
            $indice->Output('F', dirname(__FILE__) . '/../prospetti/indice_' . $this->data . '.pdf');
            if (!file_exists(dirname(__FILE__) . '/../prospetti/indice_' . $this->data . '.pdf')) {
                throw new Exception();
            }
        } catch (Exception) {
            return 1;
        }
        return 0;
    }

    /**
     * Concatena i prospetti tramite la libreria PDFMerger
     * @param $cdlCorto
     * @return int
     */
    private function concatenaProspetti($cdlCorto): int
    {
        try {
            $merge = new Clegginabox\PDFMerger\PDFMerger();
            $merge->addPDF(dirname(__FILE__) . "/../prospetti/indice_" . $this->data . ".pdf");
            for ($i = 0; $i < count($this->elencoMatricole); $i++) {
                $merge->addPDF(
                    dirname(
                        __FILE__
                    ) . "/../" . $this->path . "/prospetti/" . $cdlCorto . "/" . $this->elencoMatricole[$i] . "_laureando_sim.pdf"
                );
            }
            $merge->merge(
                'file',
                dirname(
                    __FILE__
                ) . "/../" . $this->path . "/prospetti/" . $cdlCorto . "/" . $this->CdL . "_" . $this->data . ".pdf"
            );
        } catch (Exception) {
            return 1;
        }
        return 0;
    }

    /**
     * Crea un file coda_prospetti.json per tenere traccia dei prospetti creati a partire da un array in input contenente le informazioni principali.
     * @param $infoLaureandi
     * @return void
     */
    private function setCodaProspetti($infoLaureandi): void
    {
        $JSONdocument = json_encode($infoLaureandi, JSON_PRETTY_PRINT);
        $fp = fopen(dirname(__FILE__) . "/../" . $this->path . "/cache/" . $this->CdL . "_coda_prospetti.json", 'w');
        fwrite($fp, $JSONdocument);
        fclose($fp);
    }

    /**
     * Elimina il file indice creato dalla funzione creaIndice.
     * Viene chiamata solo dopo la chiamata di concatenaProspetti.
     * @return void
     */
    private function eliminaFileIntermedi(): void
    {
        unlink(dirname(__FILE__) . '/../prospetti/indice_' . $this->data . '.pdf');
    }
}