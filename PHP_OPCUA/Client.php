<?php

namespace OPCUA;

class OPCUAClient
{
    private $communication;
    private $clientSocket;
    private $subscriptions = [];

    public function __construct(Communication $communication, $clientSocket)
    {
        $this->communication = $communication;
        $this->clientSocket = $clientSocket;
    }

    // --------------------------------------------
    // Subscription-Dienste (Event- und Variablenüberwachung)
    // --------------------------------------------

    /**
     * Abonniert eine Variable auf Änderungen und sendet eine Benachrichtigung
     */
    public function subscribeVariable($nodeId, $callback)
    {
        // Beispiel: Hier könnte ein realer Mechanismus zum Abonnieren von Variablen entwickelt werden.
        // Wir speichern die Subscription und rufen den Callback auf, wenn sich der Wert ändert.

        $this->subscriptions[$nodeId] = $callback;

        echo "Abonniert Variable: $nodeId\n";
    }

    /**
     * Benachrichtige alle Subscriptions für die Variable
     */
    public function notifySubscriptions($nodeId, $newValue)
    {
        if (isset($this->subscriptions[$nodeId])) {
            call_user_func($this->subscriptions[$nodeId], $newValue);
        } else {
            echo "Keine Subscription für Variable: $nodeId\n";
        }
    }

    /**
     * Abonniert ein Ereignis und registriert eine Benachrichtigung
     */
    public function subscribeEvent($eventType, $callback)
    {
        // Beispiel für das Abonnieren von Ereignissen.
        // Hier wird ein einfaches Event simuliert. In einer realen Anwendung könnte es eine Ereignisabfrage geben.

        $this->subscriptions[$eventType] = $callback;

        echo "Abonniert Ereignis: $eventType\n";
    }

    /**
     * Benachrichtige alle Subscriptions für ein Ereignis
     */
    public function notifyEvent($eventType, $eventData)
    {
        if (isset($this->subscriptions[$eventType])) {
            call_user_func($this->subscriptions[$eventType], $eventData);
        } else {
            echo "Keine Subscription für Ereignis: $eventType\n";
        }
    }

    // --------------------------------------------
    // Browse-Dienste (Adressraum und Knotenexploration)
    // --------------------------------------------

    /**
     * Durchsucht den Adressraum nach Knoten basierend auf einem bestimmten Filter
     */
    public function browse($nodeId)
    {
        // Beispiel: Diese Funktion simuliert das Browsen des Adressraums.
        // In einer echten Implementierung würde dies eine Anfrage an den OPC UA Server senden und die Knoten zurückgeben.
        
        echo "Durchsuche Adressraum nach Knoten: $nodeId\n";
        
        // Simulierter Rückgabewert für Knoten
        $nodes = [
            'ns=2;s=Temperature' => 'Temperature Sensor',
            'ns=2;s=Pressure' => 'Pressure Sensor'
        ];
        
        return $nodes;
    }

    /**
     * Gibt Details zu einem bestimmten Knoten basierend auf der NodeId zurück
     */
    public function getNodeDetails($nodeId)
    {
        // Beispiel: Diese Funktion simuliert das Abrufen von Knoteninformationen.
        // In einer echten Implementierung würde der Server Knoteninformationen zurückgeben.

        echo "Hole Details für Knoten: $nodeId\n";
        
        $nodeDetails = [
            'ns=2;s=Temperature' => ['Type' => 'Variable', 'DataType' => 'Double', 'Value' => '23.5°C'],
            'ns=2;s=Pressure' => ['Type' => 'Variable', 'DataType' => 'Double', 'Value' => '1.2 bar']
        ];

        return $nodeDetails[$nodeId] ?? null;
    }

    // --------------------------------------------
    // Lese- und Schreibdienste (bleiben unverändert)
    // --------------------------------------------

    private function sendRequest($message)
    {
        $encryptedMessage = $this->communication->encryptData($message);
        fwrite($this->clientSocket, $encryptedMessage);
        echo "Nachricht gesendet: $message\n";

        // Antwort vom Server empfangen
        $response = fread($this->clientSocket, 1024);
        return $this->communication->decryptData($response);
    }

    public function readVariable($nodeId)
    {
        $message = "READ $nodeId";
        $response = $this->sendRequest($message);

        echo "Wert der Variablen (NodeId $nodeId): $response\n";
        return $response;
    }

    public function writeVariable($nodeId, $newValue)
    {
        $message = "WRITE $nodeId $newValue";
        $response = $this->sendRequest($message);

        if ($response === "SUCCESS") {
            echo "Erfolgreich geschrieben: $newValue\n";
            return true;
        } else {
            echo "Fehler beim Schreiben: $response\n";
            return false;
        }
    }

    public function createSession($clientId)
    {
        $sessionId = $this->communication->createSession($clientId);
        echo "Neue Session erstellt: $sessionId\n";
        return $sessionId;
    }

    public function validateSession($clientId, $sessionId)
    {
        $valid = $this->communication->validateSession($clientId, $sessionId);
        echo $valid ? "Session validiert.\n" : "Session ungültig.\n";
        return $valid;
    }
}
