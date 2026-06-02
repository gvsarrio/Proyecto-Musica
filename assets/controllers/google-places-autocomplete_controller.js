import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        apiKey: String
    }

    static targets = ['lat', 'lng']

    connect() {
        if (window.google && window.google.maps && window.google.maps.places) {
            this.initAutocomplete();
        } else {
            this.loadScript();
        }
    }

    loadScript() {
        const existing = document.getElementById('google-maps-script');
        if (existing) {
            existing.addEventListener('load', () => this.initAutocomplete());
            return;
        }
        const script = document.createElement('script');
        script.id = 'google-maps-script';
        script.src = 'https://maps.googleapis.com/maps/api/js?key=' + this.apiKeyValue + '&libraries=places&language=es';
        script.async = true;
        script.addEventListener('load', () => this.initAutocomplete());
        document.head.appendChild(script);
    }

    initAutocomplete() {
        const input = this.element.querySelector('input[type="text"]');
        if (!input) return;

        const autocomplete = new google.maps.places.Autocomplete(input, {
            types: ['(regions)'],
            fields: ['address_components', 'formatted_address', 'geometry']
        });

        // Desactivar el autocompletado del navegador para que no interfiera
        input.setAttribute('autocomplete', 'new-password');

        autocomplete.addListener('place_changed', () => {
            const place = autocomplete.getPlace();
            if (!place.address_components) return;

            const city = place.address_components.find(c =>
                c.types.includes('locality') ||
                c.types.includes('administrative_area_level_2') ||
                c.types.includes('administrative_area_level_1')
            );
            const country = place.address_components.find(c =>
                c.types.includes('country')
            );

            if (city && country) {
                input.value = city.long_name + ', ' + country.long_name;
            } else if (place.formatted_address) {
                input.value = place.formatted_address;
            }

            input.dispatchEvent(new Event('input'));

            if (place.geometry && place.geometry.location) {
                if (this.hasLatTarget) this.latTarget.value = place.geometry.location.lat();
                if (this.hasLngTarget) this.lngTarget.value = place.geometry.location.lng();
            }
        });
    }
}
