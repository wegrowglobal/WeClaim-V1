class ValidationUtils {
    static isValidEmail(email) {
        if (!email || typeof email !== 'string') return false;
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim());
    }

    static isValidNumber(value) {
        if (value === null || value === undefined || value === '') return false;
        const parsed = parseFloat(value);
        return !isNaN(parsed) && isFinite(parsed);
    }

    static isValidDate(date) {
        if (!date) return false;
        const parsed = new Date(date);
        return parsed instanceof Date && !isNaN(parsed);
    }

    static isValidPhoneNumber(phone) {
        if (!phone || typeof phone !== 'string') return false;
        return /^[\d\s+()-]{10,}$/.test(phone.trim());
    }

    static isRequired(value) {
        if (typeof value === 'string') return value.trim().length > 0;
        return value !== null && value !== undefined;
    }

    static isValidLength(value, min, max) {
        if (!value || typeof value !== 'string') return false;
        const length = value.trim().length;
        return length >= min && length <= max;
    }
}

export default ValidationUtils; 