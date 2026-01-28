/**
 * Alumni Map - Leaflet Integration
 *
 * Planned Features:
 * - Interactive map showing alumni locations
 * - Clustered markers by city/region
 * - Click markers to see alumni cards
 * - Filter map by instrument, graduation year
 * - Heatmap view of alumni concentration
 *
 * Data Flow:
 * 1. Get alumni locations from directory
 * 2. Geocode locations (city, state -> lat/lng)
 * 3. Render map with markers
 * 4. Cluster markers by proximity
 * 5. Show alumni info on marker click
 */

class ULMAlumniMap {
    /**
     * Initialize the alumni map
     *
     * @param {string} containerId - ID of map container element
     * @param {object} options - Map configuration options
     */
    constructor( containerId, options = {} ) {
        this.containerId = containerId;
        this.container = document.querySelector( containerId );
        const themePrimary = this.getThemeColor( '--ulm-primary', '#2c5aa0' );
        this.options = Object.assign( {
            zoom: 4,
            center: { lat: 39.8283, lng: -98.5795 }, // Center of USA
            clusterRadius: 50,
            markerColor: themePrimary,
        }, options );

        this.alumniData = [];
        this.map = null;
        this.tileLayer = null;
        this.markers = [];

        this.init();
    }

    getThemeColor( cssVarName, fallback ) {
        const value = getComputedStyle( document.documentElement )
            .getPropertyValue( cssVarName )
            .trim();
        return value || fallback;
    }

    init() {
        if ( ! this.container ) {
            console.warn( 'Alumni map container not found:', this.containerId );
            return;
        }

        if ( typeof window.L === 'undefined' ) {
            console.warn( 'Leaflet not available. Check script enqueue order.' );
            return;
        }

        const lat = parseFloat( this.container.dataset.lat || this.options.center.lat );
        const lng = parseFloat( this.container.dataset.lng || this.options.center.lng );
        const zoom = parseInt( this.container.dataset.zoom || this.options.zoom, 10 );

        this.map = window.L.map( this.container, {
            zoomControl: true,
            scrollWheelZoom: false,
        } ).setView( [ lat, lng ], zoom );

        this.tileLayer = window.L.tileLayer(
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }
        ).addTo( this.map );

        console.info( 'Alumni map initialized (Leaflet)' );
    }

    /**
     * Set alumni data for map display
     *
     * Expected format:
     * [
     *   { name: 'John Doe', location: 'Chicago, IL', lat: 41.8781, lng: -87.6298 },
     *   { name: 'Jane Smith', location: 'New York, NY', lat: 40.7128, lng: -74.0060 }
     * ]
     */
    setAlumniData( data ) {
        this.alumniData = data;
        this.updateMarkers();
    }

    /**
     * Geocode locations (convert city/state to lat/lng)
     *
     * Integration points:
     * - Google Geocoding API
     * - Mapbox Geocoding API
     * - Nominatim (open-source)
     */
    geocodeLocations( locations ) {
        // TODO: Implement geocoding
        // This should handle batch requests to avoid API limits
        return Promise.resolve( {} );
    }

    /**
     * Update map markers based on alumni data
     */
    updateMarkers() {
        if ( ! this.map || ! window.L ) {
            return;
        }

        this.markers.forEach( ( marker ) => marker.remove() );
        this.markers = [];

        this.alumniData.forEach( ( entry ) => {
            if ( typeof entry.lat !== 'number' || typeof entry.lng !== 'number' ) {
                return;
            }

            const marker = window.L.circleMarker( [ entry.lat, entry.lng ], {
                radius: 6,
                color: this.options.markerColor,
                fillColor: this.options.markerColor,
                fillOpacity: 0.7,
                weight: 1,
            } );

            if ( entry.name ) {
                marker.bindPopup( `${entry.name}${entry.location ? ' · ' + entry.location : ''}` );
            }

            marker.addTo( this.map );
            this.markers.push( marker );
        } );
    }

    /**
     * Filter map by criteria
     */
    filterBy( criteria ) {
        // TODO: Filter alumni data by:
        // - instrument
        // - graduation year
        // - location region
        // - custom date range
        console.info( 'Map filtered by:', criteria );
    }

    /**
     * Show heatmap view instead of markers
     *
     * Useful for visualizing alumni density
     */
    showHeatmap() {
        // TODO: Replace markers with heatmap layer
        console.info( 'Heatmap view enabled (stub)' );
    }

    /**
     * Export map as image or PDF
     */
    export( format = 'png' ) {
        // TODO: Implement map export
        console.info( 'Export map as', format );
    }
}

// Make available globally
window.ULMAlumniMap = ULMAlumniMap;

document.addEventListener( 'DOMContentLoaded', () => {
    if ( document.querySelector( '#ulm-alumni-map' ) ) {
        window.ULMAlumniMapInstance = new ULMAlumniMap( '#ulm-alumni-map' );
    }
} );
