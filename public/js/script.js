(function () {
    var pageIsFamilyTree = document.body.classList.contains("page-family-tree");
    if (!pageIsFamilyTree) {
        return;
    }

    var dropdowns = Array.prototype.slice.call(document.querySelectorAll(".menu-dropdown"));
    var addUserModal = document.getElementById("addUserModal");
    var openAddUserModal = document.getElementById("openAddUserModal");
    var closeAddUserModal = document.getElementById("closeAddUserModal");
    var cancelAddUserModal = document.getElementById("cancelAddUserModal");
    var importUserModal = document.getElementById("importUserModal");
    var openImportUserModal = document.getElementById("openImportUserModal");
    var closeImportUserModal = document.getElementById("closeImportUserModal");
    var cancelImportUserModal = document.getElementById("cancelImportUserModal");
    var newLevelSelect = document.getElementById("newLevel");
    var dynamicFields = document.getElementById("dynamicFields");
    var newRoleSelect = document.getElementById("newRole");
    var newRoleField = document.getElementById("newRoleField");
    var contactFields = document.getElementById("contactFields");
    var familyFields = document.getElementById("familyFields");
    var newNameInput = document.getElementById("newName");
    var newUsernameInput = document.getElementById("newUsername");
    var newEmailInput = document.getElementById("newEmail");
    var newPhoneInput = document.getElementById("newPhone");
    var familyGenderInput = document.getElementById("familyGender");
    var familyAddressInput = document.getElementById("familyAddress");
    var familyLifeStatusInput = document.getElementById("familyLifeStatus");
    var familyMaritalStatusInput = document.getElementById("familyMaritalStatus");
    var familyBirthdateInput = document.getElementById("familyBirthdate");
    var familyAgeInput = document.getElementById("familyAge");
    var familyBirthplaceInput = document.getElementById("familyBirthplace");
    var addUserForm = document.getElementById("addUserForm");
    var addUserAjaxErrors = document.getElementById("addUserAjaxErrors");
    var userTableBody = document.getElementById("userTableBody");
    var userPagination = document.getElementById("userPagination");
    var userTableCount = document.getElementById("userTableCount");
    var userSearchInput = document.getElementById("userSearchInput");
    var userRoleFilter = document.getElementById("userRoleFilter");
    var userDataTable = document.getElementById("userDataTable");
    var bulkUserActions = document.getElementById("bulkUserActions");
    var bulkSelectedCount = document.getElementById("bulkSelectedCount");
    var bulkDeleteForm = document.getElementById("bulkDeleteForm");
    var bulkDeleteBtn = document.getElementById("bulkDeleteBtn");
    var bulkSelectAllUsers = document.getElementById("bulkSelectAllUsers");
    var bulkDeleteHiddenInputs = document.getElementById("bulkDeleteHiddenInputs");
    var cancelBulkDeleteBtn = document.getElementById("cancelBulkDeleteBtn");
    var editUserModal = document.getElementById("editUserModal");
    var editUserForm = document.getElementById("editUserForm");
    var closeEditUserModal = document.getElementById("closeEditUserModal");
    var cancelEditUserModal = document.getElementById("cancelEditUserModal");
    var editUsername = document.getElementById("editUsername");
    var editName = document.getElementById("editName");
    var editEmail = document.getElementById("editEmail");
    var editPhone = document.getElementById("editPhone");
    var editLevel = document.getElementById("editLevel");
    var editRoleField = document.getElementById("editRoleField");
    var editRole = document.getElementById("editRole");
    var editFamilyFields = document.getElementById("editFamilyFields");
    var editGender = document.getElementById("editGender");
    var editLifeStatus = document.getElementById("editLifeStatus");
    var editMaritalStatus = document.getElementById("editMaritalStatus");
    var editBirthdate = document.getElementById("editBirthdate");
    var editBirthplace = document.getElementById("editBirthplace");
    var editAddress = document.getElementById("editAddress");
    var editJob = document.getElementById("editJob");
    var editEducationStatus = document.getElementById("editEducationStatus");
    var activityLogTableBody = document.getElementById("activityLogTableBody");
    var activityLogPagination = document.getElementById("activityLogPagination");
    var activityLogTableCount = document.getElementById("activityLogTableCount");
    var systemLogoInput = document.getElementById("systemLogoInput");
    var systemLogoPreview = document.getElementById("systemLogoPreview");
    var systemLogoPlaceholder = document.getElementById("systemLogoPlaceholder");
    var systemSettingsForm = document.getElementById("systemSettingsForm");
    var settingsAjaxAlert = document.getElementById("settingsAjaxAlert");
    var navbarWelcomeName = document.getElementById("navbarWelcomeName");
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.getAttribute("content") : "";
    var cards = Array.prototype.slice.call(document.querySelectorAll(".member-card"));
    var treeContainer = document.getElementById("treeScrollArea");
    var treeZoomStage = document.getElementById("treeZoomStage");
    var treeCanvas = document.getElementById("treeCanvas");
    var treeConnectorSvg = document.getElementById("treeConnectorSvg");
    var saveTreeImageBtn = document.getElementById("saveTreeImageBtn");
    var treeZoomInBtn = document.getElementById("treeZoomInBtn");
    var treeZoomOutBtn = document.getElementById("treeZoomOutBtn");
    var treeZoomValue = document.getElementById("treeZoomValue");
    var treeToggleTopBtn = document.getElementById("treeToggleTopBtn");
    var treeToggleBottomWrap = document.getElementById("treeToggleBottomWrap");
    var detailName = document.getElementById("detailName");
    var detailRole = document.getElementById("detailRole");
    var detailGender = document.getElementById("detailGender");
    var detailAge = document.getElementById("detailAge");
    var detailBirthdate = document.getElementById("detailBirthdate");
    var detailBirthplace = document.getElementById("detailBirthplace");
    var detailBloodType = document.getElementById("detailBloodType");
    var detailStatus = document.getElementById("detailStatus");
    var detailMaritalStatus = document.getElementById("detailMaritalStatus");
    var detailPhone = document.getElementById("detailPhone");
    var detailEmail = document.getElementById("detailEmail");
    var detailSocialMedia = document.getElementById("detailSocialMedia");
    var detailJob = document.getElementById("detailJob");
    var detailAddress = document.getElementById("detailAddress");
    var detailEducation = document.getElementById("detailEducation");
    var detailGeneration = document.getElementById("detailGeneration");
    var detailCard = document.getElementById("detailCard");
    var detailPhoto = document.getElementById("detailPhoto");
    var detailPhotoWrap = document.getElementById("detailPhotoWrap");
    var detailPhotoHint = document.getElementById("detailPhotoHint");
    var memberActionBlock = document.getElementById("memberActionBlock");
    var deletePartnerForm = document.getElementById("deletePartnerForm");
    var deleteChildForm = document.getElementById("deleteChildForm");
    var lifeStatusForm = document.getElementById("lifeStatusForm");
    var deletePartnerMemberIdInput = document.getElementById("deletePartnerMemberIdInput");
    var deleteChildMemberIdInput = document.getElementById("deleteChildMemberIdInput");
    var lifeStatusMemberIdInput = document.getElementById("lifeStatusMemberIdInput");
    var lifeStatusSelect = document.getElementById("lifeStatusSelect");
    var saveLifeStatusBtn = document.getElementById("saveLifeStatusBtn");
    var editProfileLink = document.getElementById("editProfileLink");
    var adminProfileForm = document.getElementById("adminProfileForm");
    var accountAdminAjaxAlert = document.getElementById("accountAdminAjaxAlert");
    var profileForm = document.getElementById("profileForm");
    var profileAjaxAlert = document.getElementById("profileAjaxAlert");
    var profileSaveSuccessModal = document.getElementById("profileSaveSuccessModal");
    var profileSaveSuccessText = document.getElementById("profileSaveSuccessText");
    var profileSaveSuccessOkBtn = document.getElementById("profileSaveSuccessOkBtn");
    var profileFaceErrorModal = document.getElementById("profileFaceErrorModal");
    var profileFaceErrorText = document.getElementById("profileFaceErrorText");
    var profileFaceErrorOkBtn = document.getElementById("profileFaceErrorOkBtn");
    var profilePanelBtn = document.getElementById("profilePanelBtn");
    var addMemberPanelBtn = document.getElementById("addMemberPanelBtn");
    var profilePanel = document.getElementById("profilePanel");
    var addMemberPanel = document.getElementById("addMemberPanel");
    var memberDetailBlock = document.getElementById("memberDetailBlock");
    var profilePictureInput = document.getElementById("profilePictureInput");
    var profilePictureFaceVerified = document.getElementById("profilePictureFaceVerified");
    var relationTypeInput = document.getElementById("relationTypeInput");
    var childParentingModeField = document.getElementById("childParentingModeField");
    var childParentingModeUpdateForm = document.getElementById("childParentingModeUpdateForm");
    var childParentingModeMemberIdInput = document.getElementById("childParentingModeMemberIdInput");
    var childParentingModeActionSelect = document.getElementById("childParentingModeActionSelect");
    var childParentingModeStatusText = document.getElementById("childParentingModeStatusText");
    var childParentingModeActionBtn = document.getElementById("childParentingModeActionBtn");
    var addMemberForm = addMemberPanel ? addMemberPanel.querySelector("form") : null;
    var isSuperadminUser = addMemberForm && (addMemberForm.getAttribute("data-is-superadmin") || "0") === "1";
    var addMemberCanUseCurrentPartner = addMemberForm && (addMemberForm.getAttribute("data-can-use-current-partner") || "0") === "1";
    var relatedToMemberDisplay = document.getElementById("relatedToMemberDisplay");
    var memberGenderSelectField = document.getElementById("memberGenderSelectField");
    var memberGenderPartnerInfo = document.getElementById("memberGenderPartnerInfo");
    var memberGenderSelect = document.getElementById("memberGenderSelect");
    var memberGenderInput = document.getElementById("memberGenderInput");
    var memberGenderPartnerDisplay = document.getElementById("memberGenderPartnerDisplay");
    var memberEmailField = document.getElementById("memberEmailField");
    var memberPhoneField = document.getElementById("memberPhoneField");
    var memberEmailInput = document.getElementById("memberEmail");
    var memberPhoneInput = document.getElementById("memberPhone");
    var memberAddressInput = document.getElementById("memberAddress");
    var memberAddressCountrySelect = document.getElementById("memberAddressCountry");
    var memberAddressProvinceField = document.getElementById("memberAddressProvinceField");
    var memberAddressProvinceSelect = document.getElementById("memberAddressProvince");
    var memberAddressCityField = document.getElementById("memberAddressCityField");
    var memberAddressCitySelect = document.getElementById("memberAddressCity");
    var memberAddressDistrictField = document.getElementById("memberAddressDistrictField");
    var memberAddressDistrictSelect = document.getElementById("memberAddressDistrict");
    var memberAddressDetailInput = document.getElementById("memberAddressDetail");
    var memberAddressCountryOldInput = document.getElementById("memberAddressCountryOld");
    var memberAddressProvinceOldInput = document.getElementById("memberAddressProvinceOld");
    var memberAddressCityOldInput = document.getElementById("memberAddressCityOld");
    var memberAddressDistrictOldInput = document.getElementById("memberAddressDistrictOld");
    var memberAddressDetailOldInput = document.getElementById("memberAddressDetailOld");
    var detailEditForm = document.getElementById("memberDetailEditForm");
    var detailEditAddressInput = document.getElementById("detailEditAddress");
    var detailEditAddressCountrySelect = document.getElementById("detailEditAddressCountry");
    var detailEditAddressCountryOldInput = document.getElementById("detailEditAddressCountryOld");
    var detailEditAddressProvinceField = document.getElementById("detailEditAddressProvinceField");
    var detailEditAddressProvinceSelect = document.getElementById("detailEditAddressProvince");
    var detailEditAddressProvinceOldInput = document.getElementById("detailEditAddressProvinceOld");
    var detailEditAddressCityField = document.getElementById("detailEditAddressCityField");
    var detailEditAddressCitySelect = document.getElementById("detailEditAddressCity");
    var detailEditAddressCityOldInput = document.getElementById("detailEditAddressCityOld");
    var detailEditAddressDistrictField = document.getElementById("detailEditAddressDistrictField");
    var detailEditAddressDistrictSelect = document.getElementById("detailEditAddressDistrict");
    var detailEditAddressDistrictOldInput = document.getElementById("detailEditAddressDistrictOld");
    var detailEditAddressDetailInput = document.getElementById("detailEditAddressDetail");
    var detailEditAddressDetailOldInput = document.getElementById("detailEditAddressDetailOld");
    var relationButtons = Array.prototype.slice.call(document.querySelectorAll(".relation-btn"));
    var treeSeeMoreButtons = Array.prototype.slice.call(document.querySelectorAll(".tree-see-more-btn"));
    var treeExpandButtons = Array.prototype.slice.call(document.querySelectorAll(".tree-expand-toggle"));
    var treeExpandAllBtn = document.getElementById("treeExpandAllBtn");
    var treeSummaryText = document.getElementById("treeSummaryText");
    var flashMessageModal = document.getElementById("flashMessageModal");
    var flashMessageOkBtn = document.getElementById("flashMessageOkBtn");
    var photoCropModal = document.getElementById("photoCropModal");
    var photoCropCanvas = document.getElementById("photoCropCanvas");
    var photoCropZoom = document.getElementById("photoCropZoom");
    var photoCropApplyBtn = document.getElementById("photoCropApplyBtn");
    var photoCropCancelBtn = document.getElementById("photoCropCancelBtn");
    var photoCropTitle = document.getElementById("photoCropTitle");
    var photoCropDescription = document.getElementById("photoCropDescription");

    var cropImage = null;
    var cropImageUrl = "";
    var cropScaleBase = 1;
    var cropZoomValue = 1;
    var cropOffsetX = 0;
    var cropOffsetY = 0;
    var cropFrameX = 8;
    var cropFrameY = 8;
    var cropFrameSize = 304;
    var cropFrameMoving = false;
    var cropResizing = false;
    var cropResizeHandle = "";
    var cropDragging = false;
    var cropStartX = 0;
    var cropStartY = 0;
    var cropStartCanvasX = 0;
    var cropStartCanvasY = 0;
    var cropStartOffsetX = 0;
    var cropStartOffsetY = 0;
    var cropStartFrameX = 0;
    var cropStartFrameY = 0;
    var cropStartFrameSize = 0;
    var CROP_FRAME_MARGIN = 8;
    var CROP_MIN_SIZE = 72;
    var CROP_HANDLE_DRAW_RADIUS = 6;
    var CROP_HANDLE_HIT_RADIUS = 14;
    var pendingCroppedPreviewUrl = "";
    var pendingSystemLogoPreviewUrl = "";
    var pendingSystemLogoFile = null;
    var activePhotoCropTarget = "";
    var activePhotoCropShape = "circle";
    var treeToggleRequestInFlight = false;
    var hasTreeMemberContext = false;
    var socialMediaPlatformMap = {
        instagram: { slug: "instagram", color: "E4405F", label: "Instagram" },
        facebook: { slug: "facebook", color: "1877F2", label: "Facebook" },
        x: { slug: "x", color: "000000", label: "X" },
        twitter: { slug: "x", color: "000000", label: "X" },
        tiktok: { slug: "tiktok", color: "000000", label: "TikTok" },
        linkedin: { slug: "linkedin", color: "0A66C2", label: "LinkedIn" },
        youtube: { slug: "youtube", color: "FF0000", label: "YouTube" },
        github: { slug: "github", color: "181717", label: "GitHub" },
        telegram: { slug: "telegram", color: "26A5E4", label: "Telegram" },
        whatsapp: { slug: "whatsapp", color: "25D366", label: "WhatsApp" },
        line: { slug: "line", color: "00C300", label: "LINE" },
        discord: { slug: "discord", color: "5865F2", label: "Discord" },
        threads: { slug: "threads", color: "000000", label: "Threads" },
        reddit: { slug: "reddit", color: "FF4500", label: "Reddit" },
        pinterest: { slug: "pinterest", color: "BD081C", label: "Pinterest" }
    };
    var treeConnectorFrameHandle = 0;
    var treeConnectorRetryCount = 0;
    var treeConnectorMaxRetries = 8;
    var recenterTreeViewport = function () {};
    var userBulkModeActive = false;
    var userBulkSelectedMap = {};
    var userBulkLongPressTimer = 0;
    var userBulkLongPressTriggered = false;
    var USER_BULK_LONG_PRESS_MS = 550;
    var bulkLongPressOverrideMs = userDataTable
        ? parseInt(userDataTable.getAttribute("data-bulk-long-press-ms") || "", 10)
        : NaN;
    if (!isNaN(bulkLongPressOverrideMs) && bulkLongPressOverrideMs >= 300) {
        USER_BULK_LONG_PRESS_MS = bulkLongPressOverrideMs;
    }
    var userSearchDebounceTimer = 0;
    var USER_SEARCH_DEBOUNCE_MS = 260;
    var userFetchRequestSerial = 0;
    var profileSaveSuccessModalCloseTimer = 0;
    var profileFaceErrorModalCloseTimer = 0;
    var faceApiScriptLoadPromise = null;
    var faceApiModelLoadPromise = null;
    var ADDRESS_REGION_DATA = {};
    var ADDRESS_INDONESIA_API_BASES = [
        "https://raw.githubusercontent.com/emsifa/api-wilayah-indonesia/master/api",
        "https://emsifa.github.io/api-wilayah-indonesia/api"
    ];
    var addressCountryFetchPromise = null;
    var addressCountryDataLoaded = false;
    var addressProvinceCityFetchPromises = {};
    var addressDistrictFetchPromises = {};
    var addressIndonesiaProvinceMapByName = {};
    var addressIndonesiaCityMapByProvinceName = {};
    var addressCascadeRequestSerial = 0;

    function sortAddressOptionLabels(options) {
        return (options || []).slice().sort(function (left, right) {
            return String(left || "").localeCompare(String(right || ""), undefined, { sensitivity: "base" });
        });
    }

    function isIndonesiaCountry(countryName) {
        return String(countryName || "").trim().toLowerCase() === "indonesia";
    }

    function fetchJsonWithFallback(urls, requestInit) {
        var queue = Array.isArray(urls) ? urls.slice() : [];
        var run = function (index) {
            if (index >= queue.length) {
                return Promise.reject(new Error("All endpoints failed."));
            }

            return fetch(queue[index], requestInit || null)
                .then(function (response) {
                    if (!response || !response.ok) {
                        throw new Error("HTTP " + (response ? response.status : 0));
                    }
                    return response.json();
                })
                .catch(function () {
                    return run(index + 1);
                });
        };

        return run(0);
    }

    function ensureAddressIndonesiaProvinceDataLoaded() {
        if (
            ADDRESS_REGION_DATA.Indonesia
            && typeof ADDRESS_REGION_DATA.Indonesia === "object"
            && Object.keys(ADDRESS_REGION_DATA.Indonesia).length > 0
        ) {
            return Promise.resolve(true);
        }

        var provinceUrls = ADDRESS_INDONESIA_API_BASES.map(function (baseUrl) {
            return baseUrl + "/provinces.json";
        });

        return fetchJsonWithFallback(provinceUrls).then(function (rows) {
            var provinces = Array.isArray(rows) ? rows : [];
            if (!provinces.length) {
                return false;
            }

            ADDRESS_REGION_DATA.Indonesia = {};
            addressIndonesiaProvinceMapByName = {};

            provinces.forEach(function (row) {
                var provinceName = String(row && row.name ? row.name : "").trim();
                var provinceId = String(row && row.id ? row.id : "").trim();
                if (provinceName === "" || provinceId === "") {
                    return;
                }

                ADDRESS_REGION_DATA.Indonesia[provinceName] = {};
                addressIndonesiaProvinceMapByName[provinceName] = provinceId;
            });

            return true;
        }).catch(function () {
            return false;
        });
    }

    function ensureAddressCountryDataLoaded() {
        if (addressCountryDataLoaded) {
            return Promise.resolve(true);
        }

        if (addressCountryFetchPromise) {
            return addressCountryFetchPromise;
        }

        var buildFallbackCountryMapFromRestCountries = function () {
            return fetchJsonWithFallback([
                "https://restcountries.com/v3.1/all?fields=name"
            ]).then(function (rows) {
                var countries = Array.isArray(rows) ? rows : [];
                if (!countries.length) {
                    return false;
                }

                countries.forEach(function (countryRow) {
                    var countryName = String(
                        countryRow
                        && countryRow.name
                        && countryRow.name.common
                            ? countryRow.name.common
                            : ""
                    ).trim();
                    if (countryName === "") {
                        return;
                    }
                    if (!ADDRESS_REGION_DATA[countryName]) {
                        ADDRESS_REGION_DATA[countryName] = {};
                    }
                    if (!ADDRESS_REGION_DATA[countryName][countryName]) {
                        ADDRESS_REGION_DATA[countryName][countryName] = {};
                    }
                });

                return true;
            }).catch(function () {
                return false;
            });
        };

        addressCountryFetchPromise = fetch("https://countriesnow.space/api/v0.1/countries/states")
            .then(function (response) {
                if (!response || !response.ok) {
                    throw new Error("Unable to load address countries.");
                }
                return response.json();
            })
            .then(function (payload) {
                var countries = payload && Array.isArray(payload.data) ? payload.data : [];
                if (!countries.length) {
                    throw new Error("Address country payload is empty.");
                }

                countries.forEach(function (countryRow) {
                    var countryName = String(countryRow && countryRow.name ? countryRow.name : "").trim();
                    if (countryName === "") {
                        return;
                    }

                    if (!ADDRESS_REGION_DATA[countryName] || typeof ADDRESS_REGION_DATA[countryName] !== "object") {
                        ADDRESS_REGION_DATA[countryName] = {};
                    }

                    var states = Array.isArray(countryRow.states) ? countryRow.states : [];
                    if (!states.length) {
                        if (
                            !ADDRESS_REGION_DATA[countryName][countryName]
                            || typeof ADDRESS_REGION_DATA[countryName][countryName] !== "object"
                        ) {
                            ADDRESS_REGION_DATA[countryName][countryName] = {};
                        }
                        return;
                    }

                    states.forEach(function (stateRow) {
                        var stateName = String(stateRow && stateRow.name ? stateRow.name : "").trim();
                        if (stateName === "") {
                            return;
                        }

                        if (
                            !ADDRESS_REGION_DATA[countryName][stateName]
                            || typeof ADDRESS_REGION_DATA[countryName][stateName] !== "object"
                        ) {
                            ADDRESS_REGION_DATA[countryName][stateName] = {};
                        }
                    });
                });

                return ensureAddressIndonesiaProvinceDataLoaded().then(function () {
                    addressCountryDataLoaded = true;
                    return true;
                });
            })
            .catch(function () {
                return buildFallbackCountryMapFromRestCountries().then(function (loaded) {
                    if (!loaded) {
                        return false;
                    }
                    return ensureAddressIndonesiaProvinceDataLoaded().then(function () {
                        addressCountryDataLoaded = true;
                        return true;
                    });
                });
            });

        return addressCountryFetchPromise;
    }

    function ensureAddressProvinceCityDataLoaded(countryName, provinceName) {
        var normalizedCountry = String(countryName || "").trim();
        var normalizedProvince = String(provinceName || "").trim();
        if (normalizedCountry === "" || normalizedProvince === "") {
            return Promise.resolve(false);
        }

        if (isIndonesiaCountry(normalizedCountry)) {
            if (
                ADDRESS_REGION_DATA[normalizedCountry]
                && ADDRESS_REGION_DATA[normalizedCountry][normalizedProvince]
                && Object.keys(ADDRESS_REGION_DATA[normalizedCountry][normalizedProvince]).length > 0
            ) {
                return Promise.resolve(true);
            }

            var indonesiaProvinceId = String(addressIndonesiaProvinceMapByName[normalizedProvince] || "").trim();
            if (indonesiaProvinceId === "") {
                return Promise.resolve(false);
            }

            var indonesiaCacheKey = normalizedCountry + "::" + normalizedProvince;
            if (addressProvinceCityFetchPromises[indonesiaCacheKey]) {
                return addressProvinceCityFetchPromises[indonesiaCacheKey];
            }

            var indonesiaCityUrls = ADDRESS_INDONESIA_API_BASES.map(function (baseUrl) {
                return baseUrl + "/regencies/" + indonesiaProvinceId + ".json";
            });

            addressProvinceCityFetchPromises[indonesiaCacheKey] = fetchJsonWithFallback(indonesiaCityUrls)
                .then(function (rows) {
                    var cities = Array.isArray(rows) ? rows : [];
                    if (!cities.length) {
                        return false;
                    }

                    if (!ADDRESS_REGION_DATA[normalizedCountry] || typeof ADDRESS_REGION_DATA[normalizedCountry] !== "object") {
                        ADDRESS_REGION_DATA[normalizedCountry] = {};
                    }
                    if (
                        !ADDRESS_REGION_DATA[normalizedCountry][normalizedProvince]
                        || typeof ADDRESS_REGION_DATA[normalizedCountry][normalizedProvince] !== "object"
                    ) {
                        ADDRESS_REGION_DATA[normalizedCountry][normalizedProvince] = {};
                    }

                    addressIndonesiaCityMapByProvinceName[normalizedProvince] = {};
                    cities.forEach(function (cityRow) {
                        var cityName = String(cityRow && cityRow.name ? cityRow.name : "").trim();
                        var cityId = String(cityRow && cityRow.id ? cityRow.id : "").trim();
                        if (cityName === "" || cityId === "") {
                            return;
                        }
                        addressIndonesiaCityMapByProvinceName[normalizedProvince][cityName] = cityId;
                        if (!Array.isArray(ADDRESS_REGION_DATA[normalizedCountry][normalizedProvince][cityName])) {
                            ADDRESS_REGION_DATA[normalizedCountry][normalizedProvince][cityName] = [];
                        }
                    });
                    return true;
                })
                .catch(function () {
                    return false;
                });

            return addressProvinceCityFetchPromises[indonesiaCacheKey];
        }

        if (
            ADDRESS_REGION_DATA[normalizedCountry]
            && ADDRESS_REGION_DATA[normalizedCountry][normalizedProvince]
            && Object.keys(ADDRESS_REGION_DATA[normalizedCountry][normalizedProvince]).length > 0
        ) {
            return Promise.resolve(true);
        }

        var cacheKey = normalizedCountry + "::" + normalizedProvince;
        if (addressProvinceCityFetchPromises[cacheKey]) {
            return addressProvinceCityFetchPromises[cacheKey];
        }

        var apiUrl = normalizedProvince === normalizedCountry
            ? "https://countriesnow.space/api/v0.1/countries/cities"
            : "https://countriesnow.space/api/v0.1/countries/state/cities";
        var requestBody = normalizedProvince === normalizedCountry
            ? { country: normalizedCountry }
            : { country: normalizedCountry, state: normalizedProvince };

        addressProvinceCityFetchPromises[cacheKey] = fetch(apiUrl, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(requestBody)
        })
            .then(function (response) {
                if (!response || !response.ok) {
                    throw new Error("Unable to load address cities.");
                }
                return response.json();
            })
            .then(function (payload) {
                var cities = payload && Array.isArray(payload.data) ? payload.data : [];
                if (!cities.length) {
                    return false;
                }

                if (!ADDRESS_REGION_DATA[normalizedCountry] || typeof ADDRESS_REGION_DATA[normalizedCountry] !== "object") {
                    ADDRESS_REGION_DATA[normalizedCountry] = {};
                }
                if (
                    !ADDRESS_REGION_DATA[normalizedCountry][normalizedProvince]
                    || typeof ADDRESS_REGION_DATA[normalizedCountry][normalizedProvince] !== "object"
                ) {
                    ADDRESS_REGION_DATA[normalizedCountry][normalizedProvince] = {};
                }

                cities.forEach(function (cityNameRaw) {
                    var cityName = String(cityNameRaw || "").trim();
                    if (cityName === "") {
                        return;
                    }
                    if (!Array.isArray(ADDRESS_REGION_DATA[normalizedCountry][normalizedProvince][cityName])) {
                        ADDRESS_REGION_DATA[normalizedCountry][normalizedProvince][cityName] = [];
                    }
                });

                return true;
            })
            .catch(function () {
                return false;
            });

        return addressProvinceCityFetchPromises[cacheKey];
    }

    function ensureAddressDistrictDataLoaded(countryName, provinceName, cityName) {
        var normalizedCountry = String(countryName || "").trim();
        var normalizedProvince = String(provinceName || "").trim();
        var normalizedCity = String(cityName || "").trim();
        if (!isIndonesiaCountry(normalizedCountry) || normalizedProvince === "" || normalizedCity === "") {
            return Promise.resolve(false);
        }

        var cityMap = addressIndonesiaCityMapByProvinceName[normalizedProvince] || {};
        var cityId = String(cityMap[normalizedCity] || "").trim();
        if (cityId === "") {
            return Promise.resolve(false);
        }

        var currentDistrictOptions = ADDRESS_REGION_DATA[normalizedCountry]
            && ADDRESS_REGION_DATA[normalizedCountry][normalizedProvince]
            ? ADDRESS_REGION_DATA[normalizedCountry][normalizedProvince][normalizedCity]
            : null;
        if (Array.isArray(currentDistrictOptions) && currentDistrictOptions.length > 0) {
            return Promise.resolve(true);
        }

        var cacheKey = normalizedCountry + "::" + normalizedProvince + "::" + normalizedCity;
        if (addressDistrictFetchPromises[cacheKey]) {
            return addressDistrictFetchPromises[cacheKey];
        }

        var districtUrls = ADDRESS_INDONESIA_API_BASES.map(function (baseUrl) {
            return baseUrl + "/districts/" + cityId + ".json";
        });

        addressDistrictFetchPromises[cacheKey] = fetchJsonWithFallback(districtUrls)
            .then(function (rows) {
                var districts = Array.isArray(rows) ? rows : [];
                if (!districts.length) {
                    return false;
                }

                var districtNames = districts
                    .map(function (row) {
                        return String(row && row.name ? row.name : "").trim();
                    })
                    .filter(function (name) {
                        return name !== "";
                    });

                if (!ADDRESS_REGION_DATA[normalizedCountry]) {
                    ADDRESS_REGION_DATA[normalizedCountry] = {};
                }
                if (!ADDRESS_REGION_DATA[normalizedCountry][normalizedProvince]) {
                    ADDRESS_REGION_DATA[normalizedCountry][normalizedProvince] = {};
                }
                ADDRESS_REGION_DATA[normalizedCountry][normalizedProvince][normalizedCity] = districtNames;
                return true;
            })
            .catch(function () {
                return false;
            });

        return addressDistrictFetchPromises[cacheKey];
    }

    function updateNavbarWelcomeName(nextName) {
        if (!navbarWelcomeName) {
            return;
        }

        var safeName = String(nextName || "").trim();
        if (safeName === "") {
            return;
        }

        navbarWelcomeName.textContent = safeName;
    }

    function openProfileSaveSuccessModal(message) {
        if (!profileSaveSuccessModal) {
            return;
        }

        if (profileSaveSuccessText) {
            profileSaveSuccessText.textContent = message || "Profile details updated successfully.";
        }

        if (profileSaveSuccessModalCloseTimer) {
            window.clearTimeout(profileSaveSuccessModalCloseTimer);
            profileSaveSuccessModalCloseTimer = 0;
        }

        profileSaveSuccessModal.classList.remove("hidden", "is-closing");
        void profileSaveSuccessModal.offsetWidth;
        profileSaveSuccessModal.classList.add("is-open");

        profileSaveSuccessModalCloseTimer = window.setTimeout(function () {
            closeProfileSaveSuccessModal();
        }, 2200);
    }

    function closeProfileSaveSuccessModal() {
        if (!profileSaveSuccessModal || profileSaveSuccessModal.classList.contains("hidden")) {
            return;
        }

        profileSaveSuccessModal.classList.remove("is-open");
        profileSaveSuccessModal.classList.add("is-closing");

        if (profileSaveSuccessModalCloseTimer) {
            window.clearTimeout(profileSaveSuccessModalCloseTimer);
        }

        profileSaveSuccessModalCloseTimer = window.setTimeout(function () {
            profileSaveSuccessModal.classList.add("hidden");
            profileSaveSuccessModal.classList.remove("is-closing");
            profileSaveSuccessModalCloseTimer = 0;
        }, 220);
    }

    function openProfileFaceErrorModal(message) {
        if (!profileFaceErrorModal) {
            return;
        }

        if (profileFaceErrorText) {
            profileFaceErrorText.textContent = message || "Profile picture must contain a clear human face.";
        }

        if (profileFaceErrorModalCloseTimer) {
            window.clearTimeout(profileFaceErrorModalCloseTimer);
            profileFaceErrorModalCloseTimer = 0;
        }

        profileFaceErrorModal.classList.remove("hidden", "is-closing");
        void profileFaceErrorModal.offsetWidth;
        profileFaceErrorModal.classList.add("is-open");
    }

    function setFormLoadingState(form, isLoading, loadingLabel) {
        if (!form) {
            return;
        }

        var submitButton = form.querySelector('button[type="submit"]');
        if (!submitButton) {
            return;
        }

        if (!submitButton.dataset.originalLabel) {
            submitButton.dataset.originalLabel = submitButton.textContent || "Save";
        }

        if (isLoading) {
            submitButton.disabled = true;
            submitButton.setAttribute("aria-busy", "true");
            submitButton.classList.add("is-loading");
            submitButton.innerHTML = '<span class="btn-loading-spinner" aria-hidden="true"></span><span class="btn-loading-text">' + (loadingLabel || "Saving...") + "</span>";
            return;
        }

        submitButton.disabled = false;
        submitButton.removeAttribute("aria-busy");
        submitButton.classList.remove("is-loading");
        submitButton.innerHTML = submitButton.dataset.originalLabel || "Save";
    }

    function closeProfileFaceErrorModal() {
        if (!profileFaceErrorModal || profileFaceErrorModal.classList.contains("hidden")) {
            return;
        }

        profileFaceErrorModal.classList.remove("is-open");
        profileFaceErrorModal.classList.add("is-closing");

        if (profileFaceErrorModalCloseTimer) {
            window.clearTimeout(profileFaceErrorModalCloseTimer);
        }

        profileFaceErrorModalCloseTimer = window.setTimeout(function () {
            profileFaceErrorModal.classList.add("hidden");
            profileFaceErrorModal.classList.remove("is-closing");
            profileFaceErrorModalCloseTimer = 0;
        }, 220);
    }

    if (profileSaveSuccessModal) {
        var autoOpenProfileSuccessMessage = String(
            profileSaveSuccessModal.getAttribute("data-auto-open-message") || ""
        ).trim();
        if (autoOpenProfileSuccessMessage !== "") {
            openProfileSaveSuccessModal(autoOpenProfileSuccessMessage);
        }
    }

    function refreshTreeDomState() {
        cards = Array.prototype.slice.call(document.querySelectorAll(".member-card"));
        treeContainer = document.getElementById("treeScrollArea");
        treeZoomStage = document.getElementById("treeZoomStage");
        treeCanvas = document.getElementById("treeCanvas");
        treeConnectorSvg = document.getElementById("treeConnectorSvg");
        treeSeeMoreButtons = Array.prototype.slice.call(document.querySelectorAll(".tree-see-more-btn"));
        treeExpandButtons = Array.prototype.slice.call(document.querySelectorAll(".tree-expand-toggle"));
        treeExpandAllBtn = document.getElementById("treeExpandAllBtn");
        treeSummaryText = document.getElementById("treeSummaryText");
        treeToggleTopBtn = document.getElementById("treeToggleTopBtn");
        treeToggleBottomWrap = document.getElementById("treeToggleBottomWrap");
        hasTreeMemberContext = Boolean(
            cards.length && detailName && detailRole && detailAge && detailStatus && detailPhoto
        );
    }

    function getTreeCssNumber(variableName, fallbackValue) {
        if (!treeCanvas || !window.getComputedStyle) {
            return fallbackValue;
        }

        var value = parseFloat(window.getComputedStyle(treeCanvas).getPropertyValue(variableName));
        return isNaN(value) ? fallbackValue : value;
    }

    function getDirectChildByClassName(element, className) {
        if (!element || !className) {
            return null;
        }

        var children = element.children;
        for (var i = 0; i < children.length; i += 1) {
            if (children[i].classList && children[i].classList.contains(className)) {
                return children[i];
            }
        }

        return null;
    }

    function getDirectChildrenByTagName(element, tagName) {
        var matched = [];
        if (!element || !tagName) {
            return matched;
        }

        var normalizedTag = tagName.toUpperCase();
        var children = element.children;
        for (var i = 0; i < children.length; i += 1) {
            if (children[i].tagName === normalizedTag) {
                matched.push(children[i]);
            }
        }

        return matched;
    }

    function getDirectMemberCards(rowElement) {
        var cardsInRow = [];
        if (!rowElement) {
            return cardsInRow;
        }

        var rowChildren = rowElement.children;
        for (var i = 0; i < rowChildren.length; i += 1) {
            if (rowChildren[i].classList && rowChildren[i].classList.contains("member-card")) {
                cardsInRow.push(rowChildren[i]);
            }
        }

        return cardsInRow;
    }

    function getVisibleChildGroup(nodeElement) {
        if (!nodeElement) {
            return null;
        }

        var nodeChildren = nodeElement.children;
        for (var i = 0; i < nodeChildren.length; i += 1) {
            var child = nodeChildren[i];
            if (child.tagName === "UL" && child.classList && child.classList.contains("child-group")) {
                return child;
            }

            if (child.classList && child.classList.contains("tree-extra-children") && !child.classList.contains("hidden")) {
                var nestedChildren = child.children;
                for (var j = 0; j < nestedChildren.length; j += 1) {
                    var nestedChild = nestedChildren[j];
                    if (nestedChild.tagName === "UL" && nestedChild.classList && nestedChild.classList.contains("child-group")) {
                        return nestedChild;
                    }
                }
            }
        }

        return null;
    }

    function getOffsetWithinTree(element) {
        if (!element || !treeCanvas) {
            return null;
        }

        var current = element;
        var left = 0;
        var top = 0;

        while (current && current !== treeCanvas) {
            left += current.offsetLeft;
            top += current.offsetTop;
            current = current.offsetParent;
        }

        if (current !== treeCanvas) {
            return null;
        }

        return { left: left, top: top };
    }

    function addTreeConnectorLine(x1, y1, x2, y2) {
        if (!treeConnectorSvg) {
            return;
        }

        var isHorizontal = Math.abs(y2 - y1) < 0.1;
        var isVertical = Math.abs(x2 - x1) < 0.1;

        if (isHorizontal && Math.abs(x2 - x1) < 0.1) {
            return;
        }
        if (isVertical && Math.abs(y2 - y1) < 0.1) {
            return;
        }

        var line = document.createElementNS("http://www.w3.org/2000/svg", "line");
        line.setAttribute("class", "tree-connector-line");
        line.setAttribute("x1", String(Math.round(x1 * 10) / 10));
        line.setAttribute("y1", String(Math.round(y1 * 10) / 10));
        line.setAttribute("x2", String(Math.round(x2 * 10) / 10));
        line.setAttribute("y2", String(Math.round(y2 * 10) / 10));
        treeConnectorSvg.appendChild(line);
    }

    function drawParentToChildrenGroup(anchorX, anchorY, childEntries, linkHeight, options) {
        if (!childEntries.length) {
            return null;
        }

        var config = options || {};

        function clampJunctionY(desiredY, minChildTop) {
            var minimumJunctionY = anchorY + Math.max(14, linkHeight * 0.35);
            if (typeof config.minJunctionY === "number") {
                minimumJunctionY = Math.max(minimumJunctionY, config.minJunctionY);
            }

            var maximumJunctionY = minChildTop - 6;
            if (typeof config.maxJunctionY === "number") {
                maximumJunctionY = Math.min(maximumJunctionY, config.maxJunctionY);
            }

            if (maximumJunctionY < minimumJunctionY) {
                maximumJunctionY = minimumJunctionY;
            }

            var junctionY = Math.max(desiredY, minimumJunctionY);
            junctionY = Math.min(junctionY, maximumJunctionY);

            return junctionY;
        }

        if (childEntries.length === 1) {
            var onlyChild = childEntries[0];
            if (Math.abs(anchorX - onlyChild.x) < 0.1) {
                addTreeConnectorLine(anchorX, anchorY, onlyChild.x, onlyChild.top);
                return { junctionY: null };
            }

            var singleDesiredY = onlyChild.top - linkHeight;
            var singleJunctionY = clampJunctionY(singleDesiredY, onlyChild.top);

            addTreeConnectorLine(anchorX, anchorY, anchorX, singleJunctionY);
            addTreeConnectorLine(anchorX, singleJunctionY, onlyChild.x, singleJunctionY);
            addTreeConnectorLine(onlyChild.x, singleJunctionY, onlyChild.x, onlyChild.top);
            return { junctionY: singleJunctionY };
        }

        var minChildX = Infinity;
        var maxChildX = -Infinity;
        var minChildTop = Infinity;

        childEntries.forEach(function (entry) {
            minChildX = Math.min(minChildX, entry.x);
            maxChildX = Math.max(maxChildX, entry.x);
            minChildTop = Math.min(minChildTop, entry.top);
        });

        var desiredJunctionY = minChildTop - linkHeight;
        var junctionY = clampJunctionY(desiredJunctionY, minChildTop);

        addTreeConnectorLine(anchorX, anchorY, anchorX, junctionY);
        addTreeConnectorLine(Math.min(minChildX, anchorX), junctionY, Math.max(maxChildX, anchorX), junctionY);

        childEntries.forEach(function (entry) {
            addTreeConnectorLine(entry.x, junctionY, entry.x, entry.top);
        });

        return { junctionY: junctionY };
    }

    function bindTreeConnectorImageSync() {
        if (!treeCanvas) {
            return;
        }

        var photos = treeCanvas.querySelectorAll(".member-photo");
        Array.prototype.forEach.call(photos, function (photo) {
            if (photo.dataset.connectorBound === "1") {
                return;
            }

            photo.dataset.connectorBound = "1";
            photo.addEventListener("load", scheduleTreeConnectorDraw);
            photo.addEventListener("error", scheduleTreeConnectorDraw);
        });
    }

    function drawTreeConnectors() {
        if (!treeCanvas) {
            return;
        }

        treeConnectorSvg = document.getElementById("treeConnectorSvg");
        if (!treeConnectorSvg) {
            return;
        }

        while (treeConnectorSvg.firstChild) {
            treeConnectorSvg.removeChild(treeConnectorSvg.firstChild);
        }

        var canvasWidth = Math.ceil(treeCanvas.scrollWidth || treeCanvas.offsetWidth || 0);
        var canvasHeight = Math.ceil(treeCanvas.scrollHeight || treeCanvas.offsetHeight || 0);
        if (!canvasWidth || !canvasHeight) {
            if (treeConnectorRetryCount < treeConnectorMaxRetries) {
                treeConnectorRetryCount += 1;
                scheduleTreeConnectorDraw();
            }
            return;
        }

        treeConnectorRetryCount = 0;

        treeConnectorSvg.setAttribute("width", String(canvasWidth));
        treeConnectorSvg.setAttribute("height", String(canvasHeight));
        treeConnectorSvg.setAttribute("viewBox", "0 0 " + canvasWidth + " " + canvasHeight);

        var treeLinkHeight = getTreeCssNumber("--tree-link-height", 38);
        var partnerLinkY = getTreeCssNumber("--partner-link-y", 92);
        var allNodes = treeCanvas.querySelectorAll("li");

        Array.prototype.forEach.call(allNodes, function (nodeElement) {
            if (!nodeElement || nodeElement.offsetParent === null) {
                return;
            }

            var partnerRow = getDirectChildByClassName(nodeElement, "partner-row");
            if (!partnerRow || partnerRow.offsetParent === null) {
                return;
            }

            var rowCards = getDirectMemberCards(partnerRow);
            if (!rowCards.length) {
                return;
            }

            var rowCardAnchors = [];
            rowCards.forEach(function (rowCard) {
                var rowCardOffset = getOffsetWithinTree(rowCard);
                if (!rowCardOffset) {
                    return;
                }

                var rowCardMemberId = parseInt(rowCard.getAttribute("data-memberid") || "0", 10);
                rowCardAnchors.push({
                    memberId: isNaN(rowCardMemberId) ? 0 : rowCardMemberId,
                    centerX: rowCardOffset.left + (rowCard.offsetWidth / 2),
                    bottomY: rowCardOffset.top + rowCard.offsetHeight
                });
            });

            if (!rowCardAnchors.length) {
                return;
            }

            var primaryAnchor = rowCardAnchors[0];
            var parentAnchorX = primaryAnchor.centerX;
            var parentAnchorY = primaryAnchor.bottomY;

            if (rowCardAnchors.length > 1) {
                var firstAnchor = rowCardAnchors[0];
                var lastAnchor = rowCardAnchors[rowCardAnchors.length - 1];
                var rowOffset = getOffsetWithinTree(partnerRow);
                if (!rowOffset) {
                    return;
                }

                var marriageY = rowOffset.top + partnerLinkY;

                addTreeConnectorLine(firstAnchor.centerX, marriageY, lastAnchor.centerX, marriageY);
                parentAnchorX = (firstAnchor.centerX + lastAnchor.centerX) / 2;
                parentAnchorY = marriageY;
            }

            var childGroup = getVisibleChildGroup(nodeElement);
            if (!childGroup || childGroup.offsetParent === null) {
                return;
            }

            var childNodes = getDirectChildrenByTagName(childGroup, "li");
            if (!childNodes.length) {
                return;
            }

            var sharedChildren = [];
            var singleParentChildrenByAnchor = {};

            childNodes.forEach(function (childNode) {
                if (!childNode || childNode.offsetParent === null) {
                    return;
                }

                var childPartnerRow = getDirectChildByClassName(childNode, "partner-row");
                if (!childPartnerRow || childPartnerRow.offsetParent === null) {
                    return;
                }

                var childCards = getDirectMemberCards(childPartnerRow);
                if (!childCards.length) {
                    return;
                }

                var childPrimaryCard = childCards[0];
                var childPrimaryOffset = getOffsetWithinTree(childPrimaryCard);
                if (!childPrimaryOffset) {
                    return;
                }

                var childEntry = {
                    x: childPrimaryOffset.left + (childPrimaryCard.offsetWidth / 2),
                    top: childPrimaryOffset.top
                };

                if (childNode.classList.contains("single-parent-child") && rowCards.length > 1) {
                    var requestedAnchorMemberId = parseInt(
                        childNode.getAttribute("data-single-parent-anchor-memberid") || "0",
                        10
                    );
                    if (isNaN(requestedAnchorMemberId)) {
                        requestedAnchorMemberId = 0;
                    }

                    var bestAnchorIndex = -1;
                    if (requestedAnchorMemberId !== 0) {
                        for (var anchorIndex = 0; anchorIndex < rowCardAnchors.length; anchorIndex += 1) {
                            if (rowCardAnchors[anchorIndex].memberId === requestedAnchorMemberId) {
                                bestAnchorIndex = anchorIndex;
                                break;
                            }
                        }
                    }

                    if (bestAnchorIndex < 0) {
                        var minDistance = Infinity;
                        for (var nearestIndex = 0; nearestIndex < rowCardAnchors.length; nearestIndex += 1) {
                            var distance = Math.abs(childEntry.x - rowCardAnchors[nearestIndex].centerX);
                            if (distance < minDistance) {
                                minDistance = distance;
                                bestAnchorIndex = nearestIndex;
                            }
                        }
                    }

                    var anchorKey = String(bestAnchorIndex < 0 ? 0 : bestAnchorIndex);
                    if (!singleParentChildrenByAnchor[anchorKey]) {
                        singleParentChildrenByAnchor[anchorKey] = [];
                    }
                    singleParentChildrenByAnchor[anchorKey].push(childEntry);
                } else {
                    sharedChildren.push(childEntry);
                }
            });

            var sharedConnectorInfo = drawParentToChildrenGroup(
                parentAnchorX,
                parentAnchorY,
                sharedChildren,
                treeLinkHeight
            );
            var sharedJunctionY = sharedConnectorInfo && typeof sharedConnectorInfo.junctionY === "number"
                ? sharedConnectorInfo.junctionY
                : null;
            var singleParentLaneGap = Math.max(10, Math.round(treeLinkHeight * 0.35));
            var singleParentAnchorKeys = Object.keys(singleParentChildrenByAnchor).sort(function (left, right) {
                return parseInt(left, 10) - parseInt(right, 10);
            });

            singleParentAnchorKeys.forEach(function (anchorKey, laneIndex) {
                var anchorIndex = parseInt(anchorKey, 10);
                if (isNaN(anchorIndex) || anchorIndex < 0 || anchorIndex >= rowCardAnchors.length) {
                    return;
                }

                var singleParentChildren = singleParentChildrenByAnchor[anchorKey];
                if (!singleParentChildren || !singleParentChildren.length) {
                    return;
                }

                var singleParentOptions = null;
                if (sharedJunctionY !== null) {
                    singleParentOptions = {
                        maxJunctionY: sharedJunctionY - (singleParentLaneGap * (laneIndex + 1))
                    };
                }

                drawParentToChildrenGroup(
                    rowCardAnchors[anchorIndex].centerX,
                    parentAnchorY,
                    singleParentChildren,
                    treeLinkHeight,
                    singleParentOptions
                );
            });
        });
    }

    function scheduleTreeConnectorDraw() {
        if (!treeCanvas) {
            return;
        }

        if (treeConnectorFrameHandle) {
            return;
        }

        treeConnectorFrameHandle = window.requestAnimationFrame(function () {
            treeConnectorFrameHandle = 0;
            drawTreeConnectors();
        });
    }

    function closeFlashMessageModal() {
        if (!flashMessageModal || flashMessageModal.classList.contains("is-closing")) {
            return;
        }

        flashMessageModal.classList.add("is-closing");
        window.setTimeout(function () {
            flashMessageModal.classList.remove("is-open", "is-closing");
            flashMessageModal.classList.add("hidden");
        }, 220);
    }

    function clearPendingProfilePhotoPreview() {
        if (cropImageUrl) {
            URL.revokeObjectURL(cropImageUrl);
            cropImageUrl = "";
        }
    }

    function clearPendingCroppedPreview() {
        if (pendingCroppedPreviewUrl) {
            URL.revokeObjectURL(pendingCroppedPreviewUrl);
            pendingCroppedPreviewUrl = "";
        }
    }

    function clearPendingSystemLogoPreview() {
        if (pendingSystemLogoPreviewUrl) {
            URL.revokeObjectURL(pendingSystemLogoPreviewUrl);
            pendingSystemLogoPreviewUrl = "";
        }
    }

    if (flashMessageModal && flashMessageOkBtn) {
        flashMessageOkBtn.addEventListener("click", closeFlashMessageModal);
    }

    function closePhotoCropModal() {
        if (!photoCropModal || photoCropModal.classList.contains("is-closing")) {
            return;
        }

        cropDragging = false;
        cropFrameMoving = false;
        cropResizing = false;
        cropResizeHandle = "";
        if (photoCropCanvas) {
            photoCropCanvas.classList.remove("is-dragging");
            photoCropCanvas.style.cursor = "grab";
        }

        photoCropModal.classList.add("is-closing");
        window.setTimeout(function () {
            photoCropModal.classList.remove("is-open", "is-closing");
            photoCropModal.classList.add("hidden");
            activePhotoCropTarget = "";
            activePhotoCropShape = "circle";
        }, 220);
    }

    function setInputFile(inputElement, file) {
        if (!inputElement || !file) {
            return;
        }

        var transfer = new DataTransfer();
        transfer.items.add(file);
        inputElement.files = transfer.files;
    }

    function setProfilePictureFile(file) {
        setInputFile(profilePictureInput, file);
        setProfilePictureFaceVerified(false);
    }

    function setSystemLogoFile(file) {
        setInputFile(systemLogoInput, file);
    }

    function setProfilePictureFaceVerified(value) {
        if (!profilePictureFaceVerified) {
            return;
        }

        profilePictureFaceVerified.value = value ? "1" : "0";
    }

    function clampNumber(value, min, max) {
        return Math.min(max, Math.max(min, value));
    }

    function getScaledCropImageDimensions() {
        if (!cropImage) {
            return { width: 0, height: 0 };
        }

        return {
            width: cropImage.width * cropScaleBase * cropZoomValue,
            height: cropImage.height * cropScaleBase * cropZoomValue
        };
    }

    function getCropMaxSize() {
        if (!photoCropCanvas) {
            return 0;
        }

        var canvasLimit = Math.max(0, photoCropCanvas.width - CROP_FRAME_MARGIN * 2);
        if (!cropImage) {
            return canvasLimit;
        }

        var scaled = getScaledCropImageDimensions();
        return Math.max(0, Math.min(canvasLimit, scaled.width, scaled.height));
    }

    function getCropMinSize() {
        var maxSize = getCropMaxSize();
        if (maxSize <= 0) {
            return 0;
        }
        return Math.min(CROP_MIN_SIZE, maxSize);
    }

    function getCropHandlePositions() {
        return {
            nw: { x: cropFrameX, y: cropFrameY },
            ne: { x: cropFrameX + cropFrameSize, y: cropFrameY },
            se: { x: cropFrameX + cropFrameSize, y: cropFrameY + cropFrameSize },
            sw: { x: cropFrameX, y: cropFrameY + cropFrameSize }
        };
    }

    function getCropHandleAtPoint(pointX, pointY) {
        if (activePhotoCropShape !== "square") {
            return "";
        }

        var handles = getCropHandlePositions();
        var keys = ["nw", "ne", "se", "sw"];
        var index = 0;

        for (index = 0; index < keys.length; index += 1) {
            var key = keys[index];
            var dx = pointX - handles[key].x;
            var dy = pointY - handles[key].y;
            if (Math.sqrt(dx * dx + dy * dy) <= CROP_HANDLE_HIT_RADIUS) {
                return key;
            }
        }

        return "";
    }

    function isPointInsideCropFrame(pointX, pointY) {
        if (activePhotoCropShape !== "square") {
            return false;
        }

        return (
            pointX >= cropFrameX &&
            pointX <= cropFrameX + cropFrameSize &&
            pointY >= cropFrameY &&
            pointY <= cropFrameY + cropFrameSize
        );
    }

    function clampCropFramePosition() {
        if (!photoCropCanvas || activePhotoCropShape !== "square") {
            return;
        }

        var maxX = photoCropCanvas.width - CROP_FRAME_MARGIN - cropFrameSize;
        var maxY = photoCropCanvas.height - CROP_FRAME_MARGIN - cropFrameSize;
        cropFrameX = clampNumber(cropFrameX, CROP_FRAME_MARGIN, maxX);
        cropFrameY = clampNumber(cropFrameY, CROP_FRAME_MARGIN, maxY);
    }

    function resolveResizeCursor(handle) {
        if (handle === "nw" || handle === "se") {
            return "nwse-resize";
        }
        if (handle === "ne" || handle === "sw") {
            return "nesw-resize";
        }
        return "grab";
    }

    function resizeSquareCropFrame(pointerX, pointerY) {
        if (!photoCropCanvas || activePhotoCropShape !== "square" || !cropResizeHandle) {
            return;
        }

        var minSize = getCropMinSize();
        var maxSize = getCropMaxSize();
        if (maxSize <= 0) {
            return;
        }

        minSize = Math.min(minSize, maxSize);

        var canvasSize = photoCropCanvas.width;
        var anchorX = 0;
        var anchorY = 0;
        var sizeCandidate = 0;
        var canvasMaxSize = 0;
        var finalMaxSize = 0;
        var nextSize = 0;

        if (cropResizeHandle === "nw") {
            anchorX = cropStartFrameX + cropStartFrameSize;
            anchorY = cropStartFrameY + cropStartFrameSize;
            sizeCandidate = Math.min(anchorX - pointerX, anchorY - pointerY);
            canvasMaxSize = Math.min(anchorX - CROP_FRAME_MARGIN, anchorY - CROP_FRAME_MARGIN);
            finalMaxSize = Math.min(maxSize, canvasMaxSize);
            finalMaxSize = Math.max(minSize, finalMaxSize);
            nextSize = clampNumber(sizeCandidate, minSize, finalMaxSize);
            cropFrameX = anchorX - nextSize;
            cropFrameY = anchorY - nextSize;
            cropFrameSize = nextSize;
            return;
        }

        if (cropResizeHandle === "ne") {
            anchorX = cropStartFrameX;
            anchorY = cropStartFrameY + cropStartFrameSize;
            sizeCandidate = Math.min(pointerX - anchorX, anchorY - pointerY);
            canvasMaxSize = Math.min(canvasSize - CROP_FRAME_MARGIN - anchorX, anchorY - CROP_FRAME_MARGIN);
            finalMaxSize = Math.min(maxSize, canvasMaxSize);
            finalMaxSize = Math.max(minSize, finalMaxSize);
            nextSize = clampNumber(sizeCandidate, minSize, finalMaxSize);
            cropFrameX = anchorX;
            cropFrameY = anchorY - nextSize;
            cropFrameSize = nextSize;
            return;
        }

        if (cropResizeHandle === "se") {
            anchorX = cropStartFrameX;
            anchorY = cropStartFrameY;
            sizeCandidate = Math.min(pointerX - anchorX, pointerY - anchorY);
            canvasMaxSize = Math.min(canvasSize - CROP_FRAME_MARGIN - anchorX, canvasSize - CROP_FRAME_MARGIN - anchorY);
            finalMaxSize = Math.min(maxSize, canvasMaxSize);
            finalMaxSize = Math.max(minSize, finalMaxSize);
            nextSize = clampNumber(sizeCandidate, minSize, finalMaxSize);
            cropFrameX = anchorX;
            cropFrameY = anchorY;
            cropFrameSize = nextSize;
            return;
        }

        if (cropResizeHandle === "sw") {
            anchorX = cropStartFrameX + cropStartFrameSize;
            anchorY = cropStartFrameY;
            sizeCandidate = Math.min(anchorX - pointerX, pointerY - anchorY);
            canvasMaxSize = Math.min(anchorX - CROP_FRAME_MARGIN, canvasSize - CROP_FRAME_MARGIN - anchorY);
            finalMaxSize = Math.min(maxSize, canvasMaxSize);
            finalMaxSize = Math.max(minSize, finalMaxSize);
            nextSize = clampNumber(sizeCandidate, minSize, finalMaxSize);
            cropFrameX = anchorX - nextSize;
            cropFrameY = anchorY;
            cropFrameSize = nextSize;
        }
    }

    function clampCropOffsets() {
        if (!photoCropCanvas || !cropImage) {
            return;
        }

        var canvasSize = photoCropCanvas.width;
        var scaled = getScaledCropImageDimensions();
        var scaledWidth = scaled.width;
        var scaledHeight = scaled.height;

        if (activePhotoCropShape === "square") {
            if (scaledWidth <= cropFrameSize) {
                cropOffsetX = cropFrameX + (cropFrameSize - scaledWidth) / 2;
            } else {
                cropOffsetX = Math.min(cropFrameX, Math.max(cropFrameX + cropFrameSize - scaledWidth, cropOffsetX));
            }

            if (scaledHeight <= cropFrameSize) {
                cropOffsetY = cropFrameY + (cropFrameSize - scaledHeight) / 2;
            } else {
                cropOffsetY = Math.min(cropFrameY, Math.max(cropFrameY + cropFrameSize - scaledHeight, cropOffsetY));
            }
            return;
        }

        if (scaledWidth <= canvasSize) {
            cropOffsetX = (canvasSize - scaledWidth) / 2;
        } else {
            cropOffsetX = Math.min(0, Math.max(canvasSize - scaledWidth, cropOffsetX));
        }

        if (scaledHeight <= canvasSize) {
            cropOffsetY = (canvasSize - scaledHeight) / 2;
        } else {
            cropOffsetY = Math.min(0, Math.max(canvasSize - scaledHeight, cropOffsetY));
        }
    }

    function renderCropCanvas() {
        if (!photoCropCanvas || !cropImage) {
            return;
        }

        var ctx = photoCropCanvas.getContext("2d");
        var size = photoCropCanvas.width;
        var scaled = getScaledCropImageDimensions();
        var scaledWidth = scaled.width;
        var scaledHeight = scaled.height;
        var radius = size / 2 - CROP_FRAME_MARGIN;
        var centerX = size / 2;
        var centerY = size / 2;

        ctx.clearRect(0, 0, size, size);
        ctx.drawImage(cropImage, cropOffsetX, cropOffsetY, scaledWidth, scaledHeight);

        // Keep crop area bright and darken only the outside area.
        ctx.save();
        ctx.fillStyle = "rgba(0, 0, 0, 0.3)";
        ctx.beginPath();
        ctx.rect(0, 0, size, size);
        if (activePhotoCropShape === "square") {
            ctx.rect(cropFrameX, cropFrameY, cropFrameSize, cropFrameSize);
        } else {
            ctx.arc(centerX, centerY, radius, 0, Math.PI * 2, true);
        }
        ctx.fill("evenodd");
        ctx.restore();

        ctx.beginPath();
        if (activePhotoCropShape === "square") {
            ctx.rect(cropFrameX, cropFrameY, cropFrameSize, cropFrameSize);
        } else {
            ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
        }
        ctx.strokeStyle = "#ffffff";
        ctx.lineWidth = 2;
        ctx.stroke();

        if (activePhotoCropShape === "square") {
            var handles = getCropHandlePositions();
            var handleKeys = ["nw", "ne", "se", "sw"];
            var handleIndex = 0;

            ctx.lineWidth = 2;
            for (handleIndex = 0; handleIndex < handleKeys.length; handleIndex += 1) {
                var handleKey = handleKeys[handleIndex];
                var handle = handles[handleKey];
                ctx.beginPath();
                ctx.arc(handle.x, handle.y, CROP_HANDLE_DRAW_RADIUS, 0, Math.PI * 2);
                ctx.fillStyle = handleKey === cropResizeHandle ? "#1f9ad6" : "#ffffff";
                ctx.fill();
                ctx.strokeStyle = "#1f9ad6";
                ctx.stroke();
            }
        }
    }

    function openPhotoCropModal(file, options) {
        if (!photoCropModal || !photoCropCanvas || !file) {
            return;
        }

        var config = options || {};
        activePhotoCropTarget = String(config.target || "profile");
        activePhotoCropShape = config.shape === "square" ? "square" : "circle";
        if (photoCropTitle) {
            photoCropTitle.textContent = String(config.title || "Crop Photo");
        }
        if (photoCropDescription) {
            photoCropDescription.textContent = String(config.description || "Move and zoom to adjust your photo.");
        }

        clearPendingProfilePhotoPreview();
        cropImage = new Image();
        cropImageUrl = URL.createObjectURL(file);
        cropImage.onload = function () {
            var size = photoCropCanvas.width;
            var fitFullImage = activePhotoCropTarget === "system-logo";
            cropScaleBase = fitFullImage
                ? Math.min(size / cropImage.width, size / cropImage.height)
                : Math.max(size / cropImage.width, size / cropImage.height);
            cropZoomValue = 1;
            if (photoCropZoom) {
                photoCropZoom.value = "1";
            }

            if (activePhotoCropShape === "square") {
                var maxSize = getCropMaxSize();
                var nextFrameSize = maxSize > 0 ? maxSize : Math.max(0, size - CROP_FRAME_MARGIN * 2);
                cropFrameSize = nextFrameSize;
                cropFrameX = (size - cropFrameSize) / 2;
                cropFrameY = (size - cropFrameSize) / 2;
            } else {
                cropFrameX = CROP_FRAME_MARGIN;
                cropFrameY = CROP_FRAME_MARGIN;
                cropFrameSize = size - CROP_FRAME_MARGIN * 2;
            }

            cropOffsetX = (size - cropImage.width * cropScaleBase) / 2;
            cropOffsetY = (size - cropImage.height * cropScaleBase) / 2;
            clampCropOffsets();
            renderCropCanvas();
            photoCropModal.classList.remove("hidden", "is-closing");
            photoCropModal.classList.add("is-open");
            photoCropCanvas.style.cursor = "grab";
        };
        cropImage.src = cropImageUrl;
    }

    function applyCropSelection() {
        if (!photoCropCanvas || !cropImage) {
            return;
        }

        var outputSize = 512;
        var isSystemLogoCrop = activePhotoCropTarget === "system-logo";
        var outputMimeType = isSystemLogoCrop ? "image/png" : "image/jpeg";
        var outputFileName = isSystemLogoCrop ? "system-logo-cropped.png" : "profile-cropped.jpg";
        var exportCanvas = document.createElement("canvas");
        exportCanvas.width = outputSize;
        exportCanvas.height = outputSize;

        var exportCtx = exportCanvas.getContext("2d");
        var sourceSize = photoCropCanvas.width;
        var sourceX = 0;
        var sourceY = 0;

        if (activePhotoCropShape === "square") {
            sourceSize = cropFrameSize;
            sourceX = cropFrameX;
            sourceY = cropFrameY;
        }

        if (!sourceSize || sourceSize <= 0) {
            sourceSize = photoCropCanvas.width;
        }

        var drawScale = outputSize / sourceSize;
        var scaledWidth = cropImage.width * cropScaleBase * cropZoomValue * drawScale;
        var scaledHeight = cropImage.height * cropScaleBase * cropZoomValue * drawScale;
        var drawX = (cropOffsetX - sourceX) * drawScale;
        var drawY = (cropOffsetY - sourceY) * drawScale;

        if (isSystemLogoCrop) {
            exportCtx.fillStyle = "#ffffff";
            exportCtx.fillRect(0, 0, outputSize, outputSize);
        }

        exportCtx.drawImage(cropImage, drawX, drawY, scaledWidth, scaledHeight);
        exportCanvas.toBlob(function (blob) {
            if (!blob) {
                return;
            }

            var croppedFile = new File([blob], outputFileName, { type: outputMimeType });
            clearPendingProfilePhotoPreview();

            if (isSystemLogoCrop) {
                setSystemLogoFile(croppedFile);
                pendingSystemLogoFile = croppedFile;
                clearPendingSystemLogoPreview();
                pendingSystemLogoPreviewUrl = URL.createObjectURL(croppedFile);

                if (systemLogoPreview) {
                    systemLogoPreview.src = pendingSystemLogoPreviewUrl;
                    systemLogoPreview.classList.remove("hidden");
                }
                if (systemLogoPlaceholder) {
                    systemLogoPlaceholder.classList.add("hidden");
                }

                closePhotoCropModal();
                return;
            }

            setProfilePictureFile(croppedFile);
            clearPendingCroppedPreview();
            pendingCroppedPreviewUrl = URL.createObjectURL(croppedFile);

            if (detailPhoto && detailPhoto.dataset.isme === "1") {
                detailPhoto.src = pendingCroppedPreviewUrl;
            }

            closePhotoCropModal();

            if (detailPhotoHint) {
                detailPhotoHint.textContent = "Photo ready. Click Save Profile to apply.";
            }
        }, outputMimeType, isSystemLogoCrop ? undefined : 0.92);
    }

    function handlePhotoCropCancel() {
        closePhotoCropModal();
        clearPendingProfilePhotoPreview();

        if (activePhotoCropTarget === "system-logo") {
            if (pendingSystemLogoFile) {
                setSystemLogoFile(pendingSystemLogoFile);
            } else if (systemLogoInput) {
                systemLogoInput.value = "";
            }
            return;
        }

        if (profilePictureInput) {
            profilePictureInput.value = "";
        }
        syncDetailPhotoEditable();
    }

    function setSidePanel(panelName) {
        if (profilePanel) {
            profilePanel.classList.toggle("hidden", panelName === "add-member");
        }

        if (addMemberPanel) {
            addMemberPanel.classList.toggle("hidden", panelName !== "add-member");
        }

        if (memberDetailBlock) {
            memberDetailBlock.classList.toggle("hidden", panelName === "add-member");
        }

        if (profilePanelBtn) {
            profilePanelBtn.classList.toggle("is-active", panelName !== "add-member");
        }

        if (addMemberPanelBtn) {
            addMemberPanelBtn.classList.toggle("is-active", panelName === "add-member");
        }
    }

    if (profilePanelBtn) {
        profilePanelBtn.addEventListener("click", function () {
            setSidePanel("profile");
        });
    }

    if (addMemberPanelBtn) {
        addMemberPanelBtn.addEventListener("click", function () {
            setSidePanel("add-member");
        });
    }

    function syncChildParentingModeVisibility() {
        if (!relationTypeInput || !childParentingModeField) {
            return;
        }

        var isChildRelation = relationTypeInput.value === "child";
        var isParentRelation = relationTypeInput.value === "parent";
        var isPartnerRelation = relationTypeInput.value === "partner";
        var usesManualGender = isChildRelation || isParentRelation;
        var defaultPartnerGender = addMemberForm
            ? String(addMemberForm.getAttribute("data-default-partner-gender") || "female").toLowerCase()
            : "female";
        if (defaultPartnerGender !== "male" && defaultPartnerGender !== "female") {
            defaultPartnerGender = "female";
        }

        var canUseCurrentPartner = addMemberForm
            ? (String(addMemberForm.getAttribute("data-can-use-current-partner") || "0") === "1")
            : false;

        childParentingModeField.classList.toggle("hidden", !isChildRelation);

        if (memberGenderSelectField) {
            memberGenderSelectField.classList.toggle("hidden", !usesManualGender);
        }
        if (memberGenderPartnerInfo) {
            memberGenderPartnerInfo.classList.toggle("hidden", !isPartnerRelation);
        }
        if (memberGenderSelect) {
            memberGenderSelect.required = usesManualGender;
            memberGenderSelect.disabled = !usesManualGender;
        }
        if (memberGenderPartnerDisplay) {
            memberGenderPartnerDisplay.value = defaultPartnerGender === "male" ? "Male" : "Female";
        }
        if (memberGenderInput) {
            if (usesManualGender) {
                memberGenderInput.value = memberGenderSelect ? String(memberGenderSelect.value || "") : "";
            } else {
                memberGenderInput.value = defaultPartnerGender;
            }
        }

        if (childParentingModeField) {
            var currentPartnerOption = childParentingModeField.querySelector('option[value="with_current_partner"]');
            if (currentPartnerOption) {
                currentPartnerOption.disabled = !canUseCurrentPartner;
            }
        }

        if (childParentingModeField && childParentingModeField.querySelector('select[name="child_parenting_mode"]')) {
            var childParentingModeSelect = childParentingModeField.querySelector('select[name="child_parenting_mode"]');
            if (childParentingModeSelect) {
                if (canUseCurrentPartner && childParentingModeSelect.value !== "with_current_partner") {
                    childParentingModeSelect.value = "with_current_partner";
                }
                if (!canUseCurrentPartner && childParentingModeSelect.value === "with_current_partner") {
                    childParentingModeSelect.value = "single_parent";
                }
            }
        }

        if (memberEmailField) {
            memberEmailField.classList.toggle("hidden", !isPartnerRelation);
        }
        if (memberPhoneField) {
            memberPhoneField.classList.toggle("hidden", !isPartnerRelation);
        }

        if (memberEmailInput) {
            memberEmailInput.required = isPartnerRelation;
        }
        if (memberPhoneInput) {
            memberPhoneInput.required = isPartnerRelation;
        }
    }

    function setAddressFieldVisibility(fieldElement, shouldShow) {
        if (!fieldElement) {
            return;
        }

        fieldElement.classList.toggle("hidden", !shouldShow);
    }

    function fillAddressSelectOptions(selectElement, placeholderText, options, selectedValue) {
        if (!selectElement) {
            return;
        }

        selectElement.innerHTML = "";
        var placeholderOption = document.createElement("option");
        placeholderOption.value = "";
        placeholderOption.textContent = placeholderText;
        selectElement.appendChild(placeholderOption);

        options.forEach(function (optionLabel) {
            var option = document.createElement("option");
            option.value = optionLabel;
            option.textContent = optionLabel;
            selectElement.appendChild(option);
        });

        if (selectedValue) {
            selectElement.value = selectedValue;
            if (selectElement.value !== selectedValue) {
                selectElement.value = "";
            }
        } else {
            selectElement.value = "";
        }
    }

    function parseAddressParts(addressValue) {
        var parts = String(addressValue || "").split(",");
        var normalizedParts = parts.map(function (part) {
            return String(part || "").trim();
        });

        return {
            country: normalizedParts[0] || "",
            province: normalizedParts[1] || "",
            city: normalizedParts[2] || "",
            district: normalizedParts[3] || "",
            detail: normalizedParts.slice(4).join(", ").trim()
        };
    }

    function getAddressFormValue(inputElement) {
        if (!inputElement) {
            return "";
        }

        return String(inputElement.value || "").trim();
    }

    function buildAddressBinding(config) {
        return {
            addressInput: config.addressInput || null,
            countrySelect: config.countrySelect || null,
            provinceField: config.provinceField || null,
            provinceSelect: config.provinceSelect || null,
            cityField: config.cityField || null,
            citySelect: config.citySelect || null,
            districtField: config.districtField || null,
            districtSelect: config.districtSelect || null,
            detailInput: config.detailInput || null,
            oldCountryInput: config.oldCountryInput || null,
            oldProvinceInput: config.oldProvinceInput || null,
            oldCityInput: config.oldCityInput || null,
            oldDistrictInput: config.oldDistrictInput || null,
            oldDetailInput: config.oldDetailInput || null,
            submitForm: config.submitForm || null,
            countryPlaceholder: config.countryPlaceholder || "Select country",
            provincePlaceholder: config.provincePlaceholder || "Select province",
            cityPlaceholder: config.cityPlaceholder || "Select city",
            districtPlaceholder: config.districtPlaceholder || "Select district",
            countryRequired: config.countryRequired !== false,
            onLayoutChange: config.onLayoutChange || null,
            requestSerial: 0,
            eventsBound: false
        };
    }

    function refreshDetailEditGridLayout() {
        if (!detailEditForm) {
            return;
        }

        var grid = detailEditForm.querySelector(".detail-edit-grid");
        if (!grid) {
            return;
        }

        var fields = Array.prototype.slice.call(grid.children).filter(function (child) {
            return child && child.classList && child.classList.contains("detail-form-field");
        });

        fields.forEach(function (field) {
            field.classList.remove("is-span-full");
        });

        var buffer = [];

        var flushBuffer = function () {
            if (buffer.length % 2 === 1) {
                buffer[buffer.length - 1].classList.add("is-span-full");
            }
            buffer = [];
        };

        fields.forEach(function (field) {
            if (field.classList.contains("hidden")) {
                return;
            }

            if (field.classList.contains("full-width")) {
                flushBuffer();
                return;
            }

            buffer.push(field);
        });

        flushBuffer();
    }

    function syncAddressHiddenInput(binding) {
        if (!binding || !binding.addressInput) {
            return;
        }

        var addressParts = [];
        var country = getAddressFormValue(binding.countrySelect);
        var province = getAddressFormValue(binding.provinceSelect);
        var city = getAddressFormValue(binding.citySelect);
        var district = getAddressFormValue(binding.districtSelect);
        var detail = getAddressFormValue(binding.detailInput);

        if (country !== "") {
            addressParts.push(country);
        }
        if (province !== "") {
            addressParts.push(province);
        }
        if (city !== "") {
            addressParts.push(city);
        }
        if (district !== "") {
            addressParts.push(district);
        }
        if (detail !== "") {
            addressParts.push(detail);
        }

        binding.addressInput.value = addressParts.join(", ");
        if (typeof binding.onLayoutChange === "function") {
            binding.onLayoutChange();
        }
    }

    function syncAddressRequirements(binding) {
        if (!binding) {
            return;
        }

        var hasProvinceField = binding.provinceField && !binding.provinceField.classList.contains("hidden");
        var hasCityField = binding.cityField && !binding.cityField.classList.contains("hidden");
        var hasDistrictField = binding.districtField && !binding.districtField.classList.contains("hidden");

        if (binding.provinceSelect) {
            binding.provinceSelect.required = hasProvinceField;
            binding.provinceSelect.disabled = !hasProvinceField;
        }

        if (binding.citySelect) {
            binding.citySelect.required = hasCityField;
            binding.citySelect.disabled = !hasCityField;
        }

        if (binding.districtSelect) {
            binding.districtSelect.required = hasDistrictField;
            binding.districtSelect.disabled = !hasDistrictField;
        }
    }

    function updateAddressDistrictOptions(binding, selectedCountry, selectedProvince, selectedCity, selectedDistrict) {
        var renderDistrictOptions = function () {
            var countryData = ADDRESS_REGION_DATA[selectedCountry] || null;
            var provinceData = countryData && countryData[selectedProvince] ? countryData[selectedProvince] : null;
            var districtOptions = provinceData && provinceData[selectedCity] ? provinceData[selectedCity] : [];
            var shouldShowDistrict = districtOptions.length > 0;

            fillAddressSelectOptions(
                binding.districtSelect,
                binding.districtPlaceholder,
                districtOptions,
                selectedDistrict || ""
            );
            setAddressFieldVisibility(binding.districtField, shouldShowDistrict);
            syncAddressRequirements(binding);
            syncAddressHiddenInput(binding);
        };

        if (isIndonesiaCountry(selectedCountry)) {
            ensureAddressDistrictDataLoaded(selectedCountry, selectedProvince, selectedCity).finally(function () {
                renderDistrictOptions();
            });
            return;
        }

        renderDistrictOptions();
    }

    function updateAddressCityOptions(binding, selectedCountry, selectedProvince, selectedCity, selectedDistrict, requestSerial) {
        var countryData = ADDRESS_REGION_DATA[selectedCountry] || null;
        var provinceData = countryData && countryData[selectedProvince] ? countryData[selectedProvince] : null;
        var currentSerial = requestSerial || binding.requestSerial;

        var renderCityOptions = function () {
            var refreshedCountryData = ADDRESS_REGION_DATA[selectedCountry] || null;
            var refreshedProvinceData = refreshedCountryData && refreshedCountryData[selectedProvince]
                ? refreshedCountryData[selectedProvince]
                : null;
            var cityOptions = refreshedProvinceData ? sortAddressOptionLabels(Object.keys(refreshedProvinceData)) : [];
            var shouldShowCity = cityOptions.length > 0;

            fillAddressSelectOptions(binding.citySelect, binding.cityPlaceholder, cityOptions, selectedCity || "");
            setAddressFieldVisibility(binding.cityField, shouldShowCity);

            if (!shouldShowCity) {
                fillAddressSelectOptions(binding.districtSelect, binding.districtPlaceholder, [], "");
                setAddressFieldVisibility(binding.districtField, false);
                syncAddressRequirements(binding);
                syncAddressHiddenInput(binding);
                return;
            }

            updateAddressDistrictOptions(
                binding,
                selectedCountry,
                selectedProvince,
                getAddressFormValue(binding.citySelect),
                selectedDistrict || ""
            );
        };

        if (provinceData && Object.keys(provinceData).length > 0) {
            renderCityOptions();
            return;
        }

        ensureAddressProvinceCityDataLoaded(selectedCountry, selectedProvince).then(function () {
            if (currentSerial !== binding.requestSerial) {
                return;
            }
            renderCityOptions();
        });
    }

    function updateAddressProvinceOptions(binding, selectedCountry, selectedProvince, selectedCity, selectedDistrict, requestSerial) {
        var countryData = ADDRESS_REGION_DATA[selectedCountry] || null;
        var provinceOptions = countryData ? sortAddressOptionLabels(Object.keys(countryData)) : [];
        var shouldShowProvince = provinceOptions.length > 0;

        fillAddressSelectOptions(
            binding.provinceSelect,
            binding.provincePlaceholder,
            provinceOptions,
            selectedProvince || ""
        );
        setAddressFieldVisibility(binding.provinceField, shouldShowProvince);

        if (!shouldShowProvince) {
            fillAddressSelectOptions(binding.citySelect, binding.cityPlaceholder, [], "");
            fillAddressSelectOptions(binding.districtSelect, binding.districtPlaceholder, [], "");
            setAddressFieldVisibility(binding.cityField, false);
            setAddressFieldVisibility(binding.districtField, false);
            syncAddressRequirements(binding);
            syncAddressHiddenInput(binding);
            return;
        }

        updateAddressCityOptions(
            binding,
            selectedCountry,
            getAddressFormValue(binding.provinceSelect),
            selectedCity || "",
            selectedDistrict || "",
            requestSerial || binding.requestSerial
        );
    }

    function initializeAddressCascade(binding) {
        if (!binding || !binding.countrySelect || !binding.addressInput) {
            return;
        }

        ensureAddressCountryDataLoaded().finally(function () {
            var countries = sortAddressOptionLabels(Object.keys(ADDRESS_REGION_DATA));
            fillAddressSelectOptions(binding.countrySelect, binding.countryPlaceholder, countries, "");

            var parsedAddress = parseAddressParts(getAddressFormValue(binding.addressInput));
            var oldCountry = getAddressFormValue(binding.oldCountryInput) || parsedAddress.country;
            var oldProvince = getAddressFormValue(binding.oldProvinceInput) || parsedAddress.province;
            var oldCity = getAddressFormValue(binding.oldCityInput) || parsedAddress.city;
            var oldDistrict = getAddressFormValue(binding.oldDistrictInput) || parsedAddress.district;
            var oldDetail = getAddressFormValue(binding.oldDetailInput) || parsedAddress.detail;

            if (binding.oldCountryInput && !getAddressFormValue(binding.oldCountryInput)) {
                binding.oldCountryInput.value = oldCountry;
            }
            if (binding.oldProvinceInput && !getAddressFormValue(binding.oldProvinceInput)) {
                binding.oldProvinceInput.value = oldProvince;
            }
            if (binding.oldCityInput && !getAddressFormValue(binding.oldCityInput)) {
                binding.oldCityInput.value = oldCity;
            }
            if (binding.oldDistrictInput && !getAddressFormValue(binding.oldDistrictInput)) {
                binding.oldDistrictInput.value = oldDistrict;
            }
            if (binding.oldDetailInput && !getAddressFormValue(binding.oldDetailInput)) {
                binding.oldDetailInput.value = oldDetail;
            }

            if (oldDetail !== "" && binding.detailInput && getAddressFormValue(binding.detailInput) === "") {
                binding.detailInput.value = oldDetail;
            }

            if (oldCountry !== "") {
                binding.countrySelect.value = oldCountry;
            }

            binding.requestSerial += 1;
            updateAddressProvinceOptions(
                binding,
                getAddressFormValue(binding.countrySelect),
                oldProvince,
                oldCity,
                oldDistrict,
                binding.requestSerial
            );

            syncAddressHiddenInput(binding);

            if (!binding.eventsBound) {
                binding.eventsBound = true;

                binding.countrySelect.addEventListener("change", function () {
                    binding.countrySelect.setCustomValidity("");
                    binding.requestSerial += 1;
                    updateAddressProvinceOptions(
                        binding,
                        getAddressFormValue(binding.countrySelect),
                        "",
                        "",
                        "",
                        binding.requestSerial
                    );
                });

                if (binding.provinceSelect) {
                    binding.provinceSelect.addEventListener("change", function () {
                        binding.requestSerial += 1;
                        updateAddressCityOptions(
                            binding,
                            getAddressFormValue(binding.countrySelect),
                            getAddressFormValue(binding.provinceSelect),
                            "",
                            "",
                            binding.requestSerial
                        );
                    });
                }

                if (binding.citySelect) {
                    binding.citySelect.addEventListener("change", function () {
                        updateAddressDistrictOptions(
                            binding,
                            getAddressFormValue(binding.countrySelect),
                            getAddressFormValue(binding.provinceSelect),
                            getAddressFormValue(binding.citySelect),
                            ""
                        );
                    });
                }

                if (binding.districtSelect) {
                    binding.districtSelect.addEventListener("change", function () {
                        syncAddressHiddenInput(binding);
                    });
                }

                if (binding.detailInput) {
                    binding.detailInput.addEventListener("input", function () {
                        syncAddressHiddenInput(binding);
                    });
                }

                if (binding.submitForm) {
                    binding.submitForm.addEventListener("submit", function (event) {
                        syncAddressHiddenInput(binding);
                        if (binding.countryRequired && getAddressFormValue(binding.countrySelect) === "") {
                            binding.countrySelect.setCustomValidity("Please select country first.");
                            binding.countrySelect.reportValidity();
                            event.preventDefault();
                            return;
                        }

                        binding.countrySelect.setCustomValidity("");
                    });
                }
            }
        });
    }

    var memberAddressBinding = buildAddressBinding({
        addressInput: memberAddressInput,
        countrySelect: memberAddressCountrySelect,
        provinceField: memberAddressProvinceField,
        provinceSelect: memberAddressProvinceSelect,
        cityField: memberAddressCityField,
        citySelect: memberAddressCitySelect,
        districtField: memberAddressDistrictField,
        districtSelect: memberAddressDistrictSelect,
        detailInput: memberAddressDetailInput,
        oldCountryInput: memberAddressCountryOldInput,
        oldProvinceInput: memberAddressProvinceOldInput,
        oldCityInput: memberAddressCityOldInput,
        oldDistrictInput: memberAddressDistrictOldInput,
        oldDetailInput: memberAddressDetailOldInput,
        submitForm: addMemberForm
    });

    var detailEditAddressBinding = buildAddressBinding({
        addressInput: detailEditAddressInput,
        countrySelect: detailEditAddressCountrySelect,
        provinceField: detailEditAddressProvinceField,
        provinceSelect: detailEditAddressProvinceSelect,
        cityField: detailEditAddressCityField,
        citySelect: detailEditAddressCitySelect,
        districtField: detailEditAddressDistrictField,
        districtSelect: detailEditAddressDistrictSelect,
        detailInput: detailEditAddressDetailInput,
        oldCountryInput: detailEditAddressCountryOldInput,
        oldProvinceInput: detailEditAddressProvinceOldInput,
        oldCityInput: detailEditAddressCityOldInput,
        oldDistrictInput: detailEditAddressDistrictOldInput,
        oldDetailInput: detailEditAddressDetailOldInput,
        submitForm: detailEditForm,
        onLayoutChange: refreshDetailEditGridLayout
    });

    window.familyTreeRefreshDetailAddressCascade = function () {
        initializeAddressCascade(detailEditAddressBinding);
    };

    function initializeMemberAddressCascade() {
        initializeAddressCascade(memberAddressBinding);
        initializeAddressCascade(detailEditAddressBinding);
        refreshDetailEditGridLayout();
    }

    if (relationButtons.length && relationTypeInput) {
        relationButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                var relationType = button.getAttribute("data-relation-type") || "child";
                relationTypeInput.value = relationType;

                relationButtons.forEach(function (item) {
                    item.classList.remove("is-active");
                });
                button.classList.add("is-active");
                syncChildParentingModeVisibility();
            });
        });

        syncChildParentingModeVisibility();
    }

    initializeMemberAddressCascade();

    if (memberGenderSelect) {
        memberGenderSelect.addEventListener("change", function () {
            if (memberGenderInput && relationTypeInput && (relationTypeInput.value === "child" || relationTypeInput.value === "parent")) {
                memberGenderInput.value = String(memberGenderSelect.value || "");
            }
        });
    }

    function bindTreeSeeMoreButtons() {
        if (!treeSeeMoreButtons.length) {
            return;
        }

        treeSeeMoreButtons.forEach(function (button) {
            if (button.dataset.treeBound === "1") {
                return;
            }

            button.dataset.treeBound = "1";
            button.addEventListener("click", function () {
                var branch = button.nextElementSibling;
                if (!branch || !branch.classList.contains("tree-extra-children")) {
                    return;
                }

                var isOpen = button.getAttribute("data-open") === "1";
                if (isOpen) {
                    branch.classList.add("hidden");
                    button.setAttribute("data-open", "0");
                    button.textContent = "See more";
                } else {
                    branch.classList.remove("hidden");
                    button.setAttribute("data-open", "1");
                    button.textContent = "See less";
                }

                bindTreeConnectorImageSync();
                scheduleTreeConnectorDraw();
                recenterTreeViewport({ durationMs: 200 });
            });
        });
    }

    function syncTreeExpandButtons(treeState) {
        refreshTreeDomState();

        treeExpandButtons.forEach(function (button) {
            var direction = button.getAttribute("data-tree-direction") || "";
            var isUpper = direction === "upper";
            var hasHiddenLevels = isUpper
                ? Boolean(treeState.hasHiddenUpperTreeLevels)
                : Boolean(treeState.hasHiddenLowerTreeLevels);
            var isExpanded = isUpper
                ? Boolean(treeState.showUpperTree)
                : Boolean(treeState.showLowerTree);
            var toggleUrl = isUpper ? (treeState.toggleUpperTreeUrl || "") : (treeState.toggleLowerTreeUrl || "");
            var shouldShowButton = hasHiddenLevels || isExpanded;
            var secondaryWrap = button.closest(".tree-tools-secondary");
            var expandLabel = isUpper ? "View more ancestors" : "View more descendants";
            var collapseLabel = isUpper ? "Hide ancestors" : "Hide descendants";

            button.classList.toggle("hidden", !shouldShowButton);
            button.textContent = isExpanded ? collapseLabel : expandLabel;
            if (secondaryWrap) {
                secondaryWrap.classList.toggle("hidden", !shouldShowButton);
            }

            if (shouldShowButton && toggleUrl) {
                button.setAttribute("data-tree-toggle-url", toggleUrl);
                button.setAttribute("data-tree-expanded", isExpanded ? "1" : "0");
            } else {
                button.removeAttribute("data-tree-toggle-url");
                button.removeAttribute("data-tree-expanded");
            }
        });
    }

    function buildTreeExpandAllToggleUrl(expandAll) {
        var target = new URL(window.location.href);
        target.searchParams.delete("ajax");
        target.searchParams.delete("tree_section");
        target.searchParams.delete("show_full_tree");
        target.searchParams.set("show_upper_tree", expandAll ? "1" : "0");
        target.searchParams.set("show_lower_tree", expandAll ? "1" : "0");
        return target.toString();
    }

    function syncTreeExpandAllButton(treeState) {
        if (!treeExpandAllBtn) {
            return;
        }

        var hasHiddenTreeLevels = Boolean(treeState.hasHiddenUpperTreeLevels)
            || Boolean(treeState.hasHiddenLowerTreeLevels);
        var showFullTree = Boolean(treeState.showUpperTree) && Boolean(treeState.showLowerTree);
        var shouldShowButton = hasHiddenTreeLevels || showFullTree;

        treeExpandAllBtn.classList.toggle("hidden", !shouldShowButton);
        if (!shouldShowButton) {
            treeExpandAllBtn.removeAttribute("data-tree-toggle-url");
            treeExpandAllBtn.removeAttribute("data-tree-expanded");
            return;
        }

        treeExpandAllBtn.textContent = showFullTree ? "Show less" : "View more";
        treeExpandAllBtn.setAttribute("data-tree-expanded", showFullTree ? "1" : "0");
        treeExpandAllBtn.setAttribute("data-tree-toggle-url", buildTreeExpandAllToggleUrl(!showFullTree));
    }

    function setTreeToggleButtonsDisabled(disabled) {
        treeExpandButtons.forEach(function (item) {
            item.disabled = disabled;
        });
        if (treeExpandAllBtn) {
            treeExpandAllBtn.disabled = disabled;
        }
    }

    function requestTreeSectionToggle(url) {
        if (!url || treeToggleRequestInFlight) {
            return;
        }

        var selectedCard = document.querySelector(".member-card.active");
        var selectedMemberId = selectedCard ? selectedCard.getAttribute("data-memberid") : "";

        treeToggleRequestInFlight = true;
        setTreeToggleButtonsDisabled(true);

        fetch(getTreeSectionAjaxUrl(url), {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json"
            }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error("Failed to load family tree.");
                }
                return response.json();
            })
            .then(function (data) {
                if (treeContainer) {
                    treeContainer.innerHTML = data.tree_html || "";
                }

                if (treeSummaryText) {
                    treeSummaryText.textContent = data.summary_text || "";
                }

                var nextTreeState = {
                    hasHiddenUpperTreeLevels: Boolean(data.has_hidden_upper_tree_levels),
                    hasHiddenLowerTreeLevels: Boolean(data.has_hidden_lower_tree_levels),
                    showUpperTree: Boolean(data.show_upper_tree),
                    showLowerTree: Boolean(data.show_lower_tree),
                    toggleUpperTreeUrl: data.toggle_upper_tree_url || "",
                    toggleLowerTreeUrl: data.toggle_lower_tree_url || ""
                };
                syncTreeExpandButtons(nextTreeState);
                syncTreeExpandAllButton(nextTreeState);
                bindTreeExpandAllButton();
                bindTreeSeeMoreButtons();
                bindTreeCardClicks();
                bindTreeConnectorImageSync();
                scheduleTreeConnectorDraw();

                window.dispatchEvent(new Event("resize"));

                var nextActiveCard = selectedMemberId
                    ? document.querySelector('.member-card[data-memberid="' + selectedMemberId + '"]')
                    : null;
                if (!nextActiveCard) {
                    nextActiveCard = document.querySelector(".member-card.active") || document.querySelector(".member-card");
                }

                if (nextActiveCard) {
                    setActive(nextActiveCard);
                }

                recenterTreeViewport({ durationMs: 260 });
                var browserUrl = new URL(url, window.location.origin);
                browserUrl.searchParams.delete("ajax");
                browserUrl.searchParams.delete("tree_section");
                window.history.pushState({}, "", browserUrl.toString());
            })
            .catch(function () {})
            .finally(function () {
                treeToggleRequestInFlight = false;
                setTreeToggleButtonsDisabled(false);
            });
    }

    function getTreeSectionAjaxUrl(url) {
        var target = new URL(getAjaxUrl(url), window.location.origin);
        target.searchParams.set("tree_section", "1");
        return target.toString();
    }

    function bindTreeExpandButtons() {
        if (!treeExpandButtons.length) {
            return;
        }

        treeExpandButtons.forEach(function (button) {
            if (button.dataset.treeAjaxBound === "1") {
                return;
            }

            button.dataset.treeAjaxBound = "1";
            button.addEventListener("click", function () {
                var url = button.getAttribute("data-tree-toggle-url");
                requestTreeSectionToggle(url);
            });
        });
    }

    function bindTreeExpandAllButton() {
        if (!treeExpandAllBtn) {
            return;
        }

        if (treeExpandAllBtn.dataset.treeAjaxBound === "1") {
            return;
        }

        treeExpandAllBtn.dataset.treeAjaxBound = "1";
        treeExpandAllBtn.addEventListener("click", function () {
            var url = treeExpandAllBtn.getAttribute("data-tree-toggle-url");
            requestTreeSectionToggle(url);
        });
    }

    if (dropdowns.length) {
        dropdowns.forEach(function (dropdown) {
            var dropdownToggle = dropdown.querySelector("[data-dropdown-toggle]");
            if (!dropdownToggle) {
                return;
            }

            dropdownToggle.addEventListener("click", function () {
                var willOpen = !dropdown.classList.contains("open");

                dropdowns.forEach(function (item) {
                    item.classList.remove("open");
                    var itemToggle = item.querySelector("[data-dropdown-toggle]");
                    if (itemToggle) {
                        itemToggle.setAttribute("aria-expanded", "false");
                    }
                });

                if (willOpen) {
                    dropdown.classList.add("open");
                    dropdownToggle.setAttribute("aria-expanded", "true");
                }
            });
        });

        document.addEventListener("click", function (event) {
            dropdowns.forEach(function (dropdown) {
                if (!dropdown.contains(event.target)) {
                    dropdown.classList.remove("open");
                    var dropdownToggle = dropdown.querySelector("[data-dropdown-toggle]");
                    if (dropdownToggle) {
                        dropdownToggle.setAttribute("aria-expanded", "false");
                    }
                }
            });
        });

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape") {
                dropdowns.forEach(function (dropdown) {
                    dropdown.classList.remove("open");
                    var dropdownToggle = dropdown.querySelector("[data-dropdown-toggle]");
                    if (dropdownToggle) {
                        dropdownToggle.setAttribute("aria-expanded", "false");
                    }
                });
            }
        });
    }

    var closeModal = function () {};

    if (addUserModal && openAddUserModal) {
        closeModal = function () {
            addUserModal.classList.remove("open");
            addUserModal.setAttribute("aria-hidden", "true");
        };

        openAddUserModal.addEventListener("click", function () {
            addUserModal.classList.add("open");
            addUserModal.setAttribute("aria-hidden", "false");
        });

        if (closeAddUserModal) {
            closeAddUserModal.addEventListener("click", closeModal);
        }

        if (cancelAddUserModal) {
            cancelAddUserModal.addEventListener("click", closeModal);
        }

        addUserModal.addEventListener("click", function (event) {
            if (event.target === addUserModal) {
                closeModal();
            }
        });

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape") {
                closeModal();
            }
        });
    }

    if (importUserModal && openImportUserModal) {
        var closeImportModal = function () {
            importUserModal.classList.remove("open");
            importUserModal.setAttribute("aria-hidden", "true");
        };

        openImportUserModal.addEventListener("click", function () {
            importUserModal.classList.add("open");
            importUserModal.setAttribute("aria-hidden", "false");
        });

        if (closeImportUserModal) {
            closeImportUserModal.addEventListener("click", closeImportModal);
        }

        if (cancelImportUserModal) {
            cancelImportUserModal.addEventListener("click", closeImportModal);
        }

        importUserModal.addEventListener("click", function (event) {
            if (event.target === importUserModal) {
                closeImportModal();
            }
        });

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape") {
                closeImportModal();
            }
        });
    }

    function openEditUserModal(payload) {
        if (!editUserModal || !editUserForm) {
            return;
        }

        var userId = parseInt(payload.userid || "0", 10);
        if (!userId) {
            return;
        }

        editUserForm.setAttribute("action", "/management/users/" + userId + "/update");

        if (editUsername) {
            editUsername.value = payload.username || "";
        }
        if (editName) {
            editName.value = payload.fullname || "";
        }
        if (editEmail) {
            editEmail.value = payload.email || "";
        }
        if (editPhone) {
            editPhone.value = payload.phone || "";
        }
        if (editLevel) {
            editLevel.value = payload.levelid || "";
        }
        if (editRole) {
            editRole.value = payload.roleid || "";
        }
        if (editGender) {
            editGender.value = payload.gender || "";
        }
        if (editLifeStatus) {
            editLifeStatus.value = String(payload.life_status || "").toLowerCase();
        }
        if (editMaritalStatus) {
            editMaritalStatus.value = payload.marital_status || "";
        }
        if (editBirthdate) {
            editBirthdate.value = payload.birthdate || "";
        }
        if (editBirthplace) {
            editBirthplace.value = payload.birthplace || "";
        }
        if (editAddress) {
            editAddress.value = payload.address || "";
        }
        if (editJob) {
            editJob.value = payload.job || "";
        }
        if (editEducationStatus) {
            editEducationStatus.value = payload.education_status || "";
        }

        var isFamilySource = parseInt(payload.levelid || "0", 10) === 2 || parseInt(payload.levelid || "0", 10) === 4;
        if (editFamilyFields) {
            editFamilyFields.classList.toggle("hidden", !isFamilySource);
        }
        [editGender, editLifeStatus, editMaritalStatus, editBirthdate, editBirthplace, editAddress].forEach(function (field) {
            if (!field) {
                return;
            }
            if (isFamilySource) {
                field.setAttribute("required", "required");
            } else {
                field.removeAttribute("required");
            }
        });

        editUserModal.classList.add("open");
        editUserModal.setAttribute("aria-hidden", "false");
    }

    function closeEditUserModalFn() {
        if (!editUserModal) {
            return;
        }

        editUserModal.classList.remove("open");
        editUserModal.setAttribute("aria-hidden", "true");
    }

    function getUserBulkSelectedIds() {
        var keys = Object.keys(userBulkSelectedMap);
        var ids = [];

        for (var i = 0; i < keys.length; i += 1) {
            if (!userBulkSelectedMap[keys[i]]) {
                continue;
            }

            var parsedId = parseInt(keys[i], 10);
            if (!isNaN(parsedId) && parsedId > 0) {
                ids.push(parsedId);
            }
        }

        return ids;
    }

    function getUserRowFromTarget(target) {
        if (!target || !target.closest) {
            return null;
        }

        return target.closest("tr[data-user-row]");
    }

    function getUserRowId(row) {
        if (!row) {
            return 0;
        }

        var parsedId = parseInt(row.getAttribute("data-userid") || "0", 10);
        return isNaN(parsedId) ? 0 : parsedId;
    }

    function getUserBulkCheckbox(row) {
        if (!row) {
            return null;
        }

        return row.querySelector("input[data-bulk-checkbox]");
    }

    function isUserBulkInteractiveTarget(target) {
        if (!target || !target.closest) {
            return false;
        }

        return Boolean(target.closest("a, button, input, select, textarea, label, form, .action-group"));
    }

    function clearUserBulkLongPressTimer() {
        if (userBulkLongPressTimer) {
            window.clearTimeout(userBulkLongPressTimer);
            userBulkLongPressTimer = 0;
        }
    }

    function setUserRowBulkSelected(row, selected) {
        var userId = getUserRowId(row);
        var checkbox = getUserBulkCheckbox(row);
        if (!userId || !checkbox) {
            return;
        }

        if (selected) {
            userBulkSelectedMap[String(userId)] = true;
            row.classList.add("bulk-selected");
            checkbox.checked = true;
        } else {
            delete userBulkSelectedMap[String(userId)];
            row.classList.remove("bulk-selected");
            checkbox.checked = false;
        }
    }

    function clearUserBulkSelectionInTable() {
        if (!userTableBody) {
            return;
        }

        var rows = userTableBody.querySelectorAll("tr[data-user-row]");
        for (var i = 0; i < rows.length; i += 1) {
            rows[i].classList.remove("bulk-selected");
            var checkbox = getUserBulkCheckbox(rows[i]);
            if (checkbox) {
                checkbox.checked = false;
            }
        }
    }

    function setAllUserRowsBulkSelected(selected) {
        if (!userTableBody) {
            return;
        }

        var rows = userTableBody.querySelectorAll("tr[data-user-row]");
        for (var i = 0; i < rows.length; i += 1) {
            setUserRowBulkSelected(rows[i], selected);
        }
    }

    function rebuildBulkDeleteHiddenInputs() {
        if (!bulkDeleteHiddenInputs) {
            return;
        }

        while (bulkDeleteHiddenInputs.firstChild) {
            bulkDeleteHiddenInputs.removeChild(bulkDeleteHiddenInputs.firstChild);
        }

        var selectedIds = getUserBulkSelectedIds();
        for (var i = 0; i < selectedIds.length; i += 1) {
            var input = document.createElement("input");
            input.type = "hidden";
            input.name = "user_ids[]";
            input.value = String(selectedIds[i]);
            bulkDeleteHiddenInputs.appendChild(input);
        }
    }

    function syncUserBulkUiState() {
        var selectedCount = getUserBulkSelectedIds().length;
        var selectableCount = userTableBody
            ? userTableBody.querySelectorAll("tr[data-user-row]").length
            : 0;

        if (userDataTable) {
            if (userBulkModeActive) {
                userDataTable.classList.add("bulk-mode");
            } else {
                userDataTable.classList.remove("bulk-mode");
            }
        }

        if (bulkUserActions) {
            if (userBulkModeActive) {
                bulkUserActions.classList.remove("hidden");
            } else {
                bulkUserActions.classList.add("hidden");
            }
        }

        if (bulkSelectedCount) {
            bulkSelectedCount.textContent = selectedCount + " selected";
        }

        if (bulkDeleteBtn) {
            bulkDeleteBtn.disabled = selectedCount === 0;
        }

        if (bulkSelectAllUsers) {
            var allSelected = selectableCount > 0 && selectedCount === selectableCount;
            var someSelected = selectedCount > 0 && selectedCount < selectableCount;
            bulkSelectAllUsers.disabled = selectableCount === 0;
            bulkSelectAllUsers.checked = allSelected;
            bulkSelectAllUsers.indeterminate = someSelected;
        }

        if (document.body) {
            document.body.classList.toggle("bulk-user-card-active", userBulkModeActive);
        }

        rebuildBulkDeleteHiddenInputs();
    }

    function activateUserBulkMode(initialRow) {
        if (!userDataTable || !userTableBody) {
            return;
        }

        userBulkModeActive = true;

        syncUserBulkUiState();
    }

    function deactivateUserBulkMode() {
        clearUserBulkLongPressTimer();
        userBulkLongPressTriggered = false;
        userBulkModeActive = false;
        userBulkSelectedMap = {};
        clearUserBulkSelectionInTable();
        syncUserBulkUiState();
    }

    function startUserBulkLongPress(event) {
        if (userBulkModeActive) {
            return;
        }

        var row = getUserRowFromTarget(event.target);
        if (!row) {
            return;
        }

        if (isUserBulkInteractiveTarget(event.target)) {
            return;
        }

        if (event.type === "mousedown" && event.button !== 0) {
            return;
        }

        if (event.touches && event.touches.length > 1) {
            return;
        }

        clearUserBulkLongPressTimer();
        userBulkLongPressTriggered = false;

        userBulkLongPressTimer = window.setTimeout(function () {
            userBulkLongPressTriggered = true;
            activateUserBulkMode(row);
        }, USER_BULK_LONG_PRESS_MS);
    }

    if (userTableBody && userDataTable) {
        syncUserBulkUiState();

        userTableBody.addEventListener("mousedown", function (event) {
            startUserBulkLongPress(event);
        });

        userTableBody.addEventListener("touchstart", function (event) {
            startUserBulkLongPress(event);
        });

        userTableBody.addEventListener("mouseup", function () {
            clearUserBulkLongPressTimer();
        });

        userTableBody.addEventListener("mouseleave", function () {
            clearUserBulkLongPressTimer();
        });

        userTableBody.addEventListener("touchend", function () {
            clearUserBulkLongPressTimer();
        });

        userTableBody.addEventListener("touchcancel", function () {
            clearUserBulkLongPressTimer();
        });

        userTableBody.addEventListener("touchmove", function () {
            clearUserBulkLongPressTimer();
        });

        userTableBody.addEventListener("change", function (event) {
            var checkbox = event.target.closest("input[data-bulk-checkbox]");
            if (!checkbox) {
                return;
            }

            if (!userBulkModeActive) {
                checkbox.checked = false;
                return;
            }

            var row = getUserRowFromTarget(checkbox);
            setUserRowBulkSelected(row, !!checkbox.checked);
            syncUserBulkUiState();
        });

        userTableBody.addEventListener("click", function (event) {
            var editBtn = event.target.closest(".js-open-edit-user-modal");
            if (editBtn) {
                event.preventDefault();
                openEditUserModal({
                    userid: editBtn.getAttribute("data-userid") || "",
                    username: editBtn.getAttribute("data-username") || "",
                    fullname: editBtn.getAttribute("data-fullname") || "",
                    email: editBtn.getAttribute("data-email") || "",
                    phone: editBtn.getAttribute("data-phone") || "",
                    roleid: editBtn.getAttribute("data-roleid") || "",
                    source: editBtn.getAttribute("data-source") || "",
                    gender: editBtn.getAttribute("data-gender") || "",
                    life_status: editBtn.getAttribute("data-life-status") || "",
                    marital_status: editBtn.getAttribute("data-marital-status") || "",
                    birthdate: editBtn.getAttribute("data-birthdate") || "",
                    birthplace: editBtn.getAttribute("data-birthplace") || "",
                    address: editBtn.getAttribute("data-address") || "",
                    job: editBtn.getAttribute("data-job") || "",
                    education_status: editBtn.getAttribute("data-education-status") || ""
                });
                return;
            }

            var row = getUserRowFromTarget(event.target);
            if (!row) {
                return;
            }

            if (userBulkLongPressTriggered) {
                userBulkLongPressTriggered = false;
                event.preventDefault();
                return;
            }

            if (!userBulkModeActive) {
                return;
            }

            if (event.target.closest("input[data-bulk-checkbox]")) {
                return;
            }

            if (isUserBulkInteractiveTarget(event.target)) {
                return;
            }

            event.preventDefault();
            var checkbox = getUserBulkCheckbox(row);
            if (!checkbox) {
                return;
            }

            setUserRowBulkSelected(row, !checkbox.checked);
            syncUserBulkUiState();
        });
    }

    if (closeEditUserModal) {
        closeEditUserModal.addEventListener("click", closeEditUserModalFn);
    }
    if (cancelEditUserModal) {
        cancelEditUserModal.addEventListener("click", closeEditUserModalFn);
    }
    if (editUserModal) {
        editUserModal.addEventListener("click", function (event) {
            if (event.target === editUserModal) {
                closeEditUserModalFn();
            }
        });
    }

    if (cancelBulkDeleteBtn) {
        cancelBulkDeleteBtn.addEventListener("click", function () {
            deactivateUserBulkMode();
        });
    }

    if (bulkSelectAllUsers) {
        bulkSelectAllUsers.addEventListener("change", function () {
            if (!userBulkModeActive) {
                bulkSelectAllUsers.checked = false;
                bulkSelectAllUsers.indeterminate = false;
                return;
            }

            setAllUserRowsBulkSelected(!!bulkSelectAllUsers.checked);
            syncUserBulkUiState();
        });
    }

    if (bulkDeleteForm) {
        bulkDeleteForm.addEventListener("submit", function (event) {
            var selectedCount = getUserBulkSelectedIds().length;
            if (selectedCount === 0) {
                event.preventDefault();
                return;
            }

            rebuildBulkDeleteHiddenInputs();

            var confirmMessage = bulkDeleteForm.getAttribute("data-confirm-message")
                || "Move selected users to Recycle Bin?";
            if (!window.confirm(confirmMessage)) {
                event.preventDefault();
            }
        });
    }

    function getAjaxUrl(url) {
        var target = new URL(url, window.location.origin);
        target.searchParams.set("ajax", "1");
        return target.toString();
    }

    function getUsersFilterStateFromControls() {
        return {
            search: userSearchInput ? String(userSearchInput.value || "").trim() : "",
            role: userRoleFilter ? String(userRoleFilter.value || "").trim() : ""
        };
    }

    function buildUsersListUrl(pageNumber) {
        var target = new URL(window.location.href);
        var filters = getUsersFilterStateFromControls();
        target.searchParams.delete("ajax");

        if (filters.search !== "") {
            target.searchParams.set("search", filters.search);
        } else {
            target.searchParams.delete("search");
        }

        if (filters.role !== "") {
            target.searchParams.set("role", filters.role);
        } else {
            target.searchParams.delete("role");
        }

        if (typeof pageNumber === "number" && pageNumber > 1) {
            target.searchParams.set("page", String(pageNumber));
        } else {
            target.searchParams.delete("page");
        }

        return target.toString();
    }

    function syncUserFilterControlsFromUrl(url) {
        var target = new URL(url, window.location.origin);
        var search = target.searchParams.get("search") || "";
        var role = target.searchParams.get("role") || "";

        if (userSearchInput) {
            userSearchInput.value = search;
        }

        if (userRoleFilter) {
            var roleExists = Array.prototype.some.call(userRoleFilter.options, function (option) {
                return String(option.value || "") === role;
            });
            userRoleFilter.value = roleExists ? role : "";
        }
    }

    function fetchUsersPage(url, options) {
        if (!userTableBody || !userPagination || !url) {
            return;
        }

        var nextOptions = options || {};
        var normalizedUrl = new URL(url, window.location.origin);
        normalizedUrl.searchParams.delete("ajax");
        var normalizedUrlString = normalizedUrl.toString();
        var requestSerial = userFetchRequestSerial + 1;
        userFetchRequestSerial = requestSerial;

        deactivateUserBulkMode();

        fetch(getAjaxUrl(normalizedUrlString), {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json"
            }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error("Failed to load users page.");
                }
                return response.json();
            })
            .then(function (data) {
                if (requestSerial !== userFetchRequestSerial) {
                    return;
                }

                userTableBody.innerHTML = data.rows_html || "";
                userPagination.innerHTML = data.pagination_html || "";
                if (userTableCount && typeof data.total !== "undefined") {
                    userTableCount.textContent = "Total: " + data.total + " users";
                }

                var browserUrl = new URL(normalizedUrlString, window.location.origin);
                browserUrl.searchParams.delete("ajax");
                if (nextOptions.replaceHistory === true) {
                    window.history.replaceState({}, "", browserUrl.toString());
                } else {
                    window.history.pushState({}, "", browserUrl.toString());
                }

                syncUserFilterControlsFromUrl(browserUrl.toString());
                scrollToUsersTop();
            })
            .catch(function () {});
    }

    function scrollToUsersTop() {
        if (!userTableBody) {
            return;
        }

        var userSection = userTableBody.closest("section.management-card");
        if (userSection && typeof userSection.scrollIntoView === "function") {
            userSection.scrollIntoView({ behavior: "smooth", block: "start" });
            return;
        }

        window.scrollTo({ top: 0, behavior: "smooth" });
    }

    function fetchActivityLogPage(url) {
        if (!activityLogTableBody || !activityLogPagination || !url) {
            return;
        }

        fetch(getAjaxUrl(url), {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json"
            }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error("Failed to load activity log page.");
                }
                return response.json();
            })
            .then(function (data) {
                activityLogTableBody.innerHTML = data.rows_html || "";
                activityLogPagination.innerHTML = data.pagination_html || "";
                if (activityLogTableCount && typeof data.total !== "undefined") {
                    activityLogTableCount.textContent = "Total: " + data.total + " records";
                }
                var browserUrl = new URL(url, window.location.origin);
                browserUrl.searchParams.delete("ajax");
                window.history.pushState({}, "", browserUrl.toString());
                scrollToActivityLogTop();
            })
            .catch(function () {});
    }

    function scrollToActivityLogTop() {
        if (!activityLogTableBody) {
            return;
        }

        var activityLogSection = activityLogTableBody.closest("section.management-card");
        if (activityLogSection && typeof activityLogSection.scrollIntoView === "function") {
            activityLogSection.scrollIntoView({ behavior: "smooth", block: "start" });
            return;
        }

        window.scrollTo({ top: 0, behavior: "smooth" });
    }

    if (addUserForm) {
        addUserForm.addEventListener("submit", function (event) {
            event.preventDefault();

            var formData = new FormData(addUserForm);
            if (addUserAjaxErrors) {
                addUserAjaxErrors.classList.add("hidden");
                addUserAjaxErrors.innerHTML = "";
            }

            fetch(addUserForm.getAttribute("action"), {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken
                },
                body: formData
            })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, status: response.status, data: data };
                    });
                })
                .then(function (result) {
                    if (!result.ok) {
                        if (result.status === 422 && addUserAjaxErrors) {
                            var html = "";
                            Object.keys(result.data.errors || {}).forEach(function (key) {
                                var messages = result.data.errors[key];
                                messages.forEach(function (msg) {
                                    html += "<div>" + msg + "</div>";
                                });
                            });
                            addUserAjaxErrors.innerHTML = html || "<div>Validation failed.</div>";
                            addUserAjaxErrors.classList.remove("hidden");
                        }
                        return;
                    }

                    addUserForm.reset();
                    if (newLevelSelect) {
                        newLevelSelect.value = "";
                        newLevelSelect.dispatchEvent(new Event("change"));
                    }
                    closeModal();
                    fetchUsersPage(window.location.href);
                })
                .catch(function () {});
        });
    }

    if ((userSearchInput || userRoleFilter) && userTableBody && userPagination) {
        syncUserFilterControlsFromUrl(window.location.href);

        if (userSearchInput) {
            userSearchInput.addEventListener("input", function () {
                if (userSearchDebounceTimer) {
                    window.clearTimeout(userSearchDebounceTimer);
                }

                userSearchDebounceTimer = window.setTimeout(function () {
                    fetchUsersPage(buildUsersListUrl(1), { replaceHistory: true });
                }, USER_SEARCH_DEBOUNCE_MS);
            });

            userSearchInput.addEventListener("keydown", function (event) {
                if (event.key !== "Enter") {
                    return;
                }

                event.preventDefault();
                if (userSearchDebounceTimer) {
                    window.clearTimeout(userSearchDebounceTimer);
                    userSearchDebounceTimer = 0;
                }
                fetchUsersPage(buildUsersListUrl(1), { replaceHistory: true });
            });
        }

        if (userRoleFilter) {
            userRoleFilter.addEventListener("change", function () {
                fetchUsersPage(buildUsersListUrl(1), { replaceHistory: true });
            });
        }
    }

    if (userPagination && userTableBody) {
        userPagination.addEventListener("click", function (event) {
            var target = event.target.closest("a.page-link");
            if (!target) {
                return;
            }

            event.preventDefault();
            fetchUsersPage(target.getAttribute("href"));
        });
    }

    if (activityLogPagination && activityLogTableBody) {
        activityLogPagination.addEventListener("click", function (event) {
            var target = event.target.closest("a.page-link");
            if (!target) {
                return;
            }

            event.preventDefault();
            fetchActivityLogPage(target.getAttribute("href"));
        });
    }

    if (adminProfileForm) {
        adminProfileForm.addEventListener("submit", function (event) {
            event.preventDefault();

            if (accountAdminAjaxAlert) {
                accountAdminAjaxAlert.className = "hidden";
                accountAdminAjaxAlert.innerHTML = "";
            }

            var formData = new FormData(adminProfileForm);
            setFormLoadingState(adminProfileForm, true, "Saving...");
            fetch(adminProfileForm.getAttribute("action"), {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": csrfToken
                },
                body: formData
            })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, status: response.status, data: data };
                    });
                })
                .then(function (result) {
                    if (!accountAdminAjaxAlert) {
                        return;
                    }

                    if (!result.ok) {
                        var html = "";
                        if (result.status === 422 && result.data.errors) {
                            Object.keys(result.data.errors).forEach(function (key) {
                                var messages = result.data.errors[key];
                                messages.forEach(function (msg) {
                                    html += "<div>" + msg + "</div>";
                                });
                            });
                        } else {
                            html = "<div>" + (result.data.message || "Failed to save profile.") + "</div>";
                        }

                        accountAdminAjaxAlert.className = "alert-error";
                        accountAdminAjaxAlert.innerHTML = html;
                        return;
                    }

                    accountAdminAjaxAlert.className = "hidden";
                    accountAdminAjaxAlert.innerHTML = "";
                    openProfileSaveSuccessModal(result.data.message || "Profile updated successfully.");

                    if (result.data.profile) {
                        var adminNameInput = document.getElementById("accountAdminName");
                        var adminEmailInput = document.getElementById("accountAdminEmail");
                        var adminPhoneInput = document.getElementById("accountAdminPhone");

                        if (adminNameInput) {
                            adminNameInput.value = result.data.profile.name || "";
                        }
                        if (adminEmailInput) {
                            adminEmailInput.value = result.data.profile.email || "";
                        }
                        if (adminPhoneInput) {
                            adminPhoneInput.value = result.data.profile.phonenumber || "";
                        }

                        updateNavbarWelcomeName(result.data.profile.name || "");
                    }
                })
                .catch(function () {
                    if (accountAdminAjaxAlert) {
                        accountAdminAjaxAlert.className = "alert-error";
                        accountAdminAjaxAlert.innerHTML = "<div>Failed to save profile.</div>";
                    }
                })
                .finally(function () {
                    setFormLoadingState(adminProfileForm, false);
                });
        });
    }

    if (systemLogoInput) {
        systemLogoInput.addEventListener("change", function (event) {
            var file = event.target.files && event.target.files[0];
            if (!file) {
                return;
            }

            openPhotoCropModal(file, {
                target: "system-logo",
                shape: "square",
                title: "Crop Website Logo",
                description: "Move the image and drag the corner dots to resize the crop area."
            });
        });
    }

    if (newLevelSelect && newRoleSelect) {
        var roleOptions = Array.prototype.slice.call(newRoleSelect.querySelectorAll("option"));

        function calculateAgeFromBirthdate(value) {
            if (!value) {
                return "";
            }

            var birthdate = new Date(value + "T00:00:00");
            if (Number.isNaN(birthdate.getTime())) {
                return "";
            }

            var today = new Date();
            var age = today.getFullYear() - birthdate.getFullYear();
            var monthDiff = today.getMonth() - birthdate.getMonth();
            var hasBirthdayPassed = monthDiff > 0 || (monthDiff === 0 && today.getDate() >= birthdate.getDate());

            if (!hasBirthdayPassed) {
                age -= 1;
            }

            return age >= 0 ? String(age) : "";
        }

        function updateFamilyAgeDisplay() {
            if (!familyAgeInput) {
                return;
            }

            familyAgeInput.value = calculateAgeFromBirthdate(
                familyBirthdateInput ? familyBirthdateInput.value : ""
            );
        }

        function applyRoleFilter() {
            var selectedLevelOption = newLevelSelect.options[newLevelSelect.selectedIndex];
            var selectedLevelValue = newLevelSelect.value;
            var levelGroup = selectedLevelOption ? selectedLevelOption.getAttribute("data-level-group") : "";
            var hasLevel = Boolean(selectedLevelValue);
            var isFamily = selectedLevelValue === "2" || selectedLevelValue === "4" || levelGroup === "family";
            var isEmployer = hasLevel && !isFamily;
            var targetRoleGroup = isFamily ? "family" : "employer";

            if (dynamicFields) {
                dynamicFields.classList.toggle("hidden", !hasLevel);
            }

            newRoleSelect.disabled = !hasLevel;
            if (!hasLevel) {
                newRoleSelect.value = "";
            }

            roleOptions.forEach(function (option, index) {
                if (index === 0) {
                    option.hidden = false;
                    option.disabled = false;
                    return;
                }

                var optionGroup = option.getAttribute("data-role-group");
                if (!optionGroup) {
                    var optionValue = option.value;
                    optionGroup = (optionValue === "3" || optionValue === "4") ? "family" : "employer";
                }
                var isMatch = hasLevel && optionGroup === targetRoleGroup;
                option.hidden = !isMatch;
                option.disabled = !isMatch;
            });

            if (newRoleSelect.value) {
                var current = newRoleSelect.options[newRoleSelect.selectedIndex];
                if (current && current.disabled) {
                    newRoleSelect.value = "";
                }
            }

            if (newRoleField) {
                newRoleField.classList.remove("hidden");
            }

            if (!hasLevel) {
                if (newUsernameInput) {
                    newUsernameInput.value = "";
                }
                if (newNameInput) {
                    newNameInput.value = "";
                }
                if (newEmailInput) {
                    newEmailInput.value = "";
                }
                if (newPhoneInput) {
                    newPhoneInput.value = "";
                }
                if (familyGenderInput) {
                    familyGenderInput.value = "";
                }
                if (familyAddressInput) {
                    familyAddressInput.value = "";
                }
                if (familyLifeStatusInput) {
                    familyLifeStatusInput.value = "";
                }
                if (familyMaritalStatusInput) {
                    familyMaritalStatusInput.value = "";
                }
                if (familyBirthdateInput) {
                    familyBirthdateInput.value = "";
                }
                if (familyAgeInput) {
                    familyAgeInput.value = "";
                }
                if (familyBirthplaceInput) {
                    familyBirthplaceInput.value = "";
                }
            }

            if (contactFields) {
                contactFields.classList.toggle("hidden", !hasLevel);
            }

            if (familyFields) {
                familyFields.classList.toggle("hidden", !isFamily);
            }
            if (!isFamily && familyAgeInput) {
                familyAgeInput.value = "";
            }

            if (newEmailInput) {
                newEmailInput.required = hasLevel;
            }

            if (newPhoneInput) {
                newPhoneInput.required = hasLevel;
            }

            if (familyGenderInput) {
                familyGenderInput.required = isFamily;
            }
            if (familyAddressInput) {
                familyAddressInput.required = isFamily;
            }
            if (familyLifeStatusInput) {
                familyLifeStatusInput.required = isFamily;
            }
            if (familyMaritalStatusInput) {
                familyMaritalStatusInput.required = isFamily;
            }
            if (familyBirthdateInput) {
                familyBirthdateInput.required = isFamily;
            }
            if (familyBirthplaceInput) {
                familyBirthplaceInput.required = isFamily;
            }

            if (newUsernameInput) {
                newUsernameInput.required = hasLevel;
            }
            if (newNameInput) {
                newNameInput.required = hasLevel;
            }
            newRoleSelect.required = hasLevel && !isFamily;

            updateFamilyAgeDisplay();
        }

        if (familyBirthdateInput) {
            familyBirthdateInput.addEventListener("change", updateFamilyAgeDisplay);
            familyBirthdateInput.addEventListener("input", updateFamilyAgeDisplay);
        }

        newLevelSelect.addEventListener("change", applyRoleFilter);
        applyRoleFilter();
    }

    if (treeContainer) {
        // Keep this in sync with CSS --tree-display-base-zoom to avoid refresh flicker.
        var treeDisplayBaseZoom = Math.max(0.05, getTreeCssNumber("--tree-display-base-zoom", 0.78));
        var currentTreeZoom = treeDisplayBaseZoom;
        var minTreeZoom = 0.45;
        var maxTreeZoom = 3;
        var treeZoomStep = 0.3;
        var treeVirtualPaddingX = 0;
        var treeVirtualPaddingY = 0;
        var treeViewportInitialized = false;
        var treeZoomAnimationFrame = 0;
        var treeZoomAnimationDuration = 60;
        var treeLastCursorClientX = 0;
        var treeLastCursorClientY = 0;
        var treeHasCursorAnchor = false;
        var isTreeDragging = false;
        var activeTreePointerId = null;
        var treeDragStartX = 0;
        var treeDragStartY = 0;
        var treeStartScrollLeft = 0;
        var treeStartScrollTop = 0;
        var treeMovedDistance = 0;
        var suppressTreeClick = false;
        var treeNoDragSelector = "button, input, select, textarea, a, label, .member-card, .member-card *";
        var treeInitialCenteringDeadline = 0;
        var treeUserHasInteracted = false;
        var treePointerMap = {};
        var treePinchActive = false;
        var treePinchStartDistance = 0;
        var treePinchStartZoom = 0;

        treeContainer.classList.add("is-pannable");
        treeContainer.style.touchAction = "none";

        function syncTreeToggleFollowPosition() {
            var offsetX = 0;
            if (treeCanvas && treeContainer) {
                var treeRect = treeCanvas.getBoundingClientRect();
                var containerRect = treeContainer.getBoundingClientRect();
                offsetX = Math.round(
                    (treeRect.left + (treeRect.width / 2))
                    - (containerRect.left + (containerRect.width / 2))
                );
            }

            function applyInlineToggleOffset(toggleElement) {
                if (!toggleElement) {
                    return;
                }
                if (!toggleElement.classList.contains("tree-inline-toggle")) {
                    return;
                }

                var containerWidth = treeContainer ? treeContainer.clientWidth : 0;
                var toggleWidth = toggleElement.offsetWidth || 0;
                var maxShift = Math.max(0, Math.floor((containerWidth - toggleWidth) / 2) - 12);
                var clampedOffsetX = Math.max(-maxShift, Math.min(maxShift, offsetX));
                toggleElement.style.transform = "translateX(calc(-50% + " + clampedOffsetX + "px))";
            }

            if (treeToggleTopBtn) {
                applyInlineToggleOffset(treeToggleTopBtn);
            }

            if (treeToggleBottomWrap) {
                applyInlineToggleOffset(treeToggleBottomWrap);
            }
        }

        function getTreeViewportCenterClientPoint() {
            var containerRect = treeContainer.getBoundingClientRect();
            return {
                x: containerRect.left + (treeContainer.clientWidth / 2),
                y: containerRect.top + (treeContainer.clientHeight / 2)
            };
        }

        function updateTreeCursorAnchor(clientX, clientY) {
            if (typeof clientX !== "number" || typeof clientY !== "number") {
                return;
            }

            treeLastCursorClientX = clientX;
            treeLastCursorClientY = clientY;
            treeHasCursorAnchor = true;
        }

        function getTreeZoomAnchorPoint() {
            if (treeHasCursorAnchor) {
                return {
                    x: treeLastCursorClientX,
                    y: treeLastCursorClientY
                };
            }

            return getTreeViewportCenterClientPoint();
        }

        function setTreePointerState(event) {
            if (!event || typeof event.pointerId !== "number") {
                return;
            }

            treePointerMap[event.pointerId] = {
                x: event.clientX,
                y: event.clientY,
                type: event.pointerType || "mouse"
            };
        }

        function clearTreePointerState(pointerId) {
            if (typeof pointerId !== "number") {
                return;
            }

            delete treePointerMap[pointerId];
        }

        function getActiveTreeTouchPointers() {
            return Object.keys(treePointerMap)
                .map(function (pointerId) {
                    return treePointerMap[pointerId];
                })
                .filter(function (pointerState) {
                    return pointerState && pointerState.type === "touch";
                });
        }

        function getPointerDistance(leftPointer, rightPointer) {
            var deltaX = rightPointer.x - leftPointer.x;
            var deltaY = rightPointer.y - leftPointer.y;
            return Math.sqrt((deltaX * deltaX) + (deltaY * deltaY));
        }

        function getPointerMidpoint(leftPointer, rightPointer) {
            return {
                x: (leftPointer.x + rightPointer.x) / 2,
                y: (leftPointer.y + rightPointer.y) / 2
            };
        }

        function cancelTreeDragState() {
            if (activeTreePointerId !== null && treeContainer.hasPointerCapture && treeContainer.hasPointerCapture(activeTreePointerId)) {
                try {
                    treeContainer.releasePointerCapture(activeTreePointerId);
                } catch (error) {
                    // Ignore release errors.
                }
            }

            isTreeDragging = false;
            activeTreePointerId = null;
            treeContainer.classList.remove("is-dragging");
        }

        function startTreePinchFromCurrentPointers() {
            var touchPointers = getActiveTreeTouchPointers();
            if (touchPointers.length < 2) {
                return false;
            }

            touchPointers.sort(function (left, right) {
                return ((left.x + left.y) || 0) - ((right.x + right.y) || 0);
            });

            var leftPointer = touchPointers[0];
            var rightPointer = touchPointers[1];
            var distance = getPointerDistance(leftPointer, rightPointer);
            if (distance <= 0) {
                return false;
            }

            cancelTreeDragState();
            treePinchActive = true;
            treePinchStartDistance = distance;
            treePinchStartZoom = currentTreeZoom;
            var midpoint = getPointerMidpoint(leftPointer, rightPointer);
            updateTreeCursorAnchor(midpoint.x, midpoint.y);
            return true;
        }

        function updateTreePinchZoom() {
            if (!treePinchActive) {
                return;
            }

            var touchPointers = getActiveTreeTouchPointers();
            if (touchPointers.length < 2) {
                treePinchActive = false;
                treePinchStartDistance = 0;
                treePinchStartZoom = 0;
                suppressTreeClick = true;
                return;
            }

            touchPointers.sort(function (left, right) {
                return ((left.x + left.y) || 0) - ((right.x + right.y) || 0);
            });

            var leftPointer = touchPointers[0];
            var rightPointer = touchPointers[1];
            var currentDistance = getPointerDistance(leftPointer, rightPointer);
            if (treePinchStartDistance <= 0 || currentDistance <= 0) {
                return;
            }

            var midpoint = getPointerMidpoint(leftPointer, rightPointer);
            var nextZoom = treePinchStartZoom * (currentDistance / treePinchStartDistance);
            setTreeZoom(nextZoom, midpoint.x, midpoint.y, false);
            suppressTreeClick = false;
        }

        function endTreePinchIfNeeded() {
            var touchPointers = getActiveTreeTouchPointers();
            if (treePinchActive && touchPointers.length < 2) {
                treePinchActive = false;
                treePinchStartDistance = 0;
                treePinchStartZoom = 0;
                suppressTreeClick = true;
            }
        }

        function computeTreeVirtualPadding() {
            var containerWidth = treeContainer ? treeContainer.clientWidth : 0;
            var containerHeight = treeContainer ? treeContainer.clientHeight : 0;

            treeVirtualPaddingX = Math.max(700, Math.round(containerWidth * 1.6));
            treeVirtualPaddingY = Math.max(520, Math.round(containerHeight * 1.6));
        }

        function refreshTreeZoomSize(options) {
            if (!treeCanvas || !treeZoomStage) {
                return;
            }

            var nextOptions = options || {};
            computeTreeVirtualPadding();

            treeZoomStage.style.padding = treeVirtualPaddingY + "px " + treeVirtualPaddingX + "px";
            treeCanvas.style.transformOrigin = "top left";
            treeCanvas.style.transform = "scale(" + currentTreeZoom + ")";
            treeCanvas.style.willChange = "auto";
            treeCanvas.style.backfaceVisibility = "visible";

            var scaledCanvasWidth = treeCanvas.offsetWidth * currentTreeZoom;
            var scaledCanvasHeight = treeCanvas.offsetHeight * currentTreeZoom;
            treeZoomStage.style.width = Math.ceil(scaledCanvasWidth + (treeVirtualPaddingX * 2)) + "px";
            treeZoomStage.style.height = Math.ceil(scaledCanvasHeight + (treeVirtualPaddingY * 2)) + "px";

            if (treeZoomValue) {
                treeZoomValue.textContent = Math.round((currentTreeZoom / treeDisplayBaseZoom) * 100) + "%";
            }

            if (treeZoomInBtn) {
                treeZoomInBtn.disabled = currentTreeZoom >= maxTreeZoom;
            }

            if (treeZoomOutBtn) {
                treeZoomOutBtn.disabled = currentTreeZoom <= minTreeZoom;
            }

            scheduleTreeConnectorDraw();

            if (
                (nextOptions.recenter === true || !treeViewportInitialized)
                && !treeUserHasInteracted
                && treeContainer.clientWidth > 0
                && treeContainer.clientHeight > 0
            ) {
                treeContainer.scrollLeft = Math.max(0, Math.round(treeVirtualPaddingX + ((scaledCanvasWidth - treeContainer.clientWidth) / 2)));
                treeContainer.scrollTop = Math.max(0, Math.round(treeVirtualPaddingY + ((scaledCanvasHeight - treeContainer.clientHeight) / 2)));
                treeViewportInitialized = true;
            }

            syncTreeToggleFollowPosition();
        }

        function stopInitialTreeCentering() {
            treeInitialCenteringDeadline = 0;
        }

        function markTreeAsInteracted() {
            treeUserHasInteracted = true;
            stopInitialTreeCentering();
        }

        function runInitialTreeCenteringLoop() {
            if (!treeInitialCenteringDeadline) {
                return;
            }

            if (treeUserHasInteracted || isTreeDragging) {
                treeInitialCenteringDeadline = 0;
                return;
            }

            refreshTreeZoomSize({ recenter: true });
            treeInitialCenteringDeadline = 0;
        }

        function startInitialTreeCentering(durationMs) {
            if (treeUserHasInteracted) {
                return;
            }

            var centeringWindowMs = typeof durationMs === "number" ? durationMs : 180;
            treeInitialCenteringDeadline = Date.now() + Math.max(200, centeringWindowMs);
            runInitialTreeCenteringLoop();
        }

        recenterTreeViewport = function (options) {
            var nextOptions = options || {};
            var durationMs = typeof nextOptions.durationMs === "number" ? nextOptions.durationMs : 220;
            var shouldResetInteraction = nextOptions.resetInteraction !== false;

            refreshTreeDomState();

            if (shouldResetInteraction) {
                treeUserHasInteracted = false;
            }

            treeViewportInitialized = false;
            stopInitialTreeCentering();
            refreshTreeZoomSize({ recenter: true });
            startInitialTreeCentering(durationMs);
        };

        function applyTreeZoom(nextZoom, anchorClientX, anchorClientY) {
            if (!treeCanvas || !treeZoomStage) {
                return;
            }

            var boundedZoom = Math.max(minTreeZoom, Math.min(maxTreeZoom, nextZoom));
            var safeZoom = Math.round(boundedZoom * 100) / 100;
            if (safeZoom === currentTreeZoom) {
                return;
            }

            var centerPoint = getTreeViewportCenterClientPoint();
            var resolvedAnchorClientX = typeof anchorClientX === "number" ? anchorClientX : centerPoint.x;
            var resolvedAnchorClientY = typeof anchorClientY === "number" ? anchorClientY : centerPoint.y;
            var previousCanvasRect = treeCanvas.getBoundingClientRect();
            var oldZoom = currentTreeZoom;
            var anchorCanvasUnscaledX = (resolvedAnchorClientX - previousCanvasRect.left) / oldZoom;
            var anchorCanvasUnscaledY = (resolvedAnchorClientY - previousCanvasRect.top) / oldZoom;

            currentTreeZoom = safeZoom;
            refreshTreeZoomSize();

            var latestCanvasRect = treeCanvas.getBoundingClientRect();
            var desiredCanvasLeft = resolvedAnchorClientX - (anchorCanvasUnscaledX * currentTreeZoom);
            var desiredCanvasTop = resolvedAnchorClientY - (anchorCanvasUnscaledY * currentTreeZoom);
            var scrollDeltaX = latestCanvasRect.left - desiredCanvasLeft;
            var scrollDeltaY = latestCanvasRect.top - desiredCanvasTop;

            treeContainer.scrollLeft = Math.round(treeContainer.scrollLeft + scrollDeltaX);
            treeContainer.scrollTop = Math.round(treeContainer.scrollTop + scrollDeltaY);
            syncTreeToggleFollowPosition();
        }

        function animateTreeZoom(nextZoom, anchorClientX, anchorClientY) {
            if (!window.requestAnimationFrame) {
                applyTreeZoom(nextZoom, anchorClientX, anchorClientY);
                return;
            }

            var boundedTarget = Math.max(minTreeZoom, Math.min(maxTreeZoom, nextZoom));
            var startZoom = currentTreeZoom;
            if (Math.abs(boundedTarget - startZoom) < 0.001) {
                return;
            }

            if (treeZoomAnimationFrame) {
                window.cancelAnimationFrame(treeZoomAnimationFrame);
                treeZoomAnimationFrame = 0;
            }

            var animationStart = window.performance && window.performance.now
                ? window.performance.now()
                : Date.now();

            var stepZoom = function (timestamp) {
                var now = typeof timestamp === "number"
                    ? timestamp
                    : (window.performance && window.performance.now ? window.performance.now() : Date.now());
                var progress = Math.min(1, Math.max(0, (now - animationStart) / treeZoomAnimationDuration));
                var eased = 1 - Math.pow(1 - progress, 3);
                var animatedZoom = startZoom + ((boundedTarget - startZoom) * eased);

                applyTreeZoom(animatedZoom, anchorClientX, anchorClientY);

                if (progress < 1) {
                    treeZoomAnimationFrame = window.requestAnimationFrame(stepZoom);
                    return;
                }

                treeZoomAnimationFrame = 0;
            };

            treeZoomAnimationFrame = window.requestAnimationFrame(stepZoom);
        }

        function setTreeZoom(nextZoom, anchorClientX, anchorClientY, smooth) {
            if (smooth === false) {
                applyTreeZoom(nextZoom, anchorClientX, anchorClientY);
                return;
            }

            animateTreeZoom(nextZoom, anchorClientX, anchorClientY);
        }

        treeViewportInitialized = false;
        refreshTreeZoomSize({ recenter: true });
        treeViewportInitialized = true;

        if (treeZoomInBtn) {
            treeZoomInBtn.addEventListener("click", function () {
                markTreeAsInteracted();
                var anchorPoint = getTreeZoomAnchorPoint();
                setTreeZoom(currentTreeZoom + treeZoomStep, anchorPoint.x, anchorPoint.y);
            });
        }

        if (treeZoomOutBtn) {
            treeZoomOutBtn.addEventListener("click", function () {
                markTreeAsInteracted();
                var anchorPoint = getTreeZoomAnchorPoint();
                setTreeZoom(currentTreeZoom - treeZoomStep, anchorPoint.x, anchorPoint.y);
            });
        }

        function saveTreeAsImage() {
            if (!treeCanvas || !window.html2canvas) {
                window.alert("Save image is unavailable right now.");
                return;
            }

            var previousLabel = saveTreeImageBtn ? saveTreeImageBtn.textContent : "";
            var previousDisabled = saveTreeImageBtn ? saveTreeImageBtn.disabled : false;
            var previousTreeTransform = treeCanvas.style.transform;
            var previousTreeTransformOrigin = treeCanvas.style.transformOrigin;
            var previousStageWidth = treeZoomStage ? treeZoomStage.style.width : "";
            var previousStageHeight = treeZoomStage ? treeZoomStage.style.height : "";

            if (saveTreeImageBtn) {
                saveTreeImageBtn.disabled = true;
                saveTreeImageBtn.textContent = "Saving...";
            }

            treeCanvas.style.transform = "none";
            treeCanvas.style.transformOrigin = "top left";

            if (treeZoomStage) {
                treeZoomStage.style.width = Math.ceil(treeCanvas.offsetWidth) + "px";
                treeZoomStage.style.height = Math.ceil(treeCanvas.offsetHeight) + "px";
            }

            window.html2canvas(treeCanvas, {
                backgroundColor: "#f4f8fb",
                scale: 2,
                useCORS: true,
                logging: false
            })
                .then(function (canvas) {
                    var downloadLink = document.createElement("a");
                    var dateTag = new Date().toISOString().slice(0, 10);
                    downloadLink.href = canvas.toDataURL("image/png");
                    downloadLink.download = "family-tree-" + dateTag + ".png";
                    downloadLink.click();
                })
                .catch(function () {
                    window.alert("Failed to save family tree image.");
                })
                .finally(function () {
                    treeCanvas.style.transform = previousTreeTransform;
                    treeCanvas.style.transformOrigin = previousTreeTransformOrigin;

                    if (treeZoomStage) {
                        treeZoomStage.style.width = previousStageWidth;
                        treeZoomStage.style.height = previousStageHeight;
                    }

                    if (saveTreeImageBtn) {
                        saveTreeImageBtn.disabled = previousDisabled;
                        saveTreeImageBtn.textContent = previousLabel || "Save Image";
                    }
                });
        }

        if (saveTreeImageBtn) {
            saveTreeImageBtn.addEventListener("click", saveTreeAsImage);
        }

        window.addEventListener("resize", function () {
            refreshTreeZoomSize();
            scheduleTreeConnectorDraw();
        });

        treeContainer.addEventListener("scroll", function () {
            syncTreeToggleFollowPosition();
        }, { passive: true });

        treeContainer.addEventListener("pointerdown", function (event) {
            if (event.pointerType === "mouse" && event.button !== 0) {
                return;
            }

            markTreeAsInteracted();
            updateTreeCursorAnchor(event.clientX, event.clientY);
            setTreePointerState(event);

            if (event.pointerType === "touch" && startTreePinchFromCurrentPointers()) {
                event.preventDefault();
                return;
            }

            if (event.target && event.target.closest(treeNoDragSelector)) {
                suppressTreeClick = false;
                return;
            }

            if (treePinchActive) {
                event.preventDefault();
                return;
            }

            isTreeDragging = true;
            activeTreePointerId = event.pointerId;
            treeMovedDistance = 0;
            treeDragStartX = event.clientX;
            treeDragStartY = event.clientY;
            treeStartScrollLeft = treeContainer.scrollLeft;
            treeStartScrollTop = treeContainer.scrollTop;
            treeContainer.classList.add("is-dragging");
            treeContainer.setPointerCapture(event.pointerId);
            event.preventDefault();
        });

        treeContainer.addEventListener("pointermove", function (event) {
            updateTreeCursorAnchor(event.clientX, event.clientY);
            setTreePointerState(event);

            if (treePinchActive) {
                updateTreePinchZoom();
                event.preventDefault();
                return;
            }

            if (!isTreeDragging || event.pointerId !== activeTreePointerId) {
                return;
            }

            var deltaX = event.clientX - treeDragStartX;
            var deltaY = event.clientY - treeDragStartY;
            treeMovedDistance = Math.max(treeMovedDistance, Math.abs(deltaX), Math.abs(deltaY));

            treeContainer.scrollLeft = treeStartScrollLeft - deltaX;
            treeContainer.scrollTop = treeStartScrollTop - deltaY;
            syncTreeToggleFollowPosition();
            event.preventDefault();
        });

        function endTreeDrag(pointerId) {
            if (!isTreeDragging || pointerId !== activeTreePointerId) {
                return;
            }

            isTreeDragging = false;
            activeTreePointerId = null;
            treeContainer.classList.remove("is-dragging");
            suppressTreeClick = treeMovedDistance > 6;
        }

        treeContainer.addEventListener("pointerup", function (event) {
            clearTreePointerState(event.pointerId);
            endTreePinchIfNeeded();
            endTreeDrag(event.pointerId);
        });

        treeContainer.addEventListener("pointercancel", function (event) {
            clearTreePointerState(event.pointerId);
            endTreePinchIfNeeded();
            endTreeDrag(event.pointerId);
        });

        // Trackpad gestures:
        // - Pinch (two fingers close/open) => zoom (reported as ctrl+wheel in Chromium-based browsers).
        // - Two-finger scroll in same direction => pan tree viewport.
        treeContainer.addEventListener("wheel", function (event) {
            if (!treeCanvas || !treeZoomStage) {
                return;
            }

            markTreeAsInteracted();
            updateTreeCursorAnchor(event.clientX, event.clientY);

            var hasHorizontalDelta = Math.abs(event.deltaX) > 0;
            var hasVerticalDelta = Math.abs(event.deltaY) > 0;
            var isPinchZoomGesture = event.ctrlKey === true;

            if (isPinchZoomGesture && hasVerticalDelta) {
                if (treeZoomAnimationFrame) {
                    window.cancelAnimationFrame(treeZoomAnimationFrame);
                    treeZoomAnimationFrame = 0;
                }

                var zoomFactor = Math.exp(event.deltaY * -0.0048);
                var nextZoom = Math.max(minTreeZoom, Math.min(maxTreeZoom, currentTreeZoom * zoomFactor));
                setTreeZoom(nextZoom, event.clientX, event.clientY, false);
                event.preventDefault();
                return;
            }

            if (!hasHorizontalDelta && !hasVerticalDelta) {
                return;
            }

            var deltaScale = 1;
            if (event.deltaMode === 1) {
                deltaScale = 16;
            } else if (event.deltaMode === 2) {
                deltaScale = Math.max(1, treeContainer.clientHeight);
            }

            var panX = event.deltaX * deltaScale;
            var panY = event.deltaY * deltaScale;

            // Shift+wheel commonly maps vertical wheel into horizontal movement.
            if (event.shiftKey && !hasHorizontalDelta && hasVerticalDelta) {
                panX = panY;
                panY = 0;
            }

            treeContainer.scrollLeft += panX;
            treeContainer.scrollTop += panY;
            syncTreeToggleFollowPosition();
            event.preventDefault();
        }, { passive: false });

        treeContainer.addEventListener("click", function (event) {
            if (!suppressTreeClick) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            suppressTreeClick = false;
        }, true);

        treeContainer.addEventListener("mouseleave", function () {
            if (isTreeDragging) {
                treeContainer.classList.remove("is-dragging");
            }
        });
    }

    function clearProfileValidationState() {
        if (!profileForm) {
            return;
        }

        var invalidFields = profileForm.querySelectorAll(".is-invalid");
        Array.prototype.forEach.call(invalidFields, function (field) {
            field.classList.remove("is-invalid");
        });

        var dynamicErrors = profileForm.querySelectorAll(".field-error-text-dynamic");
        Array.prototype.forEach.call(dynamicErrors, function (errorElement) {
            if (errorElement && errorElement.parentNode) {
                errorElement.parentNode.removeChild(errorElement);
            }
        });
    }

    function appendProfileFieldError(field, message) {
        if (!field || !message) {
            return;
        }

        field.classList.add("is-invalid");

        var host = field.closest(".account-new-social-field")
            || field.closest(".settings-field")
            || field.parentElement;
        if (!host) {
            return;
        }

        var errorElement = host.querySelector(".field-error-text-dynamic");
        if (!errorElement) {
            errorElement = document.createElement("small");
            errorElement.className = "field-error-text field-error-text-dynamic";
            host.appendChild(errorElement);
        }

        errorElement.textContent = String(message);
    }

    function getProfileSocialRowByIndex(index) {
        if (!profileForm || index < 0) {
            return null;
        }

        var socialRows = profileForm.querySelectorAll(".account-new-social-row");
        if (!socialRows || index >= socialRows.length) {
            return null;
        }

        return socialRows[index];
    }

    function loadImageElementFromFile(file) {
        return new Promise(function (resolve, reject) {
            if (!file) {
                reject(new Error("Image file is missing."));
                return;
            }

            var objectUrl = URL.createObjectURL(file);
            var image = new Image();
            image.onload = function () {
                URL.revokeObjectURL(objectUrl);
                resolve(image);
            };
            image.onerror = function () {
                URL.revokeObjectURL(objectUrl);
                reject(new Error("Failed to read image file."));
            };
            image.src = objectUrl;
        });
    }

    function loadScriptOnce(src) {
        return new Promise(function (resolve, reject) {
            if (!src) {
                reject(new Error("Script URL is missing."));
                return;
            }

            var existingScript = document.querySelector('script[data-face-api-src="' + src + '"]');
            if (existingScript) {
                if (existingScript.getAttribute("data-loaded") === "1") {
                    resolve();
                    return;
                }

                existingScript.addEventListener("load", function onExistingLoad() {
                    existingScript.setAttribute("data-loaded", "1");
                    resolve();
                }, { once: true });
                existingScript.addEventListener("error", function onExistingError() {
                    reject(new Error("Failed to load face detection script."));
                }, { once: true });
                return;
            }

            var scriptElement = document.createElement("script");
            scriptElement.src = src;
            scriptElement.async = true;
            scriptElement.defer = true;
            scriptElement.setAttribute("data-face-api-src", src);
            scriptElement.addEventListener("load", function () {
                scriptElement.setAttribute("data-loaded", "1");
                resolve();
            }, { once: true });
            scriptElement.addEventListener("error", function () {
                reject(new Error("Failed to load face detection script."));
            }, { once: true });
            document.head.appendChild(scriptElement);
        });
    }

    function ensureFaceApiReady() {
        if (window.faceapi && window.faceapi.nets && window.faceapi.nets.tinyFaceDetector && window.faceapi.nets.tinyFaceDetector.isLoaded) {
            return Promise.resolve(window.faceapi);
        }

        if (!faceApiScriptLoadPromise) {
            faceApiScriptLoadPromise = loadScriptOnce("https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js");
        }

        return faceApiScriptLoadPromise
            .then(function () {
                if (!window.faceapi) {
                    throw new Error("Face detection library is unavailable.");
                }

                if (window.faceapi.nets && window.faceapi.nets.tinyFaceDetector && window.faceapi.nets.tinyFaceDetector.isLoaded) {
                    return window.faceapi;
                }

                if (!faceApiModelLoadPromise) {
                    faceApiModelLoadPromise = window.faceapi.nets.tinyFaceDetector.loadFromUri("https://justadudewhohacks.github.io/face-api.js/models");
                }

                return faceApiModelLoadPromise.then(function () {
                    return window.faceapi;
                });
            });
    }

    function validateProfilePictureHasFace(file) {
        if (!file) {
            return Promise.resolve({ valid: true });
        }

        var detector = null;
        if (window.FaceDetector) {
            try {
                detector = new window.FaceDetector({
                    fastMode: true,
                    maxDetectedFaces: 1
                });
            } catch (error) {
                detector = null;
            }
        }

        if (detector) {
            return loadImageElementFromFile(file)
                .then(function (imageElement) {
                    return detector.detect(imageElement);
                })
                .then(function (faces) {
                    if (Array.isArray(faces) && faces.length > 0) {
                        return { valid: true };
                    }

                    return {
                        valid: false,
                        message: "Profile picture must contain a clear human face."
                    };
                })
                .catch(function () {
                    return {
                        valid: false,
                        message: "Face detection failed. Please use a clear face photo."
                    };
                });
        }

        return loadImageElementFromFile(file)
            .then(function (imageElement) {
                return ensureFaceApiReady().then(function (faceapi) {
                    return faceapi.detectSingleFace(
                        imageElement,
                        new faceapi.TinyFaceDetectorOptions({
                            inputSize: 224,
                            scoreThreshold: 0.5
                        })
                    );
                });
            })
            .then(function (detection) {
                if (detection) {
                    return { valid: true };
                }

                return {
                    valid: false,
                    message: "Profile picture must contain a clear human face."
                };
            })
            .catch(function () {
                return {
                    valid: false,
                    message: "Face detection failed. Please use a clear face photo."
                };
            });
    }

    window.familyTreeValidateProfilePictureHasFace = validateProfilePictureHasFace;
    window.familyTreeOpenProfileFaceErrorModal = openProfileFaceErrorModal;

    function applyProfileValidationErrors(errorMap) {
        if (!profileForm || !errorMap || typeof errorMap !== "object") {
            return;
        }

        var legacySocialLinkErrorsById = {};

        Object.keys(errorMap).forEach(function (key) {
            var matchLegacySocialLink = String(key || "").match(/^social_links\.(\d+)$/);
            if (!matchLegacySocialLink) {
                return;
            }

            var legacyMessages = errorMap[key];
            var legacyMessage = Array.isArray(legacyMessages) && legacyMessages.length
                ? String(legacyMessages[0] || "").trim()
                : "";
            var legacySocialId = parseInt(matchLegacySocialLink[1], 10) || 0;

            if (legacySocialId > 0 && legacyMessage !== "") {
                legacySocialLinkErrorsById[legacySocialId] = legacyMessage;
            }
        });

        Object.keys(errorMap).forEach(function (key) {
            var messages = errorMap[key];
            var message = Array.isArray(messages) && messages.length
                ? String(messages[0] || "").trim()
                : "";
            if (message === "") {
                return;
            }

            var matchSocialRowId = String(key || "").match(/^social_row_ids\.(\d+)$/);
            if (matchSocialRowId) {
                var socialRowIdIndex = parseInt(matchSocialRowId[1], 10) || 0;
                var socialRowIdRow = getProfileSocialRowByIndex(socialRowIdIndex);
                if (socialRowIdRow) {
                    appendProfileFieldError(
                        socialRowIdRow.querySelector('select[name="social_row_ids[]"]'),
                        message
                    );
                }
                return;
            }

            var matchSocialRowLink = String(key || "").match(/^social_row_links\.(\d+)$/);
            if (matchSocialRowLink) {
                var socialRowLinkIndex = parseInt(matchSocialRowLink[1], 10) || 0;
                var socialRowLinkRow = getProfileSocialRowByIndex(socialRowLinkIndex);
                if (socialRowLinkRow) {
                    appendProfileFieldError(
                        socialRowLinkRow.querySelector('input[name="social_row_links[]"]'),
                        message
                    );
                }
                return;
            }

            if (String(key || "").indexOf(".") !== -1) {
                return;
            }

            var genericField = profileForm.querySelector('[name="' + String(key || "").replace(/"/g, '\\"') + '"]');
            appendProfileFieldError(genericField, message);
        });

        var socialRows = profileForm.querySelectorAll(".account-new-social-row");
        Array.prototype.forEach.call(socialRows, function (socialRow) {
            if (!socialRow) {
                return;
            }

            var socialSelect = socialRow.querySelector('select[name="social_row_ids[]"]');
            var socialLinkInput = socialRow.querySelector('input[name="social_row_links[]"]');
            if (!socialSelect || !socialLinkInput) {
                return;
            }

            var selectedSocialId = parseInt(socialSelect.value || "", 10) || 0;
            if (selectedSocialId <= 0) {
                return;
            }

            var legacySocialLinkMessage = legacySocialLinkErrorsById[selectedSocialId] || "";
            if (legacySocialLinkMessage === "") {
                return;
            }

            appendProfileFieldError(socialLinkInput, legacySocialLinkMessage);
        });
    }

    if (profileForm && profilePictureInput) {
        profileForm.addEventListener("submit", function (event) {
            event.preventDefault();
            clearProfileValidationState();

            if (profileAjaxAlert) {
                profileAjaxAlert.className = "hidden";
                profileAjaxAlert.innerHTML = "";
            }

            var selectedPictureFile = profilePictureInput.files && profilePictureInput.files[0]
                ? profilePictureInput.files[0]
                : null;

            validateProfilePictureHasFace(selectedPictureFile)
                .then(function (faceCheckResult) {
                    if (!faceCheckResult.valid) {
                        setProfilePictureFaceVerified(false);
                        openProfileFaceErrorModal(faceCheckResult.message || "Profile picture must contain a clear human face.");
                        return;
                    }

                    setProfilePictureFaceVerified(true);

                    var formData = new FormData(profileForm);
                    setFormLoadingState(profileForm, true, "Saving...");
                    fetch(profileForm.getAttribute("action"), {
                        method: "POST",
                        headers: {
                            "X-Requested-With": "XMLHttpRequest",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": csrfToken
                        },
                        body: formData
                    })
                        .then(function (response) {
                            return response.json().then(function (data) {
                                return { ok: response.ok, status: response.status, data: data };
                            });
                        })
                        .then(function (result) {
                            if (!result.ok) {
                                if (result.status === 422 && result.data.errors) {
                                    var pictureErrors = result.data.errors.picture;
                                    if (Array.isArray(pictureErrors) && pictureErrors.length > 0) {
                                        openProfileFaceErrorModal(String(pictureErrors[0] || "Profile picture must contain a clear human face."));
                                    }
                                    applyProfileValidationErrors(result.data.errors);
                                    if (profileAjaxAlert) {
                                        profileAjaxAlert.className = "hidden";
                                        profileAjaxAlert.innerHTML = "";
                                    }
                                    return;
                                }

                                if (profileAjaxAlert) {
                                    profileAjaxAlert.className = "alert-error";
                                    profileAjaxAlert.innerHTML = "<div>" + (result.data.message || "Failed to save profile.") + "</div>";
                                }
                                return;
                            }

                            clearProfileValidationState();
                            var familyMember = result.data.family_member || {};
                            var updatedSelf = result.data.updated_self === true || result.data.updated_self === 1 || result.data.updated_self === "1";
                            if (updatedSelf) {
                                updateNavbarWelcomeName(familyMember.name || "");
                            }
                            if (detailJob) {
                                detailJob.textContent = familyMember.job || "-";
                            }
                            if (detailAddress) {
                                detailAddress.textContent = familyMember.address || "-";
                            }
                            if (detailEducation) {
                                detailEducation.textContent = familyMember.education_status || "-";
                            }

                            if (familyMember.picture && detailPhoto) {
                                detailPhoto.src = familyMember.picture;

                                var myCards = document.querySelectorAll('.member-card[data-isme="1"]');
                                Array.prototype.forEach.call(myCards, function (card) {
                                    card.dataset.photo = familyMember.picture;
                                    var photo = card.querySelector(".member-photo");
                                    if (photo) {
                                        photo.src = familyMember.picture;
                                    }
                                });
                            }

                            clearPendingProfilePhotoPreview();
                            clearPendingCroppedPreview();
                            profilePictureInput.value = "";
                            setProfilePictureFaceVerified(false);
                            syncDetailPhotoEditable();
                            if (profileAjaxAlert) {
                                profileAjaxAlert.className = "hidden";
                                profileAjaxAlert.innerHTML = "";
                            }
                            openProfileSaveSuccessModal(result.data.message || "Profile details updated successfully.");
                        })
                        .catch(function () {
                            if (profileAjaxAlert) {
                                profileAjaxAlert.className = "alert-error";
                                profileAjaxAlert.innerHTML = "<div>Failed to save profile.</div>";
                            }
                        })
                        .finally(function () {
                            setFormLoadingState(profileForm, false);
                        });
                });
        });
    }

    refreshTreeDomState();

    if (profileSaveSuccessOkBtn) {
        profileSaveSuccessOkBtn.addEventListener("click", function () {
            closeProfileSaveSuccessModal();
        });
    }

    if (profileSaveSuccessModal) {
        profileSaveSuccessModal.addEventListener("click", function (event) {
            if (event.target === profileSaveSuccessModal || (event.target.classList && event.target.classList.contains("message-modal-backdrop"))) {
                closeProfileSaveSuccessModal();
            }
        });
    }

    if (profileFaceErrorOkBtn) {
        profileFaceErrorOkBtn.addEventListener("click", function () {
            closeProfileFaceErrorModal();
        });
    }

    if (profileFaceErrorModal) {
        profileFaceErrorModal.addEventListener("click", function (event) {
            if (event.target === profileFaceErrorModal || (event.target.classList && event.target.classList.contains("message-modal-backdrop"))) {
                closeProfileFaceErrorModal();
            }
        });
    }

    function syncDetailPhotoEditable() {
        if (!detailPhoto) {
            return;
        }

        var isMe = detailPhoto.dataset.isme === "1";
        detailPhoto.classList.toggle("is-editable", isMe && Boolean(profilePictureInput));
        if (detailPhotoWrap) {
            detailPhotoWrap.classList.toggle("is-editable", isMe && Boolean(profilePictureInput));
        }
        if (detailPhotoHint) {
            if (isMe && pendingCroppedPreviewUrl) {
                detailPhotoHint.textContent = "Photo ready. Click Save Profile to apply.";
            } else {
                detailPhotoHint.textContent = isMe
                    ? "Click photo to choose new profile picture."
                    : "Choose card with badge Me, then click photo to change profile picture.";
            }
        }
    }

    function syncAddMemberAccessBySelectedCard(card) {
        if (!addMemberPanelBtn || !addMemberPanel) {
            return;
        }

        var isSuperadmin = addMemberForm && (addMemberForm.getAttribute("data-is-superadmin") || "0") === "1";
        var isMeCard = card && (card.dataset.isme || "0") === "1";
        var canShowAddMember = isSuperadmin || isMeCard;
        addMemberPanelBtn.classList.toggle("hidden", !canShowAddMember);

        if (!canShowAddMember) {
            addMemberPanel.classList.add("hidden");
            if (memberDetailBlock) {
                memberDetailBlock.classList.remove("hidden");
            }
            if (profilePanel) {
                profilePanel.classList.remove("hidden");
            }
            if (profilePanelBtn) {
                profilePanelBtn.classList.add("is-active");
            }
            addMemberPanelBtn.classList.remove("is-active");
        }
    }

    function syncMemberActionAccessBySelectedCard(card) {
        if (!memberActionBlock || !card) {
            return;
        }

        var memberId = card.dataset.memberid || "";
        var lifeStatusRaw = (card.dataset.lifeStatusRaw || "").toLowerCase();
        var canDeletePartner = (card.dataset.canDeletePartner || "0") === "1";
        var canDeleteChild = (card.dataset.canDeleteChild || "0") === "1";
        var canUpdateLifeStatus = (card.dataset.canUpdateLifeStatus || "0") === "1";
        var canEditProfile = (card.dataset.canEditProfile || "0") === "1";
        var childParentingModeMap = window.familyTreeChildParentingModeMap || {};
        var childParentingMode = String(
            card.dataset.childParentingMode || childParentingModeMap[memberId] || ""
        ).toLowerCase();
        var canEditChildParentingMode = isSuperadminUser && (childParentingMode === "single_parent" || childParentingMode === "with_current_partner");
        var showActionBlock = canDeletePartner || canDeleteChild || canUpdateLifeStatus || canEditProfile || canEditChildParentingMode;

        memberActionBlock.classList.toggle("hidden", !showActionBlock);

        if (deletePartnerForm) {
            deletePartnerForm.classList.toggle("hidden", !canDeletePartner);
        }

        if (deleteChildForm) {
            deleteChildForm.classList.toggle("hidden", !canDeleteChild);
        }

        if (lifeStatusForm) {
            lifeStatusForm.classList.toggle("hidden", !canUpdateLifeStatus);
        }

        if (editProfileLink) {
            editProfileLink.classList.toggle("hidden", !canEditProfile);
            if (canEditProfile && memberId) {
                editProfileLink.setAttribute("href", "/account?memberid=" + encodeURIComponent(memberId));
            } else {
                editProfileLink.setAttribute("href", "/account");
            }
        }

        if (childParentingModeUpdateForm) {
            childParentingModeUpdateForm.classList.toggle("hidden", !canEditChildParentingMode);
        }

        if (childParentingModeMemberIdInput) {
            childParentingModeMemberIdInput.value = memberId;
        }

        if (childParentingModeActionSelect) {
            childParentingModeActionSelect.value = childParentingMode === "with_current_partner"
                ? "with_current_partner"
                : "single_parent";
        }

        if (childParentingModeStatusText) {
            if (childParentingMode === "single_parent") {
                childParentingModeStatusText.textContent = "Single parent";
            } else if (childParentingMode === "with_current_partner") {
                childParentingModeStatusText.textContent = "With current partner";
            } else {
                childParentingModeStatusText.textContent = "-";
            }
        }

        if (childParentingModeActionBtn) {
            childParentingModeActionBtn.disabled = false;
            childParentingModeActionBtn.textContent = "Save Status";
        }

        if (deletePartnerMemberIdInput) {
            deletePartnerMemberIdInput.value = memberId;
        }

        if (deleteChildMemberIdInput) {
            deleteChildMemberIdInput.value = memberId;
        }

        if (lifeStatusMemberIdInput) {
            lifeStatusMemberIdInput.value = memberId;
        }

        if (lifeStatusSelect) {
            if (lifeStatusRaw === "deceased") {
                lifeStatusSelect.value = "deceased";
            } else {
                lifeStatusSelect.value = "alive";
            }
            lifeStatusSelect.disabled = !canUpdateLifeStatus;
        }

        if (saveLifeStatusBtn) {
            saveLifeStatusBtn.disabled = !canUpdateLifeStatus;
        }
    }

    function normalizeSocialMediaLink(rawLink) {
        var value = String(rawLink || "").trim();
        if (!value) {
            return "";
        }

        if (/^https?:\/\//i.test(value)) {
            return value;
        }

        if (/^\/\//.test(value)) {
            return "https:" + value;
        }

        if (/^[a-z0-9.-]+\.[a-z]{2,}(\/|$)/i.test(value)) {
            return "https://" + value;
        }

        return "";
    }

    function parseSocialMediaUrl(urlValue) {
        if (!urlValue) {
            return null;
        }

        try {
            return new URL(urlValue);
        } catch (error) {
            return null;
        }
    }

    function detectSocialPlatformKey(urlObject, socialName) {
        var host = urlObject && urlObject.hostname
            ? String(urlObject.hostname).toLowerCase().replace(/^www\./, "")
            : "";

        if (host.indexOf("instagram.com") !== -1) {
            return "instagram";
        }
        if (host.indexOf("facebook.com") !== -1 || host === "fb.com") {
            return "facebook";
        }
        if (host === "x.com" || host.indexOf("twitter.com") !== -1) {
            return "x";
        }
        if (host.indexOf("tiktok.com") !== -1) {
            return "tiktok";
        }
        if (host.indexOf("linkedin.com") !== -1) {
            return "linkedin";
        }
        if (host.indexOf("youtube.com") !== -1 || host.indexOf("youtu.be") !== -1) {
            return "youtube";
        }
        if (host.indexOf("github.com") !== -1) {
            return "github";
        }
        if (host.indexOf("telegram.me") !== -1 || host.indexOf("t.me") !== -1) {
            return "telegram";
        }
        if (host.indexOf("whatsapp.com") !== -1 || host === "wa.me") {
            return "whatsapp";
        }
        if (host.indexOf("line.me") !== -1) {
            return "line";
        }
        if (host.indexOf("discord.com") !== -1 || host.indexOf("discord.gg") !== -1) {
            return "discord";
        }
        if (host.indexOf("threads.net") !== -1) {
            return "threads";
        }
        if (host.indexOf("reddit.com") !== -1) {
            return "reddit";
        }
        if (host.indexOf("pinterest.com") !== -1) {
            return "pinterest";
        }

        var socialNameText = String(socialName || "").toLowerCase();
        if (socialNameText.indexOf("instagram") !== -1) {
            return "instagram";
        }
        if (socialNameText.indexOf("facebook") !== -1) {
            return "facebook";
        }
        if (socialNameText === "x" || socialNameText.indexOf("twitter") !== -1) {
            return "x";
        }
        if (socialNameText.indexOf("tiktok") !== -1) {
            return "tiktok";
        }
        if (socialNameText.indexOf("linkedin") !== -1) {
            return "linkedin";
        }
        if (socialNameText.indexOf("youtube") !== -1) {
            return "youtube";
        }
        if (socialNameText.indexOf("github") !== -1) {
            return "github";
        }
        if (socialNameText.indexOf("telegram") !== -1) {
            return "telegram";
        }
        if (socialNameText.indexOf("whatsapp") !== -1) {
            return "whatsapp";
        }
        if (socialNameText.indexOf("line") !== -1) {
            return "line";
        }
        if (socialNameText.indexOf("discord") !== -1) {
            return "discord";
        }
        if (socialNameText.indexOf("threads") !== -1) {
            return "threads";
        }
        if (socialNameText.indexOf("reddit") !== -1) {
            return "reddit";
        }
        if (socialNameText.indexOf("pinterest") !== -1) {
            return "pinterest";
        }

        return "";
    }

    function normalizeSocialPlatformKey(iconValue) {
        var normalized = String(iconValue || "")
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, "");

        if (!normalized) {
            return "";
        }

        var keywordMap = {
            instagram: ["instagram", "insta", "ig"],
            facebook: ["facebook", "fb", "meta"],
            x: ["x", "xcom", "twitter"],
            tiktok: ["tiktok"],
            linkedin: ["linkedin"],
            youtube: ["youtube", "youtu", "yt"],
            github: ["github"],
            telegram: ["telegram"],
            whatsapp: ["whatsapp", "wa"],
            line: ["line"],
            discord: ["discord"],
            threads: ["threads"],
            reddit: ["reddit"],
            pinterest: ["pinterest"]
        };

        var platformKeys = Object.keys(keywordMap);
        for (var i = 0; i < platformKeys.length; i += 1) {
            var platformKey = platformKeys[i];
            var keywords = keywordMap[platformKey] || [];
            for (var j = 0; j < keywords.length; j += 1) {
                var keyword = keywords[j];
                if (normalized === keyword || normalized.indexOf(keyword) !== -1) {
                    return platformKey;
                }
            }
        }

        return "";
    }

    function decodePathSegment(pathSegment) {
        var value = String(pathSegment || "").trim();
        if (!value) {
            return "";
        }

        try {
            return decodeURIComponent(value);
        } catch (error) {
            return value;
        }
    }

    function sanitizeUsernameValue(usernameValue) {
        var value = decodePathSegment(usernameValue).trim();
        value = value.replace(/^@+/, "");
        return value;
    }

    function extractSocialUsername(urlObject, platformKey) {
        if (!urlObject) {
            return "";
        }

        var pathSegments = String(urlObject.pathname || "")
            .split("/")
            .map(function (segment) {
                return decodePathSegment(segment).trim();
            })
            .filter(function (segment) {
                return segment !== "";
            });

        if (platformKey === "youtube") {
            if (urlObject.hostname && urlObject.hostname.indexOf("youtu.be") !== -1) {
                return sanitizeUsernameValue(pathSegments[0] || "");
            }

            var firstYouTubePath = pathSegments[0] || "";
            if (firstYouTubePath.indexOf("@") === 0) {
                return sanitizeUsernameValue(firstYouTubePath);
            }
            if (["channel", "user", "c"].indexOf(firstYouTubePath.toLowerCase()) !== -1) {
                return sanitizeUsernameValue(pathSegments[1] || "");
            }
            return sanitizeUsernameValue(firstYouTubePath);
        }

        if (platformKey === "facebook") {
            var firstFacebookPath = (pathSegments[0] || "").toLowerCase();
            if (firstFacebookPath === "profile.php") {
                return sanitizeUsernameValue(urlObject.searchParams.get("id") || "");
            }
            if (["pages", "people", "groups", "watch", "reel", "story.php"].indexOf(firstFacebookPath) !== -1) {
                return sanitizeUsernameValue(pathSegments[1] || "");
            }
            return sanitizeUsernameValue(pathSegments[0] || "");
        }

        if (platformKey === "linkedin") {
            var firstLinkedInPath = (pathSegments[0] || "").toLowerCase();
            if (["in", "company", "school", "showcase", "pub"].indexOf(firstLinkedInPath) !== -1) {
                return sanitizeUsernameValue(pathSegments[1] || "");
            }
            return sanitizeUsernameValue(pathSegments[0] || "");
        }

        if (platformKey === "whatsapp") {
            if (urlObject.hostname && urlObject.hostname.toLowerCase() === "wa.me") {
                return sanitizeUsernameValue(pathSegments[0] || "");
            }
            var phoneFromQuery = sanitizeUsernameValue(urlObject.searchParams.get("phone") || "");
            if (phoneFromQuery) {
                return phoneFromQuery;
            }
            return sanitizeUsernameValue(pathSegments[0] || "");
        }

        return sanitizeUsernameValue(pathSegments[0] || "");
    }

    function getSocialMediaIconUrl(platformKey) {
        var platformConfig = socialMediaPlatformMap[platformKey] || null;
        if (!platformConfig || !platformConfig.slug) {
            return "";
        }

        // Use a neutral gray tone for the detail-card social icons.
        return "https://cdn.simpleicons.org/" + platformConfig.slug + "/808080";
    }

    function createSocialMediaFallbackIcon(labelText) {
        var fallbackIcon = document.createElement("span");
        fallbackIcon.className = "detail-social-media-icon-fallback";
        var normalizedLabel = String(labelText || "").trim();
        fallbackIcon.textContent = normalizedLabel ? normalizedLabel.charAt(0).toUpperCase() : "?";
        return fallbackIcon;
    }

    function parseSocialMediaItemsFromCard(card) {
        if (!card || !card.dataset) {
            return [];
        }

        var rawItems = card.dataset.socialMediaItems || "";
        if (rawItems) {
            try {
                var parsedItems = JSON.parse(rawItems);
                if (Array.isArray(parsedItems)) {
                    return parsedItems.filter(function (item) {
                        return item && typeof item === "object";
                    });
                }
            } catch (error) {
                // Ignore malformed JSON and fallback to plain text below.
            }
        }

        var fallbackText = String(card.dataset.socialMedia || "").trim();
        if (!fallbackText || fallbackText === "-") {
            return [];
        }

        return [{
            name: fallbackText,
            link: ""
        }];
    }

    function renderDetailSocialMedia(card) {
        if (!detailSocialMedia) {
            return;
        }

        var socialItems = parseSocialMediaItemsFromCard(card);
        if (!socialItems.length) {
            detailSocialMedia.textContent = "-";
            return;
        }

        detailSocialMedia.innerHTML = "";

        socialItems.forEach(function (item) {
            var socialName = String(item.name || "").trim();
            var normalizedLink = normalizeSocialMediaLink(item.link || "");
            var urlObject = parseSocialMediaUrl(normalizedLink);
            var platformKey = normalizeSocialPlatformKey(item.icon || "")
                || detectSocialPlatformKey(urlObject, socialName);
            var extractedUsername = extractSocialUsername(urlObject, platformKey);
            var fallbackLabel = socialName || (platformKey ? socialMediaPlatformMap[platformKey].label : "Social");
            var usernameLabel = extractedUsername || fallbackLabel;
            var shouldPrefixAt = extractedUsername && extractedUsername.indexOf(".") === -1;
            var displayUsername = shouldPrefixAt ? "@" + extractedUsername : usernameLabel;
            var socialItemElement = normalizedLink
                ? document.createElement("a")
                : document.createElement("span");

            socialItemElement.className = "detail-social-media-item";
            if (normalizedLink) {
                socialItemElement.href = normalizedLink;
                socialItemElement.target = "_blank";
                socialItemElement.rel = "noopener noreferrer";
            }

            var iconUrl = getSocialMediaIconUrl(platformKey);
            if (iconUrl) {
                var iconImage = document.createElement("img");
                iconImage.className = "detail-social-media-icon";
                iconImage.src = iconUrl;
                iconImage.alt = (socialName || fallbackLabel) + " logo";
                iconImage.loading = "lazy";
                iconImage.addEventListener("error", function () {
                    if (!iconImage.parentNode) {
                        return;
                    }
                    iconImage.parentNode.replaceChild(
                        createSocialMediaFallbackIcon(socialName || fallbackLabel),
                        iconImage
                    );
                });
                socialItemElement.appendChild(iconImage);
            } else {
                socialItemElement.appendChild(createSocialMediaFallbackIcon(socialName || fallbackLabel));
            }

            var usernameElement = document.createElement("span");
            usernameElement.className = "detail-social-media-username";
            usernameElement.textContent = displayUsername || "-";
            socialItemElement.appendChild(usernameElement);

            detailSocialMedia.appendChild(socialItemElement);
        });
    }

    function setActive(card) {
        cards.forEach(function (item) {
            item.classList.remove("active");
        });
        card.classList.add("active");
        detailName.textContent = card.dataset.name || "-";
        detailRole.textContent = card.dataset.role || "-";
        if (detailGender) {
            detailGender.textContent = card.dataset.gender || "-";
        }
        detailAge.textContent = card.dataset.age || "-";
        if (detailBirthdate) {
            detailBirthdate.textContent = card.dataset.birthdate || "-";
        }
        if (detailBirthplace) {
            detailBirthplace.textContent = card.dataset.birthplace || "-";
        }
        if (detailBloodType) {
            detailBloodType.textContent = card.dataset.bloodType || "-";
        }
        detailStatus.textContent = card.dataset.status || "-";
        if (detailMaritalStatus) {
            detailMaritalStatus.textContent = card.dataset.maritalStatus || "-";
        }
        if (detailPhone) {
            detailPhone.textContent = card.dataset.phone || "-";
        }
        if (detailEmail) {
            detailEmail.textContent = card.dataset.email || "-";
        }
        if (detailSocialMedia) {
            renderDetailSocialMedia(card);
        }
        if (detailJob) {
            detailJob.textContent = card.dataset.job || "-";
        }
        if (detailAddress) {
            detailAddress.textContent = card.dataset.address || "-";
        }
        if (detailEducation) {
            detailEducation.textContent = card.dataset.education || "-";
        }
        if (detailGeneration) {
            detailGeneration.textContent = card.dataset.generation || "-";
        }
        var lifeStatusRaw = (card.dataset.lifeStatusRaw || "").toLowerCase();
        if (detailCard) {
            detailCard.classList.toggle("is-deceased", lifeStatusRaw === "deceased");
        }
        var isMeCard = (card.dataset.isme || "0") === "1";
        detailPhoto.src = isMeCard && pendingCroppedPreviewUrl
            ? pendingCroppedPreviewUrl
            : (card.dataset.photo || "");
        detailPhoto.alt = card.dataset.name || "Member Photo";
        detailPhoto.dataset.isme = card.dataset.isme || "0";
        syncDetailPhotoEditable();
        syncAddMemberAccessBySelectedCard(card);
        syncMemberActionAccessBySelectedCard(card);

        if (targetMemberIdInput) {
            targetMemberIdInput.value = card.dataset.memberid || "";
        }
        if (relatedToMemberDisplay) {
            relatedToMemberDisplay.value = card.dataset.name || "-";
        }
        if (addMemberForm) {
            var selectedGender = String(card.dataset.gender || "").toLowerCase();
            var resolvedPartnerGender = selectedGender === "female" ? "male" : "female";
            addMemberForm.setAttribute("data-default-partner-gender", resolvedPartnerGender);
            var selectedCanUseCurrentPartner = String(card.dataset.hasPartner || "0") === "1"
                || String(card.dataset.maritalStatus || "").toLowerCase() === "married";
            addMemberForm.setAttribute("data-can-use-current-partner", selectedCanUseCurrentPartner ? "1" : "0");
            if (memberGenderPartnerDisplay) {
                memberGenderPartnerDisplay.value = resolvedPartnerGender === "male" ? "Male" : "Female";
            }
            if (relationTypeInput && relationTypeInput.value === "partner" && memberGenderInput) {
                memberGenderInput.value = resolvedPartnerGender;
            }
        }

        syncChildParentingModeVisibility();
    }

    var lastTreeCardViewportLock = null;

    function captureTreeCardViewportLock() {
        lastTreeCardViewportLock = {
            treeScrollLeft: treeContainer ? treeContainer.scrollLeft : 0,
            treeScrollTop: treeContainer ? treeContainer.scrollTop : 0,
            pageScrollX: window.scrollX || window.pageXOffset || 0,
            pageScrollY: window.scrollY || window.pageYOffset || 0
        };

        return lastTreeCardViewportLock;
    }

    function restoreTreeCardViewportLock(lock) {
        if (!lock) {
            return;
        }

        if (treeContainer) {
            treeContainer.scrollLeft = lock.treeScrollLeft;
            treeContainer.scrollTop = lock.treeScrollTop;
        }

        var currentPageScrollX = window.scrollX || window.pageXOffset || 0;
        var currentPageScrollY = window.scrollY || window.pageYOffset || 0;
        if (
            Math.abs(currentPageScrollX - lock.pageScrollX) > 1
            || Math.abs(currentPageScrollY - lock.pageScrollY) > 1
        ) {
            window.scrollTo(lock.pageScrollX, lock.pageScrollY);
        }
    }

    function bindTreeCardClicks() {
        if (!hasTreeMemberContext) {
            return;
        }

        cards.forEach(function (card) {
            if (card.dataset.treeCardBound === "1") {
                return;
            }

            card.dataset.treeCardBound = "1";
            if (window.PointerEvent) {
                card.addEventListener("pointerdown", function () {
                    captureTreeCardViewportLock();
                }, { passive: true });
            } else {
                card.addEventListener("mousedown", function () {
                    captureTreeCardViewportLock();
                });
                card.addEventListener("touchstart", function () {
                    captureTreeCardViewportLock();
                }, { passive: true });
            }

            card.addEventListener("click", function () {
                var viewportLock = lastTreeCardViewportLock || captureTreeCardViewportLock();

                setSidePanel("profile");
                setActive(card);

                // Keep tree and page viewport stable after card selection.
                restoreTreeCardViewportLock(viewportLock);
                window.requestAnimationFrame(function () {
                    restoreTreeCardViewportLock(viewportLock);
                    window.requestAnimationFrame(function () {
                        restoreTreeCardViewportLock(viewportLock);
                    });
                });
                window.setTimeout(function () {
                    restoreTreeCardViewportLock(viewportLock);
                }, 80);
                window.setTimeout(function () {
                    restoreTreeCardViewportLock(viewportLock);
                }, 220);
            });
        });
    }

    if (profilePictureInput && detailPhoto) {
        detailPhoto.addEventListener("click", function () {
            if (detailPhoto.dataset.isme !== "1") {
                return;
            }
            profilePictureInput.click();
        });

        profilePictureInput.addEventListener("change", function () {
            var file = profilePictureInput.files && profilePictureInput.files[0];
            if (!file) {
                setProfilePictureFaceVerified(false);
                syncDetailPhotoEditable();
                return;
            }

            if (detailPhoto.dataset.isme !== "1") {
                setProfilePictureFaceVerified(false);
                profilePictureInput.value = "";
                return;
            }

            openPhotoCropModal(file, {
                target: "profile",
                shape: "circle",
                title: "Crop Profile Photo",
                description: "Move image to adjust photo. Use 2-finger pinch on trackpad to zoom while cursor is inside photo."
            });
        });

        syncDetailPhotoEditable();
    }

    if (photoCropCanvas) {
        var getPointer = function (event) {
            if (event.touches && event.touches[0]) {
                return { x: event.touches[0].clientX, y: event.touches[0].clientY };
            }
            return { x: event.clientX, y: event.clientY };
        };

        var getCanvasPointer = function (event) {
            var point = getPointer(event);
            var rect = photoCropCanvas.getBoundingClientRect();
            var scaleX = rect.width ? photoCropCanvas.width / rect.width : 1;
            var scaleY = rect.height ? photoCropCanvas.height / rect.height : 1;

            return {
                x: (point.x - rect.left) * scaleX,
                y: (point.y - rect.top) * scaleY
            };
        };

        var isCanvasPointInsideImage = function (canvasX, canvasY) {
            if (!cropImage) {
                return false;
            }

            var scaled = getScaledCropImageDimensions();
            return (
                canvasX >= cropOffsetX
                && canvasX <= cropOffsetX + scaled.width
                && canvasY >= cropOffsetY
                && canvasY <= cropOffsetY + scaled.height
            );
        };

        var zoomCropAtCanvasPoint = function (canvasX, canvasY, zoomScale) {
            if (!cropImage || !photoCropCanvas) {
                return;
            }

            var previousZoom = cropZoomValue;
            var nextZoom = clampNumber(previousZoom * zoomScale, 1, 3);
            if (Math.abs(nextZoom - previousZoom) < 0.0001) {
                return;
            }

            var oldScale = cropScaleBase * previousZoom;
            var newScale = cropScaleBase * nextZoom;
            var imageX = (canvasX - cropOffsetX) / oldScale;
            var imageY = (canvasY - cropOffsetY) / oldScale;

            cropZoomValue = nextZoom;
            cropOffsetX = canvasX - imageX * newScale;
            cropOffsetY = canvasY - imageY * newScale;
            clampCropOffsets();
            renderCropCanvas();
        };

        var beginDrag = function (event) {
            if (!cropImage) {
                return;
            }

            var point = getPointer(event);
            var canvasPoint = getCanvasPointer(event);
            var handle = getCropHandleAtPoint(canvasPoint.x, canvasPoint.y);

            if (activePhotoCropShape === "square" && handle) {
                cropResizing = true;
                cropResizeHandle = handle;
                cropStartX = point.x;
                cropStartY = point.y;
                cropStartCanvasX = canvasPoint.x;
                cropStartCanvasY = canvasPoint.y;
                cropStartFrameX = cropFrameX;
                cropStartFrameY = cropFrameY;
                cropStartFrameSize = cropFrameSize;
                photoCropCanvas.style.cursor = resolveResizeCursor(handle);
                event.preventDefault();
                return;
            }

            if (activePhotoCropShape === "square" && isPointInsideCropFrame(canvasPoint.x, canvasPoint.y)) {
                cropFrameMoving = true;
                cropStartX = point.x;
                cropStartY = point.y;
                cropStartCanvasX = canvasPoint.x;
                cropStartCanvasY = canvasPoint.y;
                cropStartFrameX = cropFrameX;
                cropStartFrameY = cropFrameY;
                photoCropCanvas.style.cursor = "move";
                event.preventDefault();
                return;
            }

            cropDragging = true;
            cropStartX = point.x;
            cropStartY = point.y;
            cropStartOffsetX = cropOffsetX;
            cropStartOffsetY = cropOffsetY;
            photoCropCanvas.classList.add("is-dragging");
            photoCropCanvas.style.cursor = "grabbing";
        };

        var moveDrag = function (event) {
            if (!cropImage) {
                return;
            }

            var canvasPoint = getCanvasPointer(event);

            if (cropResizing) {
                event.preventDefault();
                resizeSquareCropFrame(canvasPoint.x, canvasPoint.y);
                clampCropOffsets();
                renderCropCanvas();
                photoCropCanvas.style.cursor = resolveResizeCursor(cropResizeHandle);
                return;
            }

            if (cropFrameMoving) {
                event.preventDefault();
                var frameDeltaX = canvasPoint.x - cropStartCanvasX;
                var frameDeltaY = canvasPoint.y - cropStartCanvasY;
                cropFrameX = cropStartFrameX + frameDeltaX;
                cropFrameY = cropStartFrameY + frameDeltaY;
                clampCropFramePosition();
                clampCropOffsets();
                renderCropCanvas();
                photoCropCanvas.style.cursor = "move";
                return;
            }

            if (cropDragging) {
                event.preventDefault();
                var pointer = getPointer(event);
                cropOffsetX = cropStartOffsetX + (pointer.x - cropStartX);
                cropOffsetY = cropStartOffsetY + (pointer.y - cropStartY);
                clampCropOffsets();
                renderCropCanvas();
                return;
            }

            if (activePhotoCropShape === "square") {
                var hoverHandle = getCropHandleAtPoint(canvasPoint.x, canvasPoint.y);
                if (hoverHandle) {
                    photoCropCanvas.style.cursor = resolveResizeCursor(hoverHandle);
                } else if (isPointInsideCropFrame(canvasPoint.x, canvasPoint.y)) {
                    photoCropCanvas.style.cursor = "move";
                } else {
                    photoCropCanvas.style.cursor = "grab";
                }
            } else {
                photoCropCanvas.style.cursor = "grab";
            }
        };

        var endDrag = function () {
            cropDragging = false;
            cropFrameMoving = false;
            cropResizing = false;
            cropResizeHandle = "";
            photoCropCanvas.classList.remove("is-dragging");
            photoCropCanvas.style.cursor = "grab";
            renderCropCanvas();
        };

        photoCropCanvas.addEventListener("mousedown", beginDrag);
        photoCropCanvas.addEventListener("mousemove", moveDrag);
        photoCropCanvas.addEventListener("mouseleave", endDrag);
        window.addEventListener("mouseup", endDrag);
        photoCropCanvas.addEventListener("touchstart", beginDrag, { passive: false });
        photoCropCanvas.addEventListener("touchmove", moveDrag, { passive: false });
        photoCropCanvas.addEventListener("wheel", function (event) {
            if (!cropImage || !photoCropModal || !photoCropModal.classList.contains("is-open")) {
                return;
            }

            // Trackpad pinch on laptop typically dispatches wheel events with ctrlKey = true.
            if (!event.ctrlKey) {
                return;
            }

            var canvasPoint = getCanvasPointer(event);
            if (!isCanvasPointInsideImage(canvasPoint.x, canvasPoint.y)) {
                return;
            }

            var zoomScale = Math.exp((-event.deltaY || 0) * 0.0035);
            zoomCropAtCanvasPoint(canvasPoint.x, canvasPoint.y, zoomScale);
            event.preventDefault();
        }, { passive: false });
        window.addEventListener("touchend", endDrag);
    }

    if (photoCropApplyBtn) {
        photoCropApplyBtn.addEventListener("click", applyCropSelection);
    }

    if (photoCropCancelBtn) {
        photoCropCancelBtn.addEventListener("click", function () {
            handlePhotoCropCancel();
        });
    }

    if (photoCropModal) {
        photoCropModal.addEventListener("click", function (event) {
            if (event.target && event.target.classList.contains("photo-crop-backdrop")) {
                handlePhotoCropCancel();
            }
        });
    }

    if (hasTreeMemberContext) {
        bindTreeSeeMoreButtons();
        bindTreeExpandButtons();
        bindTreeExpandAllButton();
        bindTreeCardClicks();
        bindTreeConnectorImageSync();
        scheduleTreeConnectorDraw();

        var initialActiveCard = document.querySelector(".member-card.active");
        if (initialActiveCard) {
            setActive(initialActiveCard);
        }
    } else {
        syncDetailPhotoEditable();

        if (treeCanvas) {
            bindTreeConnectorImageSync();
            scheduleTreeConnectorDraw();
        }
    }
})();
