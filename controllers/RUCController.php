<?php
declare(strict_types=1);

namespace Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Controllers\utils\Endpoints;
use Controllers\utils\ExtraerContenido;
use Controllers\utils\FormData;

class RucController
{
    private $client;
    private $extraerContenido;
    private $data;

    public function __construct()
    {
        $this->client = new Client();
        $this->extraerContenido = new ExtraerContenido();
        $this->data = new FormData();
    }

    public function getRUC($nEnviado)
    {
        // Crear una instancia de CookieJar para almacenar cookies
        $cookieJar = new CookieJar();

        $controladorMensaje = [
            'base_uri' => Endpoints::CONSULT,
            'headers' => [
                'Host' => Endpoints::HOSTURL,
                'sec-ch-ua' => '" Not A;Brand";v="99", "Chromium";v="90", "Google Chrome";v="90"',
                'sec-ch-ua-mobile' => '?0',
                'Sec-Fetch-Dest' => 'document',
                'Sec-Fetch-Mode' => 'navigate',
                'Sec-Fetch-Site' => 'none',
                'Sec-Fetch-User' => '?1',
                'Upgrade-Insecure-Requests' => '1',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.150 Safari/537.36',
            ],
            'cookies' => $cookieJar,
        ];

        $this->client = new Client($controladorMensaje);

        // Delay
        usleep(100000);

        try {
            $response = $this->client->get(Endpoints::CONSULT);

            usleep(100000);

            // Remove header
            unset($controladorMensaje['headers']['Sec-Fetch-Site']);

            // Add additional headers
            $controladorMensaje['headers']['Origin'] = Endpoints::HOSTURL;
            $controladorMensaje['headers']['Referer'] = Endpoints::CONSULT;
            $controladorMensaje['headers']['Sec-Fetch-Site'] = 'same-origin';

            $numeroDNI = '12345678'; // cualquier número DNI que exista en SUNAT
            $formData = $this->data->getFormInitial($numeroDNI);

            $response = $this->client->post(Endpoints::CONSULT, [
                'form_params' => $formData,
                'cookies' => $cookieJar, // Asignar la CookieJar a la solicitud POST
            ]);

            usleep(100000);
            $contenidoHTML = $response->getBody()->getContents();
            $numeroRandom = $this->extraerContenido->contenidoEntreStrings($contenidoHTML, 0, 'name="numRnd" value="', '">');

            $formData = [
                'accion' => 'consPorRuc',
                'actReturn' => '1',
                'nroRuc' => $nEnviado,
                'numRnd' => $numeroRandom,
                'modo' => '1',
            ];

            $cConsulta = 0;
            $nConsulta = 3;
            $codigoEstado = 401;

            while ($cConsulta < $nConsulta && $codigoEstado == 401) {
                $response = $this->client->post(Endpoints::CONSULT, ['form_params' => $formData]);
                $codigoEstado = $response->getStatusCode();

                if ($response->getStatusCode() == 200) {
                    $contenidoHTML = $response->getBody()->getContents();
                    $contenidoHTML = html_entity_decode($contenidoHTML);

                    // parsear el html
                    $html = new \simple_html_dom();
                    $html->load($contenidoHTML);

                    // Extrae la información deseada
                    $nombreComercial = trim($html->find('.list-group-item p', 1)->plaintext);
                    $domicilioFiscal = $html->find('.list-group-item p', 6)->plaintext;
                    $domicilioFiscal = utf8_encode($domicilioFiscal);

                    // Dividir la dirección por "-"
                    $partesDireccion = explode('-', $domicilioFiscal, 3);

                    // Actualizar el valor de la dirección con el primer elemento
                    $direccion = trim($partesDireccion[0]);

                    $palabras = explode(' ', $direccion);
                    $departamento = end($palabras);
                    if ($departamento == 'DIOS') {
                        $departamento = 'MADRE DE DIOS';
                    }

                    if ($departamento == 'LIBERTAD') {
                        $departamento = 'LA LIBERTAD';
                    }

                    if ($departamento == 'MARTIN') {
                        $departamento = 'SAN MARTIN';
                    }

                    array_pop($palabras); // Elimina la última palabra (LIMA)
                    $direccion = implode(' ', $palabras);

                    // Eliminar palabras no deseadas al final de la dirección
                    $palabrasNoDeseadas = array(" LA", " MADRE DE", " SAN");
                    $direccion = rtrim(str_replace($palabrasNoDeseadas, "", $direccion));


                    $dataRuc = [
                        'ruc' => $nEnviado,
                        'nombre_comercial' => $nombreComercial,
                        'domicilio_fiscal' => $direccion,
                    ];

                    echo json_encode($dataRuc);


                } else {
                    $mensajeRespuesta = $response->getBody()->getContents();
                    $data = [
                        'success' => false,
                        'data' => [],
                        'error' => "Ocurrió un inconveniente al consultar los datos del RUC {$nEnviado}. Detalle: {$mensajeRespuesta}"
                    ];

                    echo json_encode($data);
                }

                $cConsulta++;
            }
        } catch (\Exception $e) {
            // Capturas cualquier excepción que se lance y respondes con un mensaje de error.
            $data = [
                'success' => false,
                'data' => [],
                // 'error' => $e->getMessage(),
            ];

            echo json_encode($data);
        }
    }
}