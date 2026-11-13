/**
 * Carbon Footprint Estimator for CargoTrace Logistics
 * Backed by GLEC (Global Logistics Emissions Council) framework standard emission factors.
 */

// Average emission factors in kg CO2 per metric tonne-kilometer (tkm)
const EMISSION_FACTORS = {
    SEA: 0.015,     // Container vessel average
    AIR: 0.500,     // Dedicated cargo freighter average
    LAND: 0.095,    // Heavy-duty road freight truck average
};

/**
 * Calculate Great-Circle Distance between two coordinates in kilometers (Haversine formula).
 */
export function calculateDistanceKm(lat1, lon1, lat2, lon2) {
    if (!lat1 || !lon1 || !lat2 || !lon2) return 1200; // default estimated maritime segment
    const R = 6371; // Earth radius in km
    const dLat = ((lat2 - lat1) * Math.PI) / 180;
    const dLon = ((lon2 - lon1) * Math.PI) / 180;
    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos((lat1 * Math.PI) / 180) *
            Math.cos((lat2 * Math.PI) / 180) *
            Math.sin(dLon / 2) *
            Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return Math.round(R * c);
}

/**
 * Estimate Carbon Footprint (kg CO2) for a given shipment.
 * @param {Object} shipment - Shipment object with carrier, locations, and weight.
 * @returns {Object} { distanceKm, emissionsKg, ecoRating, factorLabel }
 */
export function estimateCarbonFootprint(shipment) {
    if (!shipment) {
        return { distanceKm: 0, emissionsKg: 0, ecoRating: 'N/A', factorLabel: '-' };
    }

    const mode = (shipment.carrier?.transport_mode || 'SEA').toUpperCase();
    const factor = EMISSION_FACTORS[mode] || EMISSION_FACTORS.SEA;

    const orig = shipment.origin_location;
    const dest = shipment.destination_location;

    const distanceKm =
        orig?.latitude && dest?.latitude
            ? calculateDistanceKm(
                  parseFloat(orig.latitude),
                  parseFloat(orig.longitude),
                  parseFloat(dest.latitude),
                  parseFloat(dest.longitude)
              )
            : 1850;

    // Weight in metric tonnes (min 0.5 tonne fallback)
    const weightTonnes = Math.max(
        0.5,
        (parseFloat(shipment.total_weight_kg || shipment.weight_kg) || 2500) / 1000
    );

    // Emissions = Distance (km) * Weight (t) * Emission Factor
    const emissionsKg = Math.round(distanceKm * weightTonnes * factor * 10) / 10;

    let ecoRating = 'Eco-Efficient';
    let ecoBadgeClass = 'bg-emerald-500/15 text-emerald-800 border-emerald-500/30';

    if (mode === 'AIR') {
        ecoRating = 'High-Speed Express';
        ecoBadgeClass = 'bg-amber-500/15 text-amber-800 border-amber-500/30';
    } else if (emissionsKg > 1500) {
        ecoRating = 'Standard Maritime';
        ecoBadgeClass = 'bg-sky-500/15 text-sky-800 border-sky-500/30';
    }

    return {
        distanceKm,
        emissionsKg,
        ecoRating,
        ecoBadgeClass,
        factorLabel: `${(factor * 1000).toFixed(1)} g CO₂/tkm`,
    };
}
