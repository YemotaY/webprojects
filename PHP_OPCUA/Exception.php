<?php

namespace OPCUA;

class OPCUAException extends \Exception
{
    // Konstruktor der übergeordneten Exception-Klasse aufrufen
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        // Standardwerte übergeben, wenn keine spezifischen Werte gesetzt werden
        parent::__construct($message, $code, $previous);
    }

    // Optionale Methode, um benutzerdefinierte Fehlernachricht zu formatieren
    public function customErrorMessage()
    {
        return "Fehler: [{$this->getCode()}] {$this->getMessage()} in {$this->getFile()} auf Zeile {$this->getLine()}";
    }
}
