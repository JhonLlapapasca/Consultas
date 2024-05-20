<?php
declare(strict_types=1);

namespace Controllers\utils;

class FormData
{
    public static function getFormInitial($numeroDNI)
    {
        return [
            'accion' => 'consPorTipdoc',
            'razSoc' => '',
            'nroRuc' => '',
            'nrodoc' => $numeroDNI,
            'contexto' => 'ti-it',
            'modo' => '1',
            'search1' => '',
            'rbtnTipo' => '2',
            'tipdoc' => '1',
            'search2' => $numeroDNI,
            'search3' => '',
            'codigo' => '',
        ];
    }
}