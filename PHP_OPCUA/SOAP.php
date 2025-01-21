<?php

namespace OPCUA;
class SoapClient
{
    private $discoveryUrl;
    private $cacertPath;

    public function __construct($discoveryUrl, $cacertPath)
    {
        $this->discoveryUrl = $discoveryUrl;
        $this->cacertPath = $cacertPath;
    }

    public function sendSoapRequest($xml, $headers)
    {
        // Setze die Optionen für den SoapClient
        $options = [
            'location' => $this->discoveryUrl,
            'uri' => 'urn:schemas-xmlsoap-org:soap',  // Dies sollte mit der Webservice URI übereinstimmen
            'trace' => 1, // Ermöglicht das Nachverfolgen von SOAP-Anfragen für Debugging
            'exceptions' => 1, // Wirft Ausnahmen bei Fehlern
            'connection_timeout' => 10, // Verbindungstimeout in Sekunden
            'stream_context' => stream_context_create([
                'ssl' => [
                    'cafile' => $this->cacertPath,
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ]
            ]),
            'headers' => $headers, // Header für die Anfrage
        ];

        try {
            // Erstelle den SoapClient
            $client = new SoapClient(null, $options);

            // Führe die SOAP-Anfrage aus und erhalte die Antwort
            $response = $client->__doRequest($xml, $this->discoveryUrl, '', SOAP_1_2);

            return $response;

        } catch (SoapFault $fault) {
            // Fehlerbehandlung bei SOAP-Anfragen
            echo 'SOAP Fehler: ' . $fault->getMessage();
        }
    }
}
?>
