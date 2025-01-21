<?php

class HttpRequest
{
    /**
     * Sendet eine HTTP/HTTPS-Anfrage.
     *
     * @param string $url Die URL der Anfrage.
     * @param string $method Die HTTP-Methode (z. B. GET, POST).
     * @param array $headers Optional: Zusätzliche Header als Array.
     * @param string|array|null $body Optional: Der Anfragekörper (z. B. JSON-Daten).
     * @return array Enthält 'status', 'headers' und 'body'.
     */
    public function sendRequest($url, $method = 'GET', $headers = [], $body = null)
    {
        $method = strtoupper($method);

        // Anfrage-Header formatieren
        $formattedHeaders = [];
        foreach ($headers as $key => $value) {
            $formattedHeaders[] = "$key: $value";
        }

        // Body vorbereiten (falls notwendig)
        if (is_array($body) || is_object($body)) {
            $body = json_encode($body);
            $formattedHeaders[] = 'Content-Type: application/json';
        }

        // Stream-Kontext-Optionen erstellen
        $options = [
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $formattedHeaders),
                'content' => $body ?? '',
                'ignore_errors' => true, // Ermöglicht die Verarbeitung von HTTP-Fehlercodes (z. B. 404)
            ],
        ];

        // Stream-Kontext erstellen
        $context = stream_context_create($options);

        // Anfrage senden
        $response = @file_get_contents($url, false, $context);

        // Antwort analysieren
        $statusCode = null;
        $responseHeaders = [];
        if (isset($http_response_header)) {
            // Statuscode extrahieren
            if (preg_match('#HTTP/\d+\.\d+ (\d+)#', $http_response_header[0], $matches)) {
                $statusCode = (int)$matches[1];
            }

            // Header in ein Array umwandeln
            $responseHeaders = array_slice($http_response_header, 1);
        }

        return [
            'status' => $statusCode,
            'headers' => $responseHeaders,
            'body' => $response,
        ];
    }
}

// Beispiel zur Nutzung der Klasse
$http = new HttpRequest();

// GET-Anfrage senden
$response = $http->sendRequest('https://jsonplaceholder.typicode.com/posts/1');
echo "Status: " . $response['status'] . "\n";
echo "Antwort: " . $response['body'] . "\n";

// POST-Anfrage senden
$postData = ['title' => 'foo', 'body' => 'bar', 'userId' => 1];
$response = $http->sendRequest(
    'http://jsonplaceholder.typicode.com/posts',
    'POST',
    ['Accept' => 'application/json'],
    $postData
);
echo "Status: " . $response['status'] . "\n";
echo "Antwort: " . $response['body'] . "\n";