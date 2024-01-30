<?php

require_once(dirname(__FILE__) . "/../../classi/ProspettoPDFLaureando.php");
require_once(dirname(__FILE__) . "/../../classi/ProspettoPDFCommissione.php");
require_once(dirname(__FILE__) . "/../../classi/GestioneCarrieraStudente.php");
require_once(dirname(__FILE__) . "/../../classi/GestoreCreazioneProspetti.php");
require_once(dirname(__FILE__) . "/../../classi/Studente.php");
require_once(dirname(__FILE__) . "/ProspettoPDFLaureandoTest.php");
require_once(dirname(__FILE__) . "/GestoreCreazioneProspettiTest.php");
require_once(dirname(__FILE__) . "/../../classi/CdL.php");

error_reporting(E_ERROR | E_PARSE);
new Test();

class Test
{
    private array $testPrelievoDati;
    private array $input;


    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->input = $this->getInput();
        $this->run();
    }

    private function getInput(): array
    {
        $content = file_get_contents(dirname(__FILE__) . '/../oracolo/matricole_test.json');
        return json_decode($content, true);
    }

    /**
     * @return void
     * @throws Exception
     */
    private function run(): void
    {
        $counter = 0;
        foreach ($this->input as $casoTest) {
            // test GCS
            $this->testPrelievoDati[0] = !is_null(
                GestioneCarrieraStudente::restituisciAnagraficaStudente($casoTest["matricola"])
            );
            $this->testPrelievoDati[1] = !is_null(
                GestioneCarrieraStudente::restituisciCarrieraStudente($casoTest["matricola"])
            );
            if ($this->testPrelievoDati[0] && $this->testPrelievoDati[1]) {
                $eStud[$counter] = "Corretto";
                $cStud[$counter] = "#00ff00";
                $causa[$counter]["GCS"] = "Niente da segnalare";
            } else {
                $eStud[$counter] = "Errore";
                $cStud[$counter] = "#ff0000";
                $causa[$counter]["GCS"] = "Laureando non trovato";
            }

            // test ProspettoPDFLaureando
            $this->preSave(CdL::getCdLcorto($casoTest["CdL"]));
            $testCreazioneLaureando = new ProspettoPDFLaureandoTest(
                $casoTest["matricola"],
                $casoTest["CdL"],
                "0001-01-01",
                "test"
            );
            $testPDF = $testCreazioneLaureando->prospetto->generaProspettoPDFLaureando();
            $testCreazioneLaureando->prelievoValori();
            if (is_null($testPDF)) {
                $eLaur[$counter] = "Errore";
                $cLaur[$counter] = "#ff0000";
                $causa[$counter]["Bonus-media"] = "Prospetto non creato";
            } else {
                if ($testCreazioneLaureando->getEsito()["media-pesata"] == $casoTest["media_pesata"]
                    && $testCreazioneLaureando->getEsito()["crediti-media"] == $casoTest["crediti_media"]
                    && $testCreazioneLaureando->getEsito(
                    )["crediti_curriculari_conseguiti"] == $casoTest["crediti_curriculari_conseguiti"]) {
                    if ($testCreazioneLaureando->prospetto->getStudente()->getCdL() == "T. Ing. Informatica") {
                        if ($testCreazioneLaureando->getEsito()["media-inf"] == $casoTest["media_pesata_inf"]
                            && $testCreazioneLaureando->getEsito()["bonus"] == $casoTest["bonus"]) {
                            $eLaur[$counter] = "Corretto";
                            $cLaur[$counter] = "#00ff00";
                        } else {
                            $eLaur[$counter] = "Errore";
                            $cLaur[$counter] = "#ff0000";
                            $causa[$counter]["Bonus-media"] = "Bonus e/o media esami informatici non corrispondenti";
                        }
                    } else {
                        $eLaur[$counter] = "Corretto";
                        $cLaur[$counter] = "#00ff00";
                    }
                } else {
                    $eLaur[$counter] = "Errore";
                    $cLaur[$counter] = "#ff0000";
                    $causa[$counter]["Bonus-media"] = "Dati calcolati non corrispondenti";
                }
            }
            if (!isset($causa[$counter]["Bonus-media"])) {
                $causa[$counter]["Bonus-media"] = "Niente da segnalare";
            }

            // test ProspettoPDFCommissione
            if (is_null($testCreazioneLaureando->prospetto->getStudente()->getAnagrafica())) {
                $eSim[$counter] = "Errore";
                $cSim[$counter] = "#ff0000";
            }
            $testCreazioneCommissione = new ProspettoPDFCommissione(
                $testCreazioneLaureando->prospetto->getStudente(),
                (($eLaur[$counter] !== "Errore") ? $testCreazioneLaureando->prospetto->getMaxY() : 0),
                "test"
            );
            $testPDF = $testCreazioneCommissione->generaProspettoPDFCommissione();
            if (is_null($testPDF)) {
                $eSim[$counter] = "Errore";
                $cSim[$counter] = "#ff0000";
                $causa[$counter]["simulazioni"] = "Prospetto con simulazioni non creato";
            } else {
                $eSim[$counter] = "Corretto";
                $cSim[$counter] = "#00ff00";
            }
            if (!isset($causa[$counter]["simulazioni"])) {
                $causa[$counter]["simulazioni"] = "Niente da segnalare";
            }

            // test GestoreCreazioneProspetti
            $testConcatenamento = new GestoreCreazioneProspettiTest(
                $casoTest["matricola"],
                $casoTest["CdL"],
                "0001-01-01",
                "test"
            );
            $testConcatenamento->prelievoValori();
            $esitoC = $testConcatenamento->getEsito();
            if ($esitoC != "Niente da segnalare") {
                $eCom[$counter] = "Errore";
                $cCom[$counter] = "#ff0000";
                $causa[$counter]["finale"] = $esitoC;
            } else {
                $eCom[$counter] = "Corretto";
                $cCom[$counter] = "#00ff00";
                $causa[$counter]["finale"] = "Niente da segnalare";
            }
            $counter++;
        }

        $this->setUp($this->input, $eStud, $cStud, $eLaur, $cLaur, $eSim, $cSim, $eCom, $cCom, $causa);
    }

    private function setUp($input, $eStud, $cStud, $eLaur, $cLaur, $eSim, $cSim, $eCom, $cCom, $causa): void
    {
        echo '<!DOCTYPE html>
                <html lang="it">
                    <head>
                        <meta charset="UTF-8">
                        <title>Laureandosi2 - test</title>
                        <style>
                        	* {
                                box-sizing: border-box;
                              }

                              html.open, body.open {
                                height: 100%;
                                overflow: hidden;
                              }

                              html {
                                padding: 40px;
                                font-size: 62.5%;
                              }

                              body {
                                padding: 0 20px 20px;
                                background-color: #dde5f4;
                                line-height: 1.6;
                                -webkit-font-smoothing: antialiased;
                                color: #fff;
                                font-size: 1.6rem;
                                font-family: "Lato", sans-serif;
                              }
                              .titolo{
                                  font-size: 3rem;
                                  color: #10318a;
                              }

                              p {
                                text-align: center;
                                margin: 20px 0 60px;
                              }

                              main {
                                background-color: #214c77;
                              }

                              h1 {
                                text-align: center;
                                font-weight: 300;
                              }

                              table {
                                display: block;
                                margin-top: 2%;
                              }

                              tr, td, tbody, tfoot {
                                display: block;
                              }

                              thead {
                                display: none;
                              }

                              tr {
                                padding-bottom: 10px;
                              }

                              td {
                                padding: 10px 10px 0;
                                text-align: center;
                              }
                              td:before {
                                content: attr(data-title);
                                color: #7a91aa;
                                text-transform: uppercase;
                                font-size: 1.4rem;
                                padding-right: 10px;
                                display: block;
                              }

                              table {
                                width: 100%;
                              }

                              th {
                                text-align: left;
                                font-weight: 700;
                              }

                              thead th {
                                background-color: #5199e1;
                                color: #fff;
                                border: 1px solid #5199e1;
                              }

                              tfoot th {
                                display: block;
                                padding: 10px;
                                text-align: center;
                                color: #fff;
                                background-color: #5199e1;
                              }

                              .button {
                                line-height: 1;
                                display: flex;
                                justify-content: center;
                                align-items: center;
                                font-size: 1.5rem;
                                text-decoration: underline;
                                padding: 8px;
                                border: 1px white;
                              }

                              .select {
                                padding-bottom: 20px;
                                border-bottom: 1px solid #5199e1;
                              }
                              .select:before {
                                display: none;
                              }
                              .lista{
                                padding-top: 2%;
                                color: #0c0c0c;
                              }
                              .lista p{
                                margin: 20px;
                                text-align: left;
                                font-weight: bold;
                              }
                              ul{
                                padding-bottom: 1%;
                              }
                              li{
                                font-size: 1.4rem;
                              }
                              footer{
                                position: fixed;
                                right: 3%;
                                bottom: 3%;
                                width: 100%;
                                justify-content: end;
                                display: flex;
                                align-items: baseline;
                                
                              }
                              button{
                                    padding: 1em;
                                    margin-bottom: 10px;
                                    background: rgb(58, 129, 197);
                                    color: white;
                                    border: 3px solid;
                                    border-radius: 10px;
                                    font-weight: 600;
                                    width: max-content;
                                    font-size: 1.5rem;
                                    cursor: pointer;
                                }

                              @media (min-width: 460px) {
                                td {
                                  text-align: left;
                                }
                                td:before {
                                  display: inline-block;
                                  text-align: right;
                                  width: 140px;
                                }

                                .select {
                                  padding-left: 160px;
                                }
                              }
                              @media (min-width: 720px) {
                                table {
                                  display: table;
                                }

                                tr {
                                  display: table-row;
                                }

                                td, th {
                                  display: table-cell;
                                }

                                tbody {
                                  display: table-row-group;
                                }

                                thead {
                                  display: table-header-group;
                                }

                                tfoot {
                                  display: table-footer-group;
                                }

                                td {
                                  border: 1px solid #dde5f4;
                                }
                                td:before {
                                  display: none;
                                }

                                td, th {
                                  padding: 10px;
                                }

                                tr:nth-child(2n+2) td {
                                  background-color: #3a81c5;
                                }

                                tfoot th {
                                  display: table-cell;
                                }

                                .select {
                                  padding: 10px;
                                }
                              }
                        </style>
                    </head>
                    <body>
                        <h1 style="color: #10318a" class="titolo">Esito dei test</h1>
                        <main>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Operazione testata</th>
                                        <th>Classe coinvolta</th>
                                        <th>Matr. ' . $input["laureando0"]["matricola"] . '</th>
                                        <th>Matr. ' . $input["laureando1"]["matricola"] . '</th>
                                        <th>Matr. ' . $input["laureando2"]["matricola"] . '</th>
                                        <th>Matr. ' . $input["laureando3"]["matricola"] . '</th>
                                        <th>Matr. ' . $input["laureando4"]["matricola"] . '</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th colspan="7"></th>
                                    </tr>
                                </tfoot>
                                <tbody>
                                    <tr>
                                        <td data-title="Operazione">Recupero informazioni studente</td>
                                        <td data-title="Classe">GestioneCarrieraStudente</td>
                                        <td data-title="Esito"><a class="button" style="color: ' . $cStud[0] . '">' . $eStud[0] . '</a></td>
                                        <td data-title="Esito"><a class="button" style="color: ' . $cStud[1] . '">' . $eStud[1] . '</a></td>
                                        <td data-title="Esito"><a class="button" style="color: ' . $cStud[2] . '">' . $eStud[2] . '</a></td>
                                        <td data-title="Esito"><a class="button" style="color: ' . $cStud[3] . '">' . $eStud[3] . '</a></td>
                                        <td data-title="Esito"><a class="button" style="color: ' . $cStud[4] . '">' . $eStud[4] . '</a></td>
                                    </tr>
                                    <tr>

                                        <td data-title="Operazione">Creazione prospetto Laureando</td>
                                        <td data-title="Classe">ProspettoPDFLaureando</td>
                                        <td data-title="Esito"><a class="button" style="color: ' . $cLaur[0] . '">' . $eLaur[0] . '</a></td>
                                        <td data-title="Esito"><a class="button" style="color: ' . $cLaur[1] . '">' . $eLaur[1] . '</a></td>
                                        <td data-title="Esito"><a class="button" style="color: ' . $cLaur[2] . '">' . $eLaur[2] . '</a></td>
                                        <td data-title="Esito"><a class="button" style="color: ' . $cLaur[3] . '">' . $eLaur[3] . '</a></td>
                                        <td data-title="Esito"><a class="button" style="color: ' . $cLaur[4] . '">' . $eLaur[4] . '</a></td>
                                    </tr>
                                    <tr>
                                        <td data-title="Operazione">Creazione prospetto con simulazioni</td>
                                        <td data-title="Classe">ProspettoPDFCommissione</td>
                                        <td data-title="Esito"><a class="button" style="color: ' . $cSim[0] . '">' . $eSim[0] . '</a></td>
                                        <td data-title="Esito"><a class="button" style="color: ' . $cSim[1] . '">' . $eSim[1] . '</a></td>
                                        <td data-title="Esito"><a class="button" style="color: ' . $cSim[2] . '">' . $eSim[2] . '</a></td>
                                        <td data-title="Esito"><a class="button" style="color: ' . $cSim[3] . '">' . $eSim[3] . '</a></td>
                                        <td data-title="Esito"><a class="button" style="color: ' . $cSim[4] . '">' . $eSim[4] . '</a></td>
                                    </tr>
                                    <tr>
                                        <td data-title="Operazione">Creazione prospetto Commissione</td>
                                        <td data-title="Classe">GestoreCreazioneProspetti</td>
                                        <td data-title="Esito"><a class="button" style="color: ' . $cCom[0] . '">' . $eCom[0] . '</a></td>
                                        <td data-title="Esito"><a class="button" style="color: ' . $cCom[1] . '">' . $eCom[1] . '</a></td>
                                        <td data-title="Esito"><a class="button" style="color: ' . $cCom[2] . '">' . $eCom[2] . '</a></td>
                                        <td data-title="Esito"><a class="button" style="color: ' . $cCom[3] . '">' . $eCom[3] . '</a></td>
                                        <td data-title="Esito"><a class="button" style="color: ' . $cCom[4] . '">' . $eCom[4] . '</a></td>
                                    </tr>                     
                                </tbody>
                            </table>
                        </main>
                        <div class="lista">
                            <p>Errori in dettaglio</p>
                            <ul>
                                <li>Matricola: 123456 (CdL: T. Ing. Informatica)</li>
                                <ul>
                                    <li style="color: ' . (($causa[0]["GCS"] != "Niente da segnalare") ? "#ff0000" : "") . '">Test GestioneCarrieraStudente: ' . $causa[0]["GCS"] . '</li>
                                    <li style="color: ' . (($causa[0]["Bonus-media"] != "Niente da segnalare") ? "#ff0000" : "") . '">Test ProspettoPDFLaureando: ' . $causa[0]["Bonus-media"] . '</li>
                                    <li style="color: ' . (($causa[0]["simulazioni"] != "Niente da segnalare") ? "#ff0000" : "") . '">Test ProspettoPDFLaureando: ' . $causa[0]["simulazioni"] . '</li>
                                    <li style="color: ' . (($causa[0]["finale"] != "Niente da segnalare") ? "#ff0000" : "") . '">Test GestoreCreazioneProspetti: ' . $causa[0]["finale"] . '</li>
                                </ul>
                                <li>Matricola: 234567</li>
                                <ul>
                                    <li style="color: ' . (($causa[1]["GCS"] != "Niente da segnalare") ? "#ff0000" : "") . '">Test GestioneCarrieraStudente: ' . $causa[1]["GCS"] . '</li>
                                    <li style="color: ' . (($causa[1]["Bonus-media"] != "Niente da segnalare") ? "#ff0000" : "") . '">Test ProspettoPDFLaureando: ' . $causa[1]["Bonus-media"] . '</li>
                                    <li style="color: ' . (($causa[1]["simulazioni"] != "Niente da segnalare") ? "#ff0000" : "") . '">Test ProspettoPDFCommissione: ' . $causa[1]["simulazioni"] . '</li>
                                    <li style="color: ' . (($causa[1]["finale"] != "Niente da segnalare") ? "#ff0000" : "") . '">Test GestoreCreazioneProspetti: ' . $causa[1]["finale"] . '</li>
                                </ul>
                                <li>Matricola: 345678 (CdL: T. Ing. Informatica)</li>
                                <ul>
                                    <li style="color: ' . (($causa[2]["GCS"] != "Niente da segnalare") ? "#ff0000" : "") . '">Test GestioneCarrieraStudente: ' . $causa[2]["GCS"] . '</li>
                                    <li style="color: ' . (($causa[2]["Bonus-media"] != "Niente da segnalare") ? "#ff0000" : "") . '">Test ProspettoPDFLaureando: ' . $causa[2]["Bonus-media"] . '</li>
                                    <li style="color: ' . (($causa[2]["simulazioni"] != "Niente da segnalare") ? "#ff0000" : "") . '">Test ProspettoPDFCommissione: ' . $causa[2]["simulazioni"] . '</li>
                                    <li style="color: ' . (($causa[2]["finale"] != "Niente da segnalare") ? "#ff0000" : "") . '">Test GestoreCreazioneProspetti: ' . $causa[2]["finale"] . '</li>
                                </ul>
                                <li>Matricola: 456789</li>
                                <ul>
                                    <li style="color: ' . (($causa[3]["GCS"] != "Niente da segnalare") ? "#ff0000" : "") . '">Test GestioneCarrieraStudente: ' . $causa[3]["GCS"] . '</li>
                                    <li style="color: ' . (($causa[3]["Bonus-media"] != "Niente da segnalare") ? "#ff0000" : "") . '">Test ProspettoPDFLaureando: ' . $causa[3]["Bonus-media"] . '</li>
                                    <li style="color: ' . (($causa[3]["simulazioni"] != "Niente da segnalare") ? "#ff0000" : "") . '">Test ProspettoPDFCommissione: ' . $causa[3]["simulazioni"] . '</li>
                                    <li style="color: ' . (($causa[3]["finale"] != "Niente da segnalare") ? "#ff0000" : "") . '">Test GestoreCreazioneProspetti: ' . $causa[3]["finale"] . '</li>
                                </ul>
                                <li>Matricola: 111111</li>
                                <ul>
                                    <li style="color: ' . (($causa[4]["GCS"] != "Niente da segnalare") ? "#ff0000" : "") . '">Test GestioneCarrieraStudente: ' . $causa[4]["GCS"] . '</li>
                                    <li style="color: ' . (($causa[4]["Bonus-media"] != "Niente da segnalare") ? "#ff0000" : "") . '">Test ProspettoPDFLaureando: ' . $causa[4]["Bonus-media"] . '</li>
                                    <li style="color: ' . (($causa[4]["simulazioni"] != "Niente da segnalare") ? "#ff0000" : "") . '">Test ProspettoPDFCommissione: ' . $causa[4]["simulazioni"] . '</li>
                                    <li style="color: ' . (($causa[4]["finale"] != "Niente da segnalare") ? "#ff0000" : "") . '">Test GestoreCreazioneProspetti: ' . $causa[4]["finale"] . '</li>
                                </ul>
                            </ul>
                        </div>
                        <footer>
                            <button class="login" type="button" onclick="window.open(' . "'/principale', '_self'" . ')">Pagina principale</button>
                        </footer>
                    </body>
                </html>';
    }

    private function preSave($corto): void
    {
        if (!$this->controllaDirectory($corto)) {
            mkdir(dirname(__FILE__) . "/../prospetti/" . $corto);
        }
    }

    private function controllaDirectory($corto): bool
    {
        if (!is_dir(dirname(__FILE__) . "/../prospetti/" . $corto)) {
            return false;
        }
        return true;
    }
}