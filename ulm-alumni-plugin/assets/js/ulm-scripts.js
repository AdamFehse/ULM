/**
 * ULM Alumni Platform - JavaScript
 * TODO: Add lazy loading, modals, etc.
 */

(function () {
    var eventsInitialized = false;
    var currentPhotoIndex = 0;
    var currentAlumniPhotos = [];

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
                photo.style.cursor = 'zoom-in';
            } else {
                photo.src = '';
                photo.alt = '';
                photo.style.display = 'none';
            }
        }

        // Store all visible photos for lightbox navigation
        var allPhotos = qsa('.js-ulm-alumni-trigger');
        currentAlumniPhotos = [];
        var currentPhotoFound = false;
        var currentIndex = 0;
        allPhotos.forEach(function(card, idx) {
            var cardPayload = parsePayload(card);
            if (cardPayload && cardPayload.photo) {
                currentAlumniPhotos.push(cardPayload.photo);
                if (cardPayload.photo === payload.photo) {
                    currentIndex = currentAlumniPhotos.length - 1;
                    currentPhotoFound = true;
                }
            }
        });
        if (currentPhotoFound) {
            currentPhotoIndex = currentIndex;
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

    function closeModal() {
        var modal = qs('#ulm-alumni-modal');
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    function openLightbox(imageUrl, index) {
        var lightbox = qs('#ulm-lightbox');
        if (!lightbox) {
            console.warn('[ULM] Lightbox element not found in DOM');
            return;
        }

        var img = qs('.ulm-lightbox__image', lightbox);
        if (img) {
            img.src = imageUrl;
            img.alt = 'Enlarged alumni photo';
        }

        currentPhotoIndex = index;
        lightbox.classList.add('is-open');
        lightbox.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        var lightbox = qs('#ulm-lightbox');
        if (!lightbox) return;
        lightbox.classList.remove('is-open');
        lightbox.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    function showLightboxImage(index) {
        if (index < 0 || index >= currentAlumniPhotos.length) {
            return;
        }
        openLightbox(currentAlumniPhotos[index], index);
    }

    function nextLightboxImage() {
        var nextIndex = (currentPhotoIndex + 1) % currentAlumniPhotos.length;
        showLightboxImage(nextIndex);
    }

    function prevLightboxImage() {
        var prevIndex = currentPhotoIndex === 0 ? currentAlumniPhotos.length - 1 : currentPhotoIndex - 1;
        showLightboxImage(prevIndex);
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
            if (event.target.closest('[data-ulm-modal-close]')) {
                closeModal();
            }
        });

        document.addEventListener('click', function (event) {
            if (event.target.closest('.ulm-modal__photo')) {
                var photo = qs('.ulm-modal__photo');
                if (photo && photo.src) {
                    openLightbox(photo.src, currentPhotoIndex);
                }
            }
        });

        document.addEventListener('click', function (event) {
            if (event.target.closest('[data-ulm-lightbox-close]')) {
                closeLightbox();
            }
        });

        document.addEventListener('click', function (event) {
            if (event.target.closest('[data-ulm-lightbox-next]')) {
                nextLightboxImage();
            }
        });

        document.addEventListener('click', function (event) {
            if (event.target.closest('[data-ulm-lightbox-prev]')) {
                prevLightboxImage();
            }
        });

        document.addEventListener('keydown', function (event) {
            var lightbox = qs('#ulm-lightbox');
            var isLightboxOpen = lightbox && lightbox.classList.contains('is-open');

            if (event.key === 'Escape') {
                if (isLightboxOpen) {
                    closeLightbox();
                } else {
                    closeModal();
                }
            }

            if (isLightboxOpen) {
                if (event.key === 'ArrowRight') {
                    nextLightboxImage();
                }
                if (event.key === 'ArrowLeft') {
                    prevLightboxImage();
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

        eventsInitialized = true;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindTriggers);
    } else {
        bindTriggers();
    }
})();
