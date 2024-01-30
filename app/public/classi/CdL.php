<?php

class CdL
{
    public static function getCdLcorto($CdL): string
    {
        $lista = self::impostazioniCdL();
        return $lista[$CdL]["nomeCorto"];
    }

    public static function impostazioniCdL(): array
    {
        return json_decode(
            file_get_contents(dirname(__FILE__) . '/../fileDiConfigurazione/CdL-impostazioni.json'),
            true
        );
    }

    public static function creditiPrevisti($CdL): string
    {
        $ARRAYdocument = self::impostazioniCdL();
        if (isset($ARRAYdocument[$CdL])) {
            return $ARRAYdocument[$CdL]["CFUCurriculari"];
        }
        return "";
    }

    /**
     * Preleva la formula per il calcolo del voto di laurea.
     * @param $CdL
     * @return string
     */
    public static function trovaFormula($CdL): string
    {
        $ARRAYdocument = self::impostazioniCdL();
        if (isset($ARRAYdocument[$CdL])) {
            return $ARRAYdocument[$CdL]["formulaLaurea"];
        }
        return "";
    }

    /**
     * Preleva il messaggio da inserire alla fine del prospetto.
     * @param $CdL
     * @return string
     */
    public static function trovaMessaggio($CdL): string
    {
        $ARRAYdocument = self::impostazioniCdL();
        return $ARRAYdocument[$CdL]["MessaggioCommissione"];
    }

    public static function getParametri($CdL): array
    {
        $impostazioni = self::impostazioniCdL();
        $data["Tmin"] = $impostazioni[$CdL]["Tmin"];
        $data["Tmax"] = $impostazioni[$CdL]["Tmax"];
        $data["Tstep"] = $impostazioni[$CdL]["Tstep"];
        $data["Cmin"] = $impostazioni[$CdL]["Cmin"];
        $data["Cmax"] = $impostazioni[$CdL]["Cmax"];
        $data["Cstep"] = $impostazioni[$CdL]["Cstep"];

        return $data;
    }
}