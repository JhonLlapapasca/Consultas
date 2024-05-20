<?php
declare(strict_types=1);

namespace Controllers\utils;

/**
 * Class Endpoints
 *
 * Esta clase define constantes que representan diferentes endpoints y URLs utilizadas en la aplicación.
 * Puedes acceder a estas constantes para realizar consultas a la SUNAT y la SBS, así como para obtener información relacionada con el tipo de cambio y consultas por DNI a la SUNAT.
 */
final class Endpoints
{
    /**
     * URL base para consultas a la SUNAT.
     */
    public const HOSTURL = 'https://e-consultaruc.sunat.gob.pe';

    /**
     * URL para la consulta principal a la SUNAT.
     */
    public const CONSULT = 'https://e-consultaruc.sunat.gob.pe/cl-ti-itmrconsruc/jcrS00Alias';

    /**
     * URL para consultar en la pagina eldni.
     */
    public const EL_DNI = 'https://eldni.com/pe/buscar-datos-por-dni';

     /**
     * URL host para eldni.
     */
    public const HOST_EL_DNI = 'https://eldni.com';
}
