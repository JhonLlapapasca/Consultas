<?php
namespace Controllers\utils;

class ExtraerContenido
{

    public function contenidoEntreStrings($contenido, $indiceInicio, $tagInicio, $tagFin)
    {
        // Encontrar la posición del tag de inicio
        $posicionInicio = strpos($contenido, $tagInicio, $indiceInicio);

        if ($posicionInicio !== false) {
            // Avanzar a la posición después del tag de inicio
            $posicionInicio += strlen($tagInicio);

            // Encontrar la posición del tag de fin
            $posicionFin = strpos($contenido, $tagFin, $posicionInicio);

            if ($posicionFin !== false) {
                // Extraer el contenido entre los tags
                $contenidoExtraido = substr($contenido, $posicionInicio, $posicionFin - $posicionInicio);

                return $contenidoExtraido;
            }
        }

        return null;
    }
}