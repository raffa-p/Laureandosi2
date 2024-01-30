<?php

require_once(dirname(__FILE__) . "/CdL.php");

class ApriProspetti
{

    /**
     * Apre il prospetto per la commissione contenente tutti i prospetti con le simulazioni.
     * Apre l'ultimo creato.
     * @param $CdL
     * @param $data
     */
    public static function apriProspetti($CdL, $data): void
    {
        if ($CdL == "" || $data == "") {
            http_response_code(203);
            return;
        }
        $filename = "../prospetti/" . CdL::getCdLcorto($CdL) . "/" . $CdL . "_" . $data . ".pdf";
        if (!file_exists($filename)) {
            http_response_code(502);
            return;
        }
        header('Location: ' . $filename);
    }
}