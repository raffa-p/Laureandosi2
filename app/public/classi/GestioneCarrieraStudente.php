<?php

require_once(dirname(__FILE__) . '/Esame.php');

class GestioneCarrieraStudente
{

    /**
     * Restituisce un array con le informazioni anagrafiche del laureando
     * @param $matricola
     * @return array|null
     */
    public static function restituisciAnagraficaStudente($matricola): ?array
    {
        try {
            $JSONdocument = file_get_contents(
                dirname(__FILE__) . "/../studenti_json/" . $matricola . "_anagrafica.json"
            );
            if ($JSONdocument == null) {
                throw new Exception("Laureando/i inesistente/i");
            }
        } catch (Exception) {
            return null;
        }
        $ARRAYdocument = json_decode($JSONdocument, true);
        return array(
            "Nome" => $ARRAYdocument['Entries']['Entry']['nome'],
            "Cognome" => $ARRAYdocument['Entries']['Entry']['cognome'],
            "CF" => $ARRAYdocument['Entries']['Entry']['cod_fis'],
            "DataNascita" => $ARRAYdocument['Entries']['Entry']['data_nascita'],
            "email" => $ARRAYdocument['Entries']['Entry']['email_ate']
        );
    }

    /**
     * Ritorna un array contenente gli esami del laureando.
     * Ogni esame è un array a sua volta.
     * @param $matricola
     * @return array|null
     */
    public static function restituisciCarrieraStudente($matricola): ?array
    {
        try {
            $JSONdocument = file_get_contents(dirname(__FILE__) . "/../studenti_json/" . $matricola . "_esami.json");
            if ($JSONdocument == null) {
                throw new Exception("Laureando/i inesistente/i");
            }
        } catch (Exception) {
            return null;
        }
        return json_decode($JSONdocument, true);
    }
}