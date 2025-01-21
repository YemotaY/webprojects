function calculateSolarSavings_Basic(type, persons, roofType) {
    let baseSavings = 0; // Basiseinsparung in Euro
    let efficiencyFactor = 1; // Faktor für Dachtyp

    // 1. Basiseinsparung je nach Typ
    switch (type) {
        case 'Privat':
            baseSavings = 5000; // Private Anlagen -> kleine Einsparung
            break;
        case 'Gewerblich':
            baseSavings = 20000; // Gewerbliche Anlagen -> große Einsparung
            break;
        case 'Sonstiges':
            baseSavings = 2000; // Sonstige -> sehr geringe Einsparung
            break;
        default:
            throw new Error("Ungültiger Typ");
    }

    // 2. Bewohner-Multiplikator (mehr Bewohner -> höhere Einsparung)
    const personFactor = Math.max(1, persons * 0.5); // 0.5 pro Bewohner, mindestens 1

    // 3. Effizienz-Faktor je nach Dachtyp
    switch (roofType) {
        case 'Satteldach':
        case 'Pultdach':
            efficiencyFactor = 1.0; // Mittelmäßige Ausbeute
            break;
        case 'Flachdach':
            efficiencyFactor = 1.3; // Hohe Ausbeute
            break;
        case 'Sonstiges':
            efficiencyFactor = 0.7; // Niedrige Ausbeute
            break;
        default:
            throw new Error("Ungültiger Dachtyp");
    }

    // 4. Endgültige Berechnung der Einsparung
    const minSavings = Math.round(baseSavings * personFactor * efficiencyFactor);
    const maxSavings = Math.round(minSavings * 1.2); // Maximal 20% mehr als Minimum

    return {
        min: minSavings,
        max: maxSavings
    };
}


function calculateSolarSavings_Advanced(type, persons, roofType, location, solarCost, consumption) {
    let baseSavings = 0; // Basiseinsparung in Euro
    let efficiencyFactor = 1; // Faktor für Dachtyp
    let locationFactor = 1; // Faktor für den Standort
    let consumptionFactor = 1; // Faktor für den Stromverbrauch

    // 1. Basiseinsparung je nach Typ
    switch (type) {
        case 'Privat':
            baseSavings = 5000; // Private Anlagen -> kleine Einsparung
            break;
        case 'Gewerblich':
            baseSavings = 20000; // Gewerbliche Anlagen -> große Einsparung
            break;
        case 'Sonstiges':
            baseSavings = 2000; // Sonstige -> sehr geringe Einsparung
            break;
        default:
            throw new Error("Ungültiger Typ");
    }

    // 2. Bewohner-Multiplikator (mehr Bewohner -> höhere Einsparung)
    const personFactor = Math.max(1, persons * 0.5); // 0.5 pro Bewohner, mindestens 1

    // 3. Effizienz-Faktor je nach Dachtyp
    switch (roofType) {
        case 'Satteldach':
        case 'Pultdach':
            efficiencyFactor = 1.0; // Mittelmäßige Ausbeute
            break;
        case 'Flachdach':
            efficiencyFactor = 1.3; // Hohe Ausbeute
            break;
        case 'Sonstiges':
            efficiencyFactor = 0.7; // Niedrige Ausbeute
            break;
        default:
            throw new Error("Ungültiger Dachtyp");
    }

    // 4. Standort-Faktor: Abhängig von der Sonneneinstrahlung
    // Wir nehmen an, dass sonnigere Regionen bessere Ausbeuten haben.
    switch (location) {
        case 'Sonnenreich':
            locationFactor = 1.3; // Hohe Sonneneinstrahlung
            break;
        case 'Mittel':
            locationFactor = 1.0; // Durchschnittliche Sonneneinstrahlung
            break;
        case 'WenigSonne':
            locationFactor = 0.7; // Geringe Sonneneinstrahlung
            break;
        default:
            throw new Error("Ungültiger Standort");
    }


    const solarCostFactor = Math.max(1, 0.1 / solarCost); // Ein höherer Solarstrompreis reduziert die Ersparnis

    consumptionFactor = consumption / 5000; // Durchschnittlicher Verbrauch eines Haushalts pro Jahr (z. B. 5000 kWh)

    const minSavings = Math.round(baseSavings * personFactor * efficiencyFactor * locationFactor * solarCostFactor * consumptionFactor);
    const maxSavings = Math.round(minSavings * 1.2); // Maximal 20% mehr als Minimum

    return {
        min: minSavings,
        max: maxSavings
    };
}
