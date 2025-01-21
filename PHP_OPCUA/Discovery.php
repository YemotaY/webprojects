<?php

namespace OPCUA;

class DiscoveryClient
{
    private $discoveryUrl;

    public function __construct($discoveryUrl)
    {
        $this->discoveryUrl = $discoveryUrl;
    }

    /**
     * FindServers - Findet OPC UA-Server auf dem angegebenen Discovery-Server
     * 
     * @param string $serverUri URI des Servers, um die Suche einzugrenzen (optional)
     * @param string $localeId Die bevorzugte Sprache/Region für Server (optional)
     * @return mixed Antwort des Discovery-Servers
     */
    public function findServers($serverUri = '', $localeId = 'en')
    {
        $headers = [
            'Content-Type: text/xml;charset=UTF-8',
            'SOAPAction: "http://opcfoundation.org/webservices/FindServers"'
        ];

        // Erstellen der SOAP-Nachricht für die FindServers-Anfrage
        $xml = $this->createFindServersRequest($serverUri, $localeId);

        // Senden der Anfrage
        return $this->sendSoapRequest($xml, $headers);
    }

    /**
     * GetEndpoints - Ruft die Endpunkte eines OPC UA-Servers ab
     * 
     * @param string $serverUrl URL des OPC UA Servers, dessen Endpunkte abgerufen werden sollen
     * @return mixed Antwort des Discovery-Servers
     */
    public function getEndpoints($serverUrl)
    {
        $headers = [
            'Content-Type: text/xml;charset=UTF-8',
            'SOAPAction: "http://opcfoundation.org/webservices/GetEndpoints"'
        ];

        // Erstellen der SOAP-Nachricht für die GetEndpoints-Anfrage
        $xml = $this->createGetEndpointsRequest($serverUrl);

        // Senden der Anfrage
        return $this->sendSoapRequest($xml, $headers);
    }

    /**
     * Sendet eine SOAP-Anfrage an den Discovery-Server
     * 
     * @param string $xml Die SOAP-Anfrage als XML
     * @param array $headers Die HTTP-Header für die Anfrage
     * @return mixed Antwort des Discovery-Servers
     */
    private function sendSoapRequest($xml, $headers)
    {
        // Setze die URL des Webservices
        $soapUrl = $this->discoveryUrl;
    
        // Erstelle einen neuen SOAP-Client
        $options = array(
            'location' => $soapUrl,
            'uri' => 'urn:schemas-xmlsoap-org:soap',  // Dies sollte mit der Webservice URI übereinstimmen
            'trace' => 1, // Ermöglicht das Nachverfolgen von SOAP-Anfragen für Debugging
            'exceptions' => 1, // Wirft Ausnahmen bei Fehlern
            'connection_timeout' => 10, // Verbindungstimeout in Sekunden
            'stream_context' => stream_context_create(array(
                'ssl' => array(
                    'cafile' => "C:/xamppnew/php/extras/ssl/cacert.pem",
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                )
            )),
            'headers' => $headers, // Hier fügst du die Header hinzu
        );
    
        try {
            // SOAP-Client erstellen
            $client = new SoapClient(null, $options);
    
            // Führe die SOAP-Anfrage aus und erhalte die Antwort
            $response = $client->__doRequest($xml, $soapUrl, '', SOAP_1_2);
            
            return $response;
    
        } catch (SoapFault $fault) {
            // Fehlerbehandlung bei SOAP-Anfragen
            echo 'SOAP Fehler: ' . $fault->getMessage();
        }
    }

    /**
     * Erstellt die SOAP-Anfrage für FindServers
     * 
     * @param string $serverUri Die URI des Servers (optional)
     * @param string $localeId Die bevorzugte Sprache (optional)
     * @return string XML der SOAP-Anfrage
     */
    private function createFindServersRequest($serverUri = '', $localeId = 'en')
    {
        $xml = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:web="http://opcfoundation.org/webservices/">
   <soapenv:Header/>
   <soapenv:Body>
      <web:FindServers>
         <web:RequestHeader>
            <web:AuthenticationToken xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>
         </web:RequestHeader>
         <web:ServerUris>
            <web:string>$serverUri</web:string>
         </web:ServerUris>
         <web:LocaleIds>
            <web:string>$localeId</web:string>
         </web:LocaleIds>
      </web:FindServers>
   </soapenv:Body>
</soapenv:Envelope>
XML;

        return $xml;
    }

    /**
     * Erstellt die SOAP-Anfrage für GetEndpoints
     * 
     * @param string $serverUrl Die URL des OPC UA Servers
     * @return string XML der SOAP-Anfrage
     */
    private function createGetEndpointsRequest($serverUrl)
    {
        $xml = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:web="http://opcfoundation.org/webservices/">
   <soapenv:Header/>
   <soapenv:Body>
      <web:GetEndpoints>
         <web:RequestHeader>
            <web:AuthenticationToken xsi:nil="true" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"/>
         </web:RequestHeader>
         <web:EndpointUrl>$serverUrl</web:EndpointUrl>
      </web:GetEndpoints>
   </soapenv:Body>
</soapenv:Envelope>
XML;

        return $xml;
    }
}

// Beispiel der Nutzung der Klasse:

$discoveryUrl = "https://opcua.demo-this.com:51212/UA/SampleServer";  // URL des Discovery-Servers
$client = new \OPCUA\DiscoveryClient($discoveryUrl);

// Finde OPC UA-Server
$servers = $client->findServers();
echo "Gefundene Server:\n";
echo $servers;  // Ausgabe der Antwort von FindServers

// Abrufen der Endpunkte eines bestimmten OPC UA Servers
$serverUrl = "opc.tcp://opcua.demo-this.com:51210/UA/SampleServer";  // Beispiel-Server-URL
$endpoints = $client->getEndpoints($serverUrl);
echo "Gefundene Endpunkte:\n";
echo $endpoints;  // Ausgabe der Antwort von GetEndpoints
?>