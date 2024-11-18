export class SwalUtils {
    static async showLoadingMap(mapContainer, message = 'Loading map...') {
        return this.showMapLoading(mapContainer, message);
    }

    static async showMapLoading(mapContainer, message = 'Calculating Route...') {
        // Create a loading overlay div
        const overlayDiv = document.createElement('div');
        overlayDiv.className = 'absolute inset-0 flex items-center justify-center bg-gray-900/30 backdrop-blur-sm z-50';
        
        const loadingContent = document.createElement('div');
        loadingContent.className = 'bg-white rounded-lg shadow-lg p-6 max-w-sm w-full mx-4 text-center';
        loadingContent.innerHTML = `
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-indigo-600 border-t-transparent mb-4"></div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">${message}</h3>
        `;

        overlayDiv.appendChild(loadingContent);
        mapContainer.style.position = 'relative';
        mapContainer.appendChild(overlayDiv);

        // Set timeout to remove overlay after 3 seconds
        setTimeout(() => {
            if (overlayDiv && overlayDiv.parentNode === mapContainer) {
                overlayDiv.remove();
            }
        }, 3000);

        return {
            close: () => {
                if (overlayDiv && overlayDiv.parentNode === mapContainer) {
                    overlayDiv.remove();
                }
            }
        };
    }

    static async showError(message, mapContainer = null) {
        const options = {
            icon: 'error',
            title: 'Error',
            text: message,
            toast: true,
            position: 'top-end',
            timer: 3000,
            showConfirmButton: false,
            customClass: {
                popup: 'bg-white rounded-lg shadow-lg border border-red-100',
                title: 'text-base font-medium text-gray-900',
                htmlContainer: 'text-sm text-gray-500'
            }
        };

        if (mapContainer) {
            options.target = mapContainer;
        }

        return Swal.fire(options);
    }

    static async showSuccess(message, target = null) {
        const options = {
            icon: 'success',
            title: 'Success',
            text: message,
            timer: 2000,
            customClass: {
                popup: 'bg-white rounded-lg shadow-lg border border-green-100',
                title: 'text-lg font-medium text-gray-900',
                htmlContainer: 'text-sm text-gray-500'
            }
        };

        if (target) {
            options.target = target;
        }

        return Swal.fire(options);
    }
} 