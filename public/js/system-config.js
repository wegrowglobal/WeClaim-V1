import ErrorHandler from './utils/error-handler.js';
import { SwalUtils } from './utils/swal-utils.js';

document.addEventListener('DOMContentLoaded', () => {
    const configForm = document.getElementById('configForm');

    configForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(configForm);
        const configs = {};

        for (const [key, value] of formData.entries()) {
            configs[key] = value;
        }

        const operation = async () => {
            const response = await axios.post('/admin/system-config', { configs });
            return response.data;
        };

        const result = await ErrorHandler.handle(operation, 'Updating system configuration');

        if (result) {
            SwalUtils.showSuccess('System configuration updated successfully');
        }
    });
}); 
