(function () {
    if (window.__activityLogGeoInitialized) {
        return;
    }
    window.__activityLogGeoInitialized = true;

    var CACHE_KEY = 'activity_log_geo_cache_v1';
    var MAX_CACHE_AGE_MS = 10 * 60 * 1000;
    var GEO_TIMEOUT_MS = 1800;
    var pendingGeoPromise = null;

    function parseStoredGeo(raw) {
        if (!raw) {
            return null;
        }

        try {
            var parsed = JSON.parse(raw);
            if (!parsed || typeof parsed !== 'object') {
                return null;
            }

            var latitude = normalizeCoordinate(parsed.latitude);
            var longitude = normalizeCoordinate(parsed.longitude);
            var timestamp = Number(parsed.timestamp || 0);

            if (latitude === null || longitude === null) {
                return null;
            }

            return {
                latitude: latitude,
                longitude: longitude,
                timestamp: timestamp > 0 ? timestamp : Date.now(),
            };
        } catch (error) {
            return null;
        }
    }

    function readCachedGeo() {
        try {
            var sessionValue = parseStoredGeo(window.sessionStorage ? sessionStorage.getItem(CACHE_KEY) : null);
            if (sessionValue) {
                return sessionValue;
            }
        } catch (error) {
            // Ignore storage access failures.
        }

        try {
            return parseStoredGeo(window.localStorage ? localStorage.getItem(CACHE_KEY) : null);
        } catch (error) {
            return null;
        }
    }

    function storeCachedGeo(coords) {
        if (!coords) {
            return;
        }

        var payload = JSON.stringify({
            latitude: coords.latitude,
            longitude: coords.longitude,
            timestamp: Date.now(),
        });

        try {
            if (window.sessionStorage) {
                sessionStorage.setItem(CACHE_KEY, payload);
            }
        } catch (error) {
            // Ignore storage failures.
        }

        try {
            if (window.localStorage) {
                localStorage.setItem(CACHE_KEY, payload);
            }
        } catch (error) {
            // Ignore storage failures.
        }
    }

    function normalizeCoordinate(value) {
        if (value === null || value === undefined) {
            return null;
        }

        var numericValue = Number(value);
        if (!isFinite(numericValue)) {
            return null;
        }

        return numericValue;
    }

    function fetchBrowserGeo() {
        return new Promise(function (resolve) {
            if (!navigator.geolocation) {
                resolve(null);
                return;
            }

            var settled = false;
            var timer = window.setTimeout(function () {
                if (!settled) {
                    settled = true;
                    resolve(null);
                }
            }, GEO_TIMEOUT_MS);

            navigator.geolocation.getCurrentPosition(function (position) {
                if (settled) {
                    return;
                }

                settled = true;
                window.clearTimeout(timer);

                var latitude = normalizeCoordinate(position && position.coords ? position.coords.latitude : null);
                var longitude = normalizeCoordinate(position && position.coords ? position.coords.longitude : null);
                if (latitude === null || longitude === null) {
                    resolve(null);
                    return;
                }

                var coords = {
                    latitude: latitude,
                    longitude: longitude,
                    timestamp: Date.now(),
                };
                storeCachedGeo(coords);
                resolve(coords);
            }, function () {
                if (settled) {
                    return;
                }

                settled = true;
                window.clearTimeout(timer);
                resolve(null);
            }, {
                enableHighAccuracy: false,
                timeout: GEO_TIMEOUT_MS,
                maximumAge: MAX_CACHE_AGE_MS,
            });
        });
    }

    function getGeoCoordinates() {
        var cached = readCachedGeo();
        if (cached && (Date.now() - cached.timestamp) <= MAX_CACHE_AGE_MS) {
            return Promise.resolve(cached);
        }

        if (!pendingGeoPromise) {
            pendingGeoPromise = fetchBrowserGeo().then(function (coords) {
                pendingGeoPromise = null;
                return coords;
            }, function () {
                pendingGeoPromise = null;
                return null;
            });
        }

        return pendingGeoPromise;
    }

    function ensureHiddenField(form, name, value) {
        if (!form) {
            return;
        }

        var input = form.querySelector('input[name="' + name + '"]');
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            form.appendChild(input);
        }

        input.value = value;
    }

    function bindForm(form) {
        if (!form || form.dataset.activityGeoBound === '1') {
            return;
        }

        form.dataset.activityGeoBound = '1';
        form.addEventListener('submit', function (event) {
            if (form.dataset.activityGeoSubmitting === '1') {
                return;
            }

            event.preventDefault();
            getGeoCoordinates().then(function (coords) {
                if (coords) {
                    ensureHiddenField(form, 'client_latitude', coords.latitude);
                    ensureHiddenField(form, 'client_longitude', coords.longitude);
                }

                form.dataset.activityGeoSubmitting = '1';
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            }).catch(function () {
                form.dataset.activityGeoSubmitting = '1';
                form.submit();
            });
        });
    }

    function bindForms() {
        document.querySelectorAll('form[action="/login"], form[action="/logout"]').forEach(bindForm);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            bindForms();
            getGeoCoordinates().catch(function () {
                return null;
            });
        });
    } else {
        bindForms();
        getGeoCoordinates().catch(function () {
            return null;
        });
    }

    window.familyTreeGeo = {
        getCoordinates: getGeoCoordinates,
    };
})();
