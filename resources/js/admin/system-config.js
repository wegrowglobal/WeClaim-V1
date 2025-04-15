import ErrorHandler from '../utils/error-handler.js';
import Logger from '../utils/logger.js';
import { SwalUtils } from '../utils/swal-utils.js';
import ValidationUtils from '../utils/validation.js';

class SystemConfigManager {
    constructor() {
        this.form = document.getElementById('configForm');
        this.initialize();
    }

    initialize() {
        Logger.init(true);
        this.setupEventListeners();
    }

    setupEventListeners() {
        this.form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.handleFormSubmit();
        });
    }

    validateConfigs(configs) {
        for (const [key, value] of Object.entries(configs)) {
            if (!ValidationUtils.isRequired(value)) {
                return false;
            }
            
            const input = document.querySelector(`input[name="${key}"]`);
            if (input.type === 'number' && !ValidationUtils.isValidNumber(value)) {
                return false;
            }
        }
        return true;
    }

    async handleFormSubmit() {
        const formData = new FormData(this.form);
        const configs = {};

        for (const [key, value] of formData.entries()) {
            configs[key] = value;
        }

        if (!this.validateConfigs(configs)) {
            await SwalUtils.showError('Please fill in all fields with valid values');
            return;
        }

        const operation = async () => {
            Logger.log('Updating system configuration', configs);
            const response = await axios.post('/admin/system-config', { configs });
            return response.data;
        };

        const result = await ErrorHandler.handle(operation, 'Updating system configuration');

        if (result) {
            await SwalUtils.showSuccess('System configuration updated successfully');
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new SystemConfigManager();
}); 
