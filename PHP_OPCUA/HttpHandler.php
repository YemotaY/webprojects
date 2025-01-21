<?php

class HttpHandler
{
    // Empfängt die HTTP-Methode (GET, POST, etc.)
    public function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    // Empfängt die Header der Anfrage
    public function getHeaders()
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headerKey = str_replace('_', '-', substr($key, 5));
                $headers[$headerKey] = $value;
            }
        }
        return $headers;
    }

    // Empfängt die URL-Parameter (z. B. ?key=value)
    public function getQueryParams()
    {
        return $_GET;
    }

    // Empfängt die Daten aus dem Anfragekörper
    public function getBody()
    {
        return file_get_contents('php://input');
    }

    // Empfängt JSON-Daten aus dem Anfragekörper
    public function getJsonBody()
    {
        $rawBody = $this->getBody();
        return json_decode($rawBody, true);
    }

    // Sendet eine HTTP-Antwort
    public function sendResponse($statusCode, $data = null, $headers = [])
    {
        // Statuscode setzen
        http_response_code($statusCode);

        // Zusätzliche Header setzen
        foreach ($headers as $key => $value) {
            header("$key: $value");
        }

        // Antwortdaten senden
        if ($data !== null) {
            if (is_array($data) || is_object($data)) {
                header('Content-Type: application/json');
                echo json_encode($data);
            } else {
                echo $data;
            }
        }
    }

    // Sendet eine JSON-Antwort
    public function sendJsonResponse($statusCode, $data)
    {
        $this->sendResponse($statusCode, $data, ['Content-Type' => 'application/json']);
    }
}

// Beispiel zur Nutzung der Klasse
$handler = new HttpHandler();

// Anfrage verarbeiten
if ($handler->getMethod() === 'POST') {
    $jsonData = $handler->getJsonBody();
    if ($jsonData) {
        $handler->sendJsonResponse(200, ['success' => true, 'data' => $jsonData]);
    } else {
        $handler->sendJsonResponse(400, ['error' => 'Invalid JSON']);
    }
} else {
    $handler->sendJsonResponse(405, ['error' => 'Method not allowed']);
}