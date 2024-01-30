<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['Type'] === 'invio') {
    GestoreRichiesteInterfaccia::richiestaInvio($_POST["CdL"]);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['Type'] === 'crea') {
    GestoreRichiesteInterfaccia::richiestaCreazione($_POST["matricole"], $_POST["CdL"], $_POST["dataLaurea"]);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['Type'] === 'apri') {
    GestoreRichiesteInterfaccia::richiestaApertura($_POST["CdL"], $_POST["dataLaurea"]);
}

class GestoreRichiesteInterfaccia
{

    public static function richiestaInvio($CdL): void
    {
        require_once(dirname(__FILE__) . "/InvioProspetto.php");
        if (!file_exists(dirname(__FILE__) . '/../cache/' . $CdL . '_coda_prospetti.json')) {
            http_response_code(204);
        }
        $studenti = json_decode(
            file_get_contents(dirname(__FILE__) . '/../cache/' . $CdL . '_coda_prospetti.json'),
            true
        );
        if (count($studenti) <= $studenti[0]["numero"]) {
            unlink(dirname(__FILE__) . '/../cache/' . $CdL . '_coda_prospetti.json');
            echo "Invio completato";
            http_response_code(203);
        }
        $mail = new InvioProspetto(
            $studenti[($studenti[0]["numero"])]["email"],
            $studenti[($studenti[0]["numero"])]["Matricola"],
            $CdL
        );
        if (!$mail->inviaEmail()) {
            echo "Errore invio";
            http_response_code(502);
        } else {
            echo "Inviato: " . $studenti[0]["numero"] . "/" . (count($studenti) - 1);
            $studenti[0]["numero"] += 1;
            $studenti = json_encode($studenti, JSON_PRETTY_PRINT);
            file_put_contents(dirname(__FILE__) . '/../cache/' . $CdL . '_coda_prospetti.json', $studenti);
            http_response_code(202);
        }
    }

    public static function richiestaCreazione($matricole, $CdL, $data): void
    {
        require_once(dirname(__FILE__) . "/GestoreCreazioneProspetti.php");
        if ($matricole == "" || $CdL == "" || $data == "") {
            http_response_code(203);
        }
        try {
            $prospettiLaureandi = new GestoreCreazioneProspetti($matricole, $CdL, $data);
            if ($prospettiLaureandi->generaProspetti() != 00) {
                throw new Exception();
            }
            http_response_code(202);
        } catch (Exception) {
            error_log("Errore creazione prospetti");
            http_response_code(502);
        }
    }

    public static function richiestaApertura($CdL, $data): void
    {
        require_once(dirname(__FILE__) . "/ApriProspetti.php");
        ApriProspetti::apriProspetti($CdL, $data);
    }
}