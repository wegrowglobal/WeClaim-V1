import ErrorHandler from './utils/error-handler';
import Swal from 'sweetalert2';

window.handleRegistration = function(form) {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        return ErrorHandler.handle(async () => {
            const formData = new FormData(this);
            
            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const data = await response.json();

                if (data.success) {
                    window.location.href = data.redirect;
                }
            } catch (error) {
                await Swal.fire({
                    title: 'Error!',
                    text: 'Something went wrong. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#4F46E5'
                });
            }
        });
    });
}; 
