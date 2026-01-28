/**
 * Alumni Directory - Search, Filter, and Sort Functionality
 */

class ULMAlumniFilter {
    constructor() {
        this.searchInput = document.querySelector( '.ulm-search' );
        this.instrumentFilter = document.querySelector( '.ulm-filter-instrument' );
        this.yearFilter = document.querySelector( '.ulm-filter-year' );
        this.locationFilter = document.querySelector( '.ulm-filter-location' );
        this.sortSelect = document.querySelector( '.ulm-sort' );
        this.clearButton = document.querySelector( '.ulm-clear-filters' );
        this.grid = document.querySelector( '.ulm-alumni-grid' );
        this.resultsCount = document.querySelector( '.ulm-results-count' );
        this.resultsNumber = document.querySelector( '#ulm-results-number' );

        if ( ! this.grid ) {
            return;
        }

        this.cards = Array.from( this.grid.querySelectorAll( '.ulm-alumni-card' ) );
        this.currentSort = 'name-asc';
        this.debounceTimer = null;

        this.init();
    }

    init() {
        if ( this.searchInput ) {
            this.searchInput.addEventListener( 'input', ( e ) => {
                clearTimeout( this.debounceTimer );
                this.debounceTimer = setTimeout( () => {
                    this.applyFilters();
                }, 300 );
            } );
        }

        if ( this.instrumentFilter ) {
            this.instrumentFilter.addEventListener( 'change', () => this.applyFilters() );
        }

        if ( this.yearFilter ) {
            this.yearFilter.addEventListener( 'change', () => this.applyFilters() );
        }

        if ( this.locationFilter ) {
            this.locationFilter.addEventListener( 'change', () => this.applyFilters() );
        }

        if ( this.sortSelect ) {
            this.sortSelect.addEventListener( 'change', ( e ) => {
                this.currentSort = e.target.value;
                this.applyFilters();
            } );
        }

        if ( this.clearButton ) {
            this.clearButton.addEventListener( 'click', () => this.clearAllFilters() );
        }

        // Populate filter dropdowns
        this.populateFilters();
    }

    populateFilters() {
        this.populateInstruments();
        this.populateYears();
        this.populateLocations();
    }

    populateInstruments() {
        if ( ! this.instrumentFilter ) {
            return;
        }

        const instruments = new Set();
        this.cards.forEach( ( card ) => {
            const cardInstruments = card.getAttribute( 'data-instruments' );
            if ( cardInstruments ) {
                cardInstruments.split( ',' ).forEach( ( inst ) => {
                    instruments.add( inst.trim() );
                } );
            }
        } );

        // Clear existing options (keep the first one)
        while ( this.instrumentFilter.options.length > 1 ) {
            this.instrumentFilter.remove( 1 );
        }

        // Add sorted instruments
        Array.from( instruments )
            .sort()
            .forEach( ( instrument ) => {
                const option = document.createElement( 'option' );
                option.value = instrument;
                option.textContent = instrument;
                this.instrumentFilter.appendChild( option );
            } );
    }

    populateYears() {
        if ( ! this.yearFilter ) {
            return;
        }

        const decades = new Set();
        this.cards.forEach( ( card ) => {
            const year = card.getAttribute( 'data-year' );
            if ( year ) {
                const decade = Math.floor( year / 10 ) * 10 + 's';
                decades.add( decade );
            }
        } );

        // Clear existing options (keep the first one)
        while ( this.yearFilter.options.length > 1 ) {
            this.yearFilter.remove( 1 );
        }

        // Add sorted decades
        Array.from( decades )
            .sort( ( a, b ) => parseInt( b ) - parseInt( a ) )
            .forEach( ( decade ) => {
                const option = document.createElement( 'option' );
                option.value = decade;
                option.textContent = decade;
                this.yearFilter.appendChild( option );
            } );
    }

    populateLocations() {
        if ( ! this.locationFilter ) {
            return;
        }

        const locations = new Set();
        this.cards.forEach( ( card ) => {
            const location = card.getAttribute( 'data-location' );
            if ( location ) {
                locations.add( location );
            }
        } );

        // Clear existing options (keep the first one)
        while ( this.locationFilter.options.length > 1 ) {
            this.locationFilter.remove( 1 );
        }

        // Add sorted locations
        Array.from( locations )
            .sort()
            .forEach( ( location ) => {
                const option = document.createElement( 'option' );
                option.value = location;
                option.textContent = location;
                this.locationFilter.appendChild( option );
            } );
    }

    applyFilters() {
        const searchTerm = this.searchInput ? this.searchInput.value.toLowerCase() : '';
        const selectedInstrument = this.instrumentFilter ? this.instrumentFilter.value : '';
        const selectedYear = this.yearFilter ? this.yearFilter.value : '';
        const selectedLocation = this.locationFilter ? this.locationFilter.value : '';

        let visibleCards = this.cards.filter( ( card ) => {
            const matchesSearch = this.matchesSearch( card, searchTerm );
            const matchesInstrument = ! selectedInstrument || this.matchesInstrument( card, selectedInstrument );
            const matchesYear = ! selectedYear || this.matchesYear( card, selectedYear );
            const matchesLocation = ! selectedLocation || this.matchesLocation( card, selectedLocation );

            return matchesSearch && matchesInstrument && matchesYear && matchesLocation;
        } );

        // Apply sorting
        visibleCards = this.sortCards( visibleCards );

        // Update display
        this.cards.forEach( ( card ) => {
            if ( visibleCards.includes( card ) ) {
                card.classList.remove( 'ulm-hidden' );
            } else {
                card.classList.add( 'ulm-hidden' );
            }
        } );

        // Update results count
        this.updateResultsCount( visibleCards.length );

        // Show "no results" message if needed
        this.showNoResults( visibleCards.length === 0 );
    }

    matchesSearch( card, searchTerm ) {
        if ( ! searchTerm ) {
            return true;
        }

        const name = card.getAttribute( 'data-name' ) || '';
        return name.toLowerCase().includes( searchTerm );
    }

    matchesInstrument( card, instrument ) {
        const cardInstruments = card.getAttribute( 'data-instruments' ) || '';
        return cardInstruments.split( ',' ).map( ( i ) => i.trim() ).includes( instrument );
    }

    matchesYear( card, decade ) {
        const year = card.getAttribute( 'data-year' ) || '';
        const cardDecade = Math.floor( year / 10 ) * 10 + 's';
        return cardDecade === decade;
    }

    matchesLocation( card, location ) {
        const cardLocation = card.getAttribute( 'data-location' ) || '';
        return cardLocation === location;
    }

    sortCards( cardsToSort ) {
        const sorted = [ ...cardsToSort ];

        switch ( this.currentSort ) {
            case 'name-asc':
                sorted.sort( ( a, b ) => {
                    const nameA = ( a.getAttribute( 'data-sort-name' ) || '' ).toLowerCase();
                    const nameB = ( b.getAttribute( 'data-sort-name' ) || '' ).toLowerCase();
                    return nameA.localeCompare( nameB );
                } );
                break;

            case 'name-desc':
                sorted.sort( ( a, b ) => {
                    const nameA = ( a.getAttribute( 'data-sort-name' ) || '' ).toLowerCase();
                    const nameB = ( b.getAttribute( 'data-sort-name' ) || '' ).toLowerCase();
                    return nameB.localeCompare( nameA );
                } );
                break;

            case 'year-desc':
                sorted.sort( ( a, b ) => {
                    const yearA = parseInt( a.getAttribute( 'data-sort-year' ) || '0' );
                    const yearB = parseInt( b.getAttribute( 'data-sort-year' ) || '0' );
                    return yearB - yearA;
                } );
                break;

            case 'year-asc':
                sorted.sort( ( a, b ) => {
                    const yearA = parseInt( a.getAttribute( 'data-sort-year' ) || '0' );
                    const yearB = parseInt( b.getAttribute( 'data-sort-year' ) || '0' );
                    return yearA - yearB;
                } );
                break;

            case 'years-active-recent':
                sorted.sort( ( a, b ) => {
                    const yearsA = a.getAttribute( 'data-sort-years-active' ) || '0';
                    const yearsB = b.getAttribute( 'data-sort-years-active' ) || '0';
                    const recentA = parseInt( yearsA.split( '-' ).pop() || '0' );
                    const recentB = parseInt( yearsB.split( '-' ).pop() || '0' );
                    return recentB - recentA;
                } );
                break;
        }

        return sorted;
    }

    updateResultsCount( count ) {
        if ( ! this.resultsCount || ! this.resultsNumber ) {
            return;
        }

        this.resultsNumber.textContent = count;

        if ( this.searchInput && this.searchInput.value !== '' ||
             this.instrumentFilter && this.instrumentFilter.value !== '' ||
             this.yearFilter && this.yearFilter.value !== '' ||
             this.locationFilter && this.locationFilter.value !== '' ) {
            this.resultsCount.classList.add( 'active' );
        } else {
            this.resultsCount.classList.remove( 'active' );
        }
    }

    showNoResults( show ) {
        let noResultsDiv = document.querySelector( '.ulm-no-results' );

        if ( show ) {
            if ( ! noResultsDiv ) {
                noResultsDiv = document.createElement( 'div' );
                noResultsDiv.className = 'ulm-no-results';
                noResultsDiv.innerHTML = `
                    <div class="ulm-no-results-icon">🔍</div>
                    <div class="ulm-no-results-text">No alumni found matching your filters.</div>
                `;
                this.grid.appendChild( noResultsDiv );
            }
        } else {
            if ( noResultsDiv ) {
                noResultsDiv.remove();
            }
        }
    }

    clearAllFilters() {
        if ( this.searchInput ) {
            this.searchInput.value = '';
        }
        if ( this.instrumentFilter ) {
            this.instrumentFilter.value = '';
        }
        if ( this.yearFilter ) {
            this.yearFilter.value = '';
        }
        if ( this.locationFilter ) {
            this.locationFilter.value = '';
        }
        if ( this.sortSelect ) {
            this.sortSelect.value = 'name-asc';
        }

        this.currentSort = 'name-asc';
        this.applyFilters();
    }
}

// Initialize when DOM is ready
document.addEventListener( 'DOMContentLoaded', function() {
    new ULMAlumniFilter();
} );
