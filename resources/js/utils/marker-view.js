export class MarkerView {
    createMarkerElement(label, color) {
        const markerElement = document.createElement('div');
        markerElement.className = 'custom-marker';
        markerElement.innerHTML = `
            <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-medium text-white"
                 style="background-color: ${color}">
                ${label || ''}
            </div>
        `;
        return markerElement;
    }
} 