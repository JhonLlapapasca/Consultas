<?php
declare(strict_types=1);

namespace Controllers;

use GuzzleHttp\Cookie\CookieJar;
use Controllers\utils\Endpoints;
use GuzzleHttp\Client;
use Controllers\utils\ExtraerContenido;

class ElDniController
{
    private $client;
    private $extraerContenido;

    public function __construct()
    {
        $this->client = new Client();
        $this->extraerContenido = new ExtraerContenido();
        $cookieJar = new CookieJar();
        $this->client = new Client(['cookies' => $cookieJar]);
    }
    public function elDni($dni)
    {
        // Crear una instancia de CookieJar para almacenar cookies
        $cookieJar = new CookieJar();
        $controladorMensaje = [
            'base_uri' => Endpoints::EL_DNI,
            'headers' => [
                'Host' => Endpoints::HOST_EL_DNI,
                'sec-ch-ua' => '" Not A;Brand";v="99", "Chromium";v="90", "Google Chrome";v="90"',
                'sec-ch-ua-mobile' => '?0',
                'Sec-Fetch-Dest' => 'document',
                'Sec-Fetch-Mode' => 'navigate',
                'Sec-Fetch-Site' => 'none',
                'Sec-Fetch-User' => '?1',
                'acept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Upgrade-Insecure-Requests' => '1',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.150 Safari/537.36',
            ],
            'cookies' => $cookieJar,
        ];

        // Delay
        usleep(100000);

        try {
            $this->validarDni($dni);

            $response = $this->client->get(Endpoints::EL_DNI);
            usleep(200000);

            // Remove header
            unset($controladorMensaje['headers']['Sec-Fetch-Site']);

            // Add additional headers
            $controladorMensaje['headers']['Origin'] = Endpoints::EL_DNI;
            $controladorMensaje['headers']['Referer'] = Endpoints::HOST_EL_DNI;
            $controladorMensaje['headers']['Sec-Fetch-Site'] = 'same-origin';

            // Realizar la solicitud GET al servicio web
            $contenidoHTML = $this->client->get(Endpoints::EL_DNI)->getBody();
            $html = new \simple_html_dom();
            $html->load($contenidoHTML);

            $token = $this->extraerContenido->contenidoEntreStrings($contenidoHTML, 0, 'name="_token" value="', '">');

            $formData = [
                '_token' => $token,
                'dni' => $dni
            ];

            $response = $this->client->post(Endpoints::EL_DNI, ['form_params' => $formData]);
            $codigoEstado = $response->getStatusCode();

            if ($codigoEstado == 200) {
                $contenidoHTML = $response->getBody()->getContents();
                $contenidoHTML = html_entity_decode($contenidoHTML);

                // parsear el html
                $html = new \simple_html_dom();
                $html->load($contenidoHTML);

                $nombreCompleto = $html->find('input[id="completos"]', 0);
                $nombreCompleto = $nombreCompleto->value;

                $dni = $html->find('input[id="dni_digito"]', 0);
                $dni = $dni->value;
                $dni = substr($dni, 0, 8);

                $dataDni = [
                    'dni' => $dni,
                    'nombre_completo' => $nombreCompleto,
                ];

                echo json_encode($dataDni);
            } else {
                echo "error";
            }


        } catch (\Exception $e) {
            $responseData = [
                'error' => $e->getMessage(),
            ];
            echo json_encode($responseData);
        }
    }

    private function validarDni($dni)
    {
        // Validar que el DNI sea numérico y tenga menos de 9 dígitos
        if (!is_numeric($dni) || strlen($dni) > 8) {
            throw new \Exception('DNI no válido');
        }
    }
}