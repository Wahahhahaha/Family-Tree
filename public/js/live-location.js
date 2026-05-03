(function () {
    if (window.__liveLocationFeatureInitialized) {
        return;
    }
    window.__liveLocationFeatureInitialized = true;

    var UPDATE_URL = '/live-location/update';
    var csrfToken = '';
    var lastSentKey = '';
    var watchId = null;
    var mapInstance = null;
    var mapMarkers = [];
    var translations = window.liveLocationTranslations || {};

    function t(key, fallback) {
        var value = Object.prototype.hasOwnProperty.call(translations, key) ? translations[key] : null;
        return value || fallback || key;
    }

    function getCsrfToken() {
        if (csrfToken) {
            return csrfToken;
        }

        var tokenNode = document.querySelector('meta[name="csrf-token"]');
        csrfToken = tokenNode ? (tokenNode.getAttribute('content') || '') : '';
        return csrfToken;
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function normalizeNumber(value) {
        var numeric = Number(value);
        return isFinite(numeric) ? numeric : null;
    }

    function truncateCoordinate(value) {
        var numeric = normalizeNumber(value);
        if (numeric === null) {
            return '';
        }

        return numeric.toFixed(5);
    }

    function getSharedCoordinates() {
        if (window.familyTreeGeo && typeof window.familyTreeGeo.getCoordinates === 'function') {
            return window.familyTreeGeo.getCoordinates();
        }

        if (!navigator.geolocation) {
            return Promise.resolve(null);
        }

        return new Promise(function (resolve) {
            navigator.geolocation.getCurrentPosition(function (position) {
                var latitude = normalizeNumber(position && position.coords ? position.coords.latitude : null);
                var longitude = normalizeNumber(position && position.coords ? position.coords.longitude : null);
                var accuracy = normalizeNumber(position && position.coords ? position.coords.accuracy : null);

                if (latitude === null || longitude === null) {
                    resolve(null);
                    return;
                }

                resolve({
                    latitude: latitude,
                    longitude: longitude,
                    accuracy: accuracy,
                });
            }, function () {
                resolve(null);
            }, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0,
            });
        });
    }

    function updateStatus(message, isError) {
        var statusNode = document.getElementById('liveLocationStatusMessage');
        if (!statusNode) {
            return;
        }

        statusNode.textContent = message || '';
        statusNode.style.color = isError ? '#b42318' : '#27445f';
    }

    function buildMarkerIcon(pictureUrl) {
        return L.divIcon({
            className: '',
            html: '<div class="live-location-marker"><img src="' + escapeHtml(pictureUrl) + '" alt="' + escapeHtml(t('member_photo', 'Member photo')) + '"></div>',
            iconSize: [48, 48],
            iconAnchor: [24, 48],
            popupAnchor: [0, -44],
        });
    }

    function buildPopupHtml(member) {
        return [
            '<div class="live-location-tooltip-card">',
            '<h3>' + escapeHtml(member.name || t('unknown_member', 'Unknown member')) + '</h3>',
            '<p class="relation">' + escapeHtml(member.relationship || t('other_family_member', 'Other family member')) + '</p>',
            '<p class="updated-at"><strong>' + escapeHtml(t('last_updated', 'Last updated')) + ':</strong> ' + escapeHtml(member.updated_at_exact || t('unknown', 'Unknown')) + '</p>',
            '<p class="updated-at">' + escapeHtml(member.updated_at_label || '') + '</p>',
            '</div>',
        ].join('');
    }

    function initializeMap() {
        var mapElement = document.getElementById('liveLocationMap');
        var pageData = window.liveLocationPageData || {};
        if (!mapElement || typeof L === 'undefined') {
            return;
        }

        var center = Array.isArray(pageData.center) ? pageData.center : [0, 0];
        var zoom = Number(pageData.zoom || 2);

        mapInstance = L.map(mapElement, {
            scrollWheelZoom: false,
        }).setView(center, zoom);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(mapInstance);

        var bounds = [];
        var markers = Array.isArray(pageData.markers) ? pageData.markers : [];
        markers.forEach(function (member) {
            var latitude = normalizeNumber(member.latitude);
            var longitude = normalizeNumber(member.longitude);
            if (latitude === null || longitude === null) {
                return;
            }

            var marker = L.marker([latitude, longitude], {
                icon: buildMarkerIcon(member.picture_url || ''),
                title: member.name || 'Family member',
            }).addTo(mapInstance);

            marker.bindTooltip(buildPopupHtml(member), {
                className: 'live-location-tooltip',
                direction: 'top',
                sticky: true,
                opacity: 1,
                offset: [0, -28],
            });

            marker.on('mouseover', function () {
                this.openTooltip();
            });

            marker.on('mouseout', function () {
                this.closeTooltip();
            });

            bounds.push([latitude, longitude]);
            mapMarkers.push(marker);
        });

        if (bounds.length > 1) {
            mapInstance.fitBounds(bounds, {
                padding: [50, 50],
            });
        } else if (bounds.length === 1) {
            mapInstance.setView(bounds[0], 10);
        }

        var trackedCountNode = document.getElementById('trackedMemberCount');
        if (trackedCountNode) {
            trackedCountNode.textContent = String(markers.length);
        }

        updateStatus(markers.length > 0 ? t('live_markers_ready', 'Live markers are ready.') : t('no_tracked_locations', 'No tracked locations have been saved yet.'), false);
    }

    function sendLocationUpdate(coords) {
        if (!coords) {
            return Promise.resolve(null);
        }

        var latitude = normalizeNumber(coords.latitude);
        var longitude = normalizeNumber(coords.longitude);
        var accuracy = normalizeNumber(coords.accuracy);

        if (latitude === null || longitude === null) {
            return Promise.resolve(null);
        }

        var sentKey = truncateCoordinate(latitude) + ':' + truncateCoordinate(longitude) + ':' + truncateCoordinate(accuracy || 0);
        if (sentKey === lastSentKey) {
            return Promise.resolve(null);
        }

        lastSentKey = sentKey;
        updateStatus(t('updating_live_location', 'Updating your live location...'), false);

        return fetch(UPDATE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                latitude: latitude,
                longitude: longitude,
                accuracy: accuracy,
            }),
        }).then(function (response) {
            return response.json().catch(function () {
                return null;
            }).then(function (data) {
                return { ok: response.ok, data: data };
            });
        }).then(function (result) {
            if (result && result.ok) {
                updateStatus(t('location_shared_updated', 'Your location is being shared and updated automatically.'), false);
                return result;
            }

            var message = result && result.data && result.data.message
                ? result.data.message
                : t('unable_update_live_location', 'Unable to update live location.');
            updateStatus(message, true);
            return result;
        }).catch(function () {
            updateStatus(t('unable_update_live_location', 'Unable to update live location.'), true);
            return null;
        });
    }

    function startWatchingLocation() {
        if (!navigator.geolocation || watchId !== null) {
            return;
        }

        watchId = navigator.geolocation.watchPosition(function (position) {
            var coords = position && position.coords ? position.coords : null;
            if (!coords) {
                return;
            }

            sendLocationUpdate({
                latitude: coords.latitude,
                longitude: coords.longitude,
                accuracy: coords.accuracy,
            });
        }, function () {
            updateStatus(t('location_access_denied', 'Location access was denied or is unavailable.'), true);
        }, {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0,
        });
    }

    function bootstrap() {
        initializeMap();

        getSharedCoordinates()
            .then(function (coords) {
                return sendLocationUpdate(coords);
            })
            .then(function () {
                startWatchingLocation();
            })
            .catch(function () {
                startWatchingLocation();
            });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootstrap);
    } else {
        bootstrap();
    }
})();
