/**
 * ULM Alumni Platform - JavaScript
 * TODO: Add lazy loading, modals, etc.
 */

(function () {
    var eventsInitialized = false;

    function qs(selector, scope) {
        return (scope || document).querySelector(selector);
    }

    function qsa(selector, scope) {
        return Array.from((scope || document).querySelectorAll(selector));
    }

    function safeText(value) {
        return value ? String(value) : '';
    }

    function openModal(payload) {
        var modal = qs('#ulm-alumni-modal');
        if (!modal) {
            console.warn('[ULM] Modal element not found in DOM');
            return;
        }

        var photo = qs('.ulm-modal__photo', modal);
        var name = qs('.ulm-modal__name', modal);
        var meta = qs('.ulm-modal__meta', modal);
        var body = qs('.ulm-modal__body', modal);
        var links = qs('.ulm-modal__links', modal);

        if (photo) {
            if (payload.photo) {
                photo.src = payload.photo;
                photo.alt = safeText(payload.name);
                photo.style.display = '';
                photo.style.cursor = 'default';
            } else {
                photo.src = '';
                photo.alt = '';
                photo.style.display = 'none';
            }
        }

        if (name) name.textContent = safeText(payload.name);

        var metaParts = [];
        if (payload.instruments) metaParts.push(payload.instruments);
        if (payload.role) metaParts.push(payload.role);
        if (payload.years) metaParts.push(payload.years);
        if (payload.gradYear) metaParts.push('Class of ' + payload.gradYear);
        if (payload.current) metaParts.push(payload.current);
        if (payload.location) metaParts.push(payload.location);
        if (meta) meta.textContent = metaParts.join(' · ');

        var bodyParts = [];
        if (payload.bio) bodyParts.push(payload.bio);
        if (payload.progression) bodyParts.push(payload.progression);
        if (body) body.textContent = bodyParts.join('\n\n');

        if (links) {
            links.innerHTML = '';
            if (payload.website) {
                var site = document.createElement('a');
                site.href = payload.website;
                site.target = '_blank';
                site.rel = 'noopener noreferrer';
                site.textContent = 'Website';
                links.appendChild(site);
            }
            if (payload.mediaItems && payload.mediaItems.length > 0) {
                payload.mediaItems.forEach(function(item) {
                    var media = document.createElement('a');
                    media.href = item.url;
                    media.target = '_blank';
                    media.rel = 'noopener noreferrer';
                    media.textContent = item.caption || 'Media';
                    links.appendChild(media);
                });
            }
            if (!links.childNodes.length) {
                links.style.display = 'none';
            } else {
                links.style.display = '';
            }
        }

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        // Verify modal became visible after adding is-open class
        var computedStyle = window.getComputedStyle(modal);
        if (computedStyle.display === 'none') {
            console.warn('[ULM] Modal CSS not loaded - modal is still invisible after adding is-open class');
        }
    }

    function openScreeningModal(payload) {
        var modal = qs('#ulm-screening-modal');
        if (!modal) {
            return;
        }

        var photo = qs('.ulm-modal__photo', modal);
        var title = qs('.ulm-modal__name', modal);
        var meta = qs('.ulm-modal__meta', modal);
        var body = qs('.ulm-modal__body', modal);
        var links = qs('.ulm-modal__links', modal);

        if (photo) {
            if (payload.photo) {
                photo.src = payload.photo;
                photo.alt = safeText(payload.title);
                photo.style.display = '';
            } else {
                photo.src = '';
                photo.alt = '';
                photo.style.display = 'none';
            }
        }

        if (title) title.textContent = safeText(payload.title);

        var metaParts = [];
        if (payload.dateDisplay || payload.date) metaParts.push(payload.dateDisplay || payload.date);
        if (payload.venue) metaParts.push(payload.venue);
        if (payload.location) metaParts.push(payload.location);
        if (meta) meta.textContent = metaParts.join(' · ');

        if (body) {
            body.textContent = safeText(payload.description);
        }

        if (links) {
            links.innerHTML = '';

            var destination = payload.lat && payload.lng
                ? payload.lat + ',' + payload.lng
                : safeText(payload.location);
            if (destination) {
                var directions = document.createElement('a');
                directions.href = 'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(destination);
                directions.target = '_blank';
                directions.rel = 'noopener noreferrer';
                directions.textContent = 'Get Directions';
                links.appendChild(directions);
            }

            if (payload.ticketsUrl) {
                var tickets = document.createElement('a');
                tickets.href = payload.ticketsUrl;
                tickets.target = '_blank';
                tickets.rel = 'noopener noreferrer';
                tickets.textContent = 'Get Tickets';
                links.appendChild(tickets);
            }

            if (payload.mediaItems && payload.mediaItems.length > 0) {
                payload.mediaItems.forEach(function(item) {
                    var media = document.createElement('a');
                    media.href = item.url;
                    media.target = '_blank';
                    media.rel = 'noopener noreferrer';
                    media.textContent = item.caption || 'Media';
                    links.appendChild(media);
                });
            }

            if (!links.childNodes.length) {
                links.style.display = 'none';
            } else {
                links.style.display = '';
            }
        }

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';

        if (window.ULMAlumniMapInstance && typeof window.ULMAlumniMapInstance.focusOnScreening === 'function' && payload.id) {
            window.ULMAlumniMapInstance.focusOnScreening(payload.id);
        }
    }

    function closeModal() {
        var modal = qs('#ulm-alumni-modal');
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    function closeScreeningModal() {
        var modal = qs('#ulm-screening-modal');
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }


    function parsePayload(el) {
        var raw = el.getAttribute('data-alumni') || el.dataset.alumni;
        if (!raw) return null;
        try {
            return JSON.parse(raw);
        } catch (err) {
            return null;
        }
    }

    function parseScreeningPayload(el) {
        var raw = el.getAttribute('data-screening') || el.dataset.screening;
        if (!raw) return null;
        try {
            return JSON.parse(raw);
        } catch (err) {
            return null;
        }
    }

    function bindTriggers() {
        if (eventsInitialized) {
            console.warn('[ULM] Event listeners already bound, skipping duplicate initialization');
            return;
        }

        document.addEventListener('click', function (event) {
            var trigger = event.target.closest('.js-ulm-alumni-trigger');
            if (!trigger) return;
            var payload = parsePayload(trigger);
            if (payload) openModal(payload);
        });

        document.addEventListener('click', function (event) {
            var trigger = event.target.closest('.js-ulm-screening-trigger');
            if (!trigger) return;
            var payload = parseScreeningPayload(trigger);
            if (payload) openScreeningModal(payload);
        });

        document.addEventListener('click', function (event) {
            if (event.target.closest('[data-ulm-modal-close]')) {
                closeModal();
            }
        });

        document.addEventListener('click', function (event) {
            if (event.target.closest('[data-ulm-screening-close]')) {
                closeScreeningModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            var screeningModal = qs('#ulm-screening-modal');
            var isScreeningModalOpen = screeningModal && screeningModal.classList.contains('is-open');

            if (event.key === 'Escape') {
                if (isScreeningModalOpen) {
                    closeScreeningModal();
                } else {
                    closeModal();
                }
            }
        });

        // Keyboard accessibility: Allow Enter and Space to open modal on alumni cards
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                var trigger = event.target.closest('.js-ulm-alumni-trigger');
                if (!trigger) return;
                event.preventDefault();
                var payload = parsePayload(trigger);
                if (payload) openModal(payload);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                var trigger = event.target.closest('.js-ulm-screening-trigger');
                if (!trigger) return;
                event.preventDefault();
                var payload = parseScreeningPayload(trigger);
                if (payload) openScreeningModal(payload);
            }
        });

        eventsInitialized = true;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindTriggers);
    } else {
        bindTriggers();
    }

    window.ULMOpenScreeningModal = openScreeningModal;
})();
