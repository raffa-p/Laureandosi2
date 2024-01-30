<?php

class FiltroEsami
{
    public static function getEsamiNonAvg($CdL): array
    {
        return self::filtroEsami()[$CdL]["esami-non-avg"];
    }

    public static function filtroEsami(): array
    {
        return json_decode(file_get_contents(dirname(__FILE__) . '/../fileDiConfigurazione/filtro-esami.json'), true);
    }

    public static function getEsamiNonCdL($CdL): array
    {
        return self::filtroEsami()[$CdL]["esami-non-CdL"];
    }
}