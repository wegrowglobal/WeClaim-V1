export class MapUtils {
    static async geocodeWithRetry(geocoder, address, maxRetries = 3) {
        let retries = 0;
        
        while (retries < maxRetries) {
            try {
                const response = await new Promise((resolve, reject) => {
                    geocoder.geocode({ address }, (results, status) => {
                        if (status === 'OK' && results[0]) {
                            resolve(results[0]);
                        } else {
                            reject(new Error(`Geocoding failed: ${status}`));
                        }
                    });
                });
                return response;
            } catch (error) {
                retries++;
                if (retries === maxRetries) throw error;
                await new Promise(resolve => setTimeout(resolve, 1000 * retries));
            }
        }
    }

    static createBounds(locations) {
        const bounds = new google.maps.LatLngBounds();
        locations.forEach(location => bounds.extend(location));
        return bounds;
    }

    static calculateTotalDistance(legs) {
        return legs.reduce((sum, leg) => sum + leg.distance.value, 0) / 1000;
    }

    static formatDuration(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        return { hours, minutes };
    }
} 