export const RATE_PER_KM = 0.60;
export const CURRENCY = 'RM';
export const DISTANCE_DECIMAL_PLACES = 2;
export const DEFAULT_COUNTRY = 'MY';

export const FORMAT_OPTIONS = {
    distance: {
        minimumFractionDigits: DISTANCE_DECIMAL_PLACES,
        maximumFractionDigits: DISTANCE_DECIMAL_PLACES
    },
    currency: {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }
};

// Add formatting utilities
export const formatCurrency = (value) => {
    const number = parseFloat(value).toFixed(FORMAT_OPTIONS.currency.maximumFractionDigits);
    return `${number}`;
};

export const formatDistance = (value) => {
    const number = parseFloat(value).toFixed(FORMAT_OPTIONS.distance.maximumFractionDigits);
    return `${number} km`;
};
