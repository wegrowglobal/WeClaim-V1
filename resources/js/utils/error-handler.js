class ErrorHandler {
    static async handle(operation, context) {
        try {
            return await operation();
        } catch (error) {
            console.error(`Error in ${context}:`, error);
            
            // Show user-friendly error message
            Swal.fire({
                icon: 'error',
                title: 'Operation Failed',
                text: this.getUserFriendlyMessage(error),
                toast: true,
                position: 'top-end',
                timer: 3000
            });
            
            return false;
        }
    }

    static getUserFriendlyMessage(error) {
        if (error.response?.data?.message) {
            return error.response.data.message;
        }

        const errorMap = {
            'INVALID_JSON': 'Invalid data format',
            'NETWORK_ERROR': 'Network connection issue',
            'VALIDATION_ERROR': 'Please check your input'
        };

        return errorMap[error.code] || 'An unexpected error occurred';
    }
}

export default ErrorHandler; 