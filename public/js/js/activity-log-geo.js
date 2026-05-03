(function () {
    var forms = Array.prototype.slice.call(document.querySelectorAll("form"));
    if (!forms.length) {
        return;
    }

    var postForms = forms.filter(function (form) {
        var method = (form.getAttribute("method") || "get").toLowerCase();
        return method === "post";
    });

    if (!postForms.length) {
        return;
    }

    function ensureHiddenInput(form, name) {
        var input = form.querySelector('input[name="' + name + '"]');
        if (input) {
            return input;
        }

        input = document.createElement("input");
        input.type = "hidden";
        input.name = name;
        form.appendChild(input);
        return input;
    }

    var inputPairs = postForms.map(function (form) {
        return {
            lat: ensureHiddenInput(form, "activity_latitude"),
            lng: ensureHiddenInput(form, "activity_longitude")
        };
    });

    function setCoords(latitude, longitude) {
        var latText = String(latitude);
        var lngText = String(longitude);
        inputPairs.forEach(function (pair) {
            pair.lat.value = latText;
            pair.lng.value = lngText;
        });
    }

    if (!navigator.geolocation || !window.isSecureContext) {
        return;
    }

    var isLocating = false;
    var locationResolved = false;
    var submitQueue = [];

    function flushQueuedSubmits() {
        if (!submitQueue.length) {
            return;
        }

        var queued = submitQueue.slice();
        submitQueue = [];

        queued.forEach(function (form) {
            form.submit();
        });
    }

    function resolveLocationAndContinue() {
        locationResolved = true;
        isLocating = false;
        flushQueuedSubmits();
    }

    function requestLocation(onDone) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                setCoords(position.coords.latitude, position.coords.longitude);
                if (typeof onDone === "function") {
                    onDone();
                }
            },
            function () {
                // User can deny location; keep logging without coordinates.
                if (typeof onDone === "function") {
                    onDone();
                }
            },
            {
                enableHighAccuracy: false,
                timeout: 8000,
                maximumAge: 120000
            }
        );
    }

    var loginForm = document.querySelector('form[action="/login"]');
    if (loginForm) {
        loginForm.addEventListener("submit", function (event) {
            if (locationResolved) {
                return;
            }

            event.preventDefault();
            submitQueue.push(loginForm);

            if (isLocating) {
                return;
            }

            isLocating = true;
            requestLocation(resolveLocationAndContinue);
        });
    }

    isLocating = true;
    requestLocation(resolveLocationAndContinue);
})();
