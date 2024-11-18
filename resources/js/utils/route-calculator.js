export class RouteCalculator {
    constructor(ratePerKm = 0.60) {
        this.ratePerKm = ratePerKm;
    }

    async calculateRoute(directionsService, locations) {
        if (locations.length < 2) return null;

        try {
            return await new Promise((resolve, reject) => {
                directionsService.route({
                    origin: locations[0],
                    destination: locations[locations.length - 1],
                    waypoints: locations.slice(1, -1).map(location => ({
                        location,
                        stopover: true
                    })),
                    travelMode: google.maps.TravelMode.DRIVING,
                    region: 'MY'
                }, (result, status) => {
                    if (status === 'OK') resolve(result);
                    else reject(new Error(`Directions request failed: ${status}`));
                });
            });
        } catch (error) {
            console.error('Error calculating route:', error);
            return null;
        }
    }

    calculateTotals(legs) {
        const totalDistance = legs.reduce((sum, leg) => sum + leg.distance.value, 0) / 1000;
        const totalDuration = legs.reduce((sum, leg) => sum + leg.duration.value, 0);
        const totalCost = totalDistance * this.ratePerKm;

        return {
            distance: totalDistance.toFixed(2),
            duration: this.formatDuration(totalDuration),
            cost: totalCost.toFixed(2)
        };
    }

    calculateSegmentDetails(legs) {
        return legs.map((leg, index) => ({
            from_location: leg.start_address,
            to_location: leg.end_address,
            distance: leg.distance.value / 1000,
            duration: leg.duration.text,
            cost: (leg.distance.value / 1000) * this.ratePerKm,
            order: index + 1
        }));
    }

    formatDuration(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        return `${hours}h ${minutes}m`;
    }
} 