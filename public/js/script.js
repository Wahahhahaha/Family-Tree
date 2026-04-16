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
    var saveTreeImageBtn = document.getElementById("saveTreeImageBtn");
    var treeZoomInBtn = document.getElementById("treeZoomInBtn");
    var treeZoomOutBtn = document.getElementById("treeZoomOutBtn");
    var treeZoomValue = document.getElementById("treeZoomValue");
    var detailName = document.getElementById("detailName");
    var detailRole = document.getElementById("detailRole");
    var detailAge = document.getElementById("detailAge");
    var detailBirthdate = document.getElementById("detailBirthdate");
    var detailBirthplace = document.getElementById("detailBirthplace");
    var detailStatus = document.getElementById("detailStatus");
    var detailPhone = document.getElementById("detailPhone");
    var detailEmail = document.getElementById("detailEmail");
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
    var adminProfileForm = document.getElementById("adminProfileForm");
    var accountAdminAjaxAlert = document.getElementById("accountAdminAjaxAlert");
    var profileForm = document.getElementById("profileForm");
    var profileAjaxAlert = document.getElementById("profileAjaxAlert");
    var profilePanelBtn = document.getElementById("profilePanelBtn");
    var addMemberPanelBtn = document.getElementById("addMemberPanelBtn");
    var profilePanel = document.getElementById("profilePanel");
    var addMemberPanel = document.getElementById("addMemberPanel");
    var memberDetailBlock = document.getElementById("memberDetailBlock");
    var profilePictureInput = document.getElementById("profilePictureInput");
    var relationTypeInput = document.getElementById("relationTypeInput");
    var childParentingModeField = document.getElementById("childParentingModeField");
    var relationButtons = Array.prototype.slice.call(document.querySelectorAll(".relation-btn"));
    var treeSeeMoreButtons = Array.prototype.slice.call(document.querySelectorAll(".tree-see-more-btn"));
    var treeExpandButtons = Array.prototype.slice.call(document.querySelectorAll(".tree-expand-toggle"));
    var treeSummaryText = document.getElementById("treeSummaryText");
    var flashMessageModal = document.getElementById("flashMessageModal");
    var flashMessageOkBtn = document.getElementById("flashMessageOkBtn");
    var photoCropModal = document.getElementById("photoCropModal");
    var photoCropCanvas = document.getElementById("photoCropCanvas");
    var photoCropZoom = document.getElementById("photoCropZoom");
    var photoCropApplyBtn = document.getElementById("photoCropApplyBtn");
    var photoCropCancelBtn = document.getElementById("photoCropCancelBtn");

    var cropImage = null;
    var cropImageUrl = "";
    var cropScaleBase = 1;
    var cropZoomValue = 1;
    var cropOffsetX = 0;
    var cropOffsetY = 0;
    var cropDragging = false;
    var cropStartX = 0;
    var cropStartY = 0;
    var cropStartOffsetX = 0;
    var cropStartOffsetY = 0;
    var pendingCroppedPreviewUrl = "";
    var treeToggleRequestInFlight = false;
    var hasTreeMemberContext = false;

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

    function refreshTreeDomState() {
        cards = Array.prototype.slice.call(document.querySelectorAll(".member-card"));
        treeContainer = document.getElementById("treeScrollArea");
        treeZoomStage = document.getElementById("treeZoomStage");
        treeCanvas = document.getElementById("treeCanvas");
        treeSeeMoreButtons = Array.prototype.slice.call(document.querySelectorAll(".tree-see-more-btn"));
        treeExpandButtons = Array.prototype.slice.call(document.querySelectorAll(".tree-expand-toggle"));
        treeSummaryText = document.getElementById("treeSummaryText");
        hasTreeMemberContext = Boolean(
            cards.length && detailName && detailRole && detailAge && detailStatus && detailPhoto
        );
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

    if (flashMessageModal && flashMessageOkBtn) {
        flashMessageOkBtn.addEventListener("click", closeFlashMessageModal);
    }

    function closePhotoCropModal() {
        if (!photoCropModal || photoCropModal.classList.contains("is-closing")) {
            return;
        }

        photoCropModal.classList.add("is-closing");
        window.setTimeout(function () {
            photoCropModal.classList.remove("is-open", "is-closing");
            photoCropModal.classList.add("hidden");
        }, 220);
    }

    function setProfilePictureFile(file) {
        if (!profilePictureInput || !file) {
            return;
        }

        var transfer = new DataTransfer();
        transfer.items.add(file);
        profilePictureInput.files = transfer.files;
    }

    function clampCropOffsets() {
        if (!photoCropCanvas || !cropImage) {
            return;
        }

        var size = photoCropCanvas.width;
        var scaledWidth = cropImage.width * cropScaleBase * cropZoomValue;
        var scaledHeight = cropImage.height * cropScaleBase * cropZoomValue;

        cropOffsetX = Math.min(0, Math.max(size - scaledWidth, cropOffsetX));
        cropOffsetY = Math.min(0, Math.max(size - scaledHeight, cropOffsetY));
    }

    function renderCropCanvas() {
        if (!photoCropCanvas || !cropImage) {
            return;
        }

        var ctx = photoCropCanvas.getContext("2d");
        var size = photoCropCanvas.width;
        var scaledWidth = cropImage.width * cropScaleBase * cropZoomValue;
        var scaledHeight = cropImage.height * cropScaleBase * cropZoomValue;
        var radius = size / 2 - 8;
        var centerX = size / 2;
        var centerY = size / 2;

        ctx.clearRect(0, 0, size, size);
        ctx.drawImage(cropImage, cropOffsetX, cropOffsetY, scaledWidth, scaledHeight);

        // Keep crop area bright and darken only the outside area.
        ctx.save();
        ctx.fillStyle = "rgba(0, 0, 0, 0.3)";
        ctx.beginPath();
        ctx.rect(0, 0, size, size);
        ctx.arc(centerX, centerY, radius, 0, Math.PI * 2, true);
        ctx.fill("evenodd");
        ctx.restore();

        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
        ctx.strokeStyle = "#ffffff";
        ctx.lineWidth = 2;
        ctx.stroke();
    }

    function openPhotoCropModal(file) {
        if (!photoCropModal || !photoCropCanvas || !photoCropZoom || !file) {
            return;
        }

        clearPendingProfilePhotoPreview();
        cropImage = new Image();
        cropImageUrl = URL.createObjectURL(file);
        cropImage.onload = function () {
            var size = photoCropCanvas.width;
            cropScaleBase = Math.max(size / cropImage.width, size / cropImage.height);
            cropZoomValue = 1;
            photoCropZoom.value = "1";
            cropOffsetX = (size - cropImage.width * cropScaleBase) / 2;
            cropOffsetY = (size - cropImage.height * cropScaleBase) / 2;
            clampCropOffsets();
            renderCropCanvas();
            photoCropModal.classList.remove("hidden", "is-closing");
            photoCropModal.classList.add("is-open");
        };
        cropImage.src = cropImageUrl;
    }

    function applyCropSelection() {
        if (!photoCropCanvas || !cropImage) {
            return;
        }

        var outputSize = 512;
        var exportCanvas = document.createElement("canvas");
        exportCanvas.width = outputSize;
        exportCanvas.height = outputSize;

        var exportCtx = exportCanvas.getContext("2d");
        var drawScale = outputSize / photoCropCanvas.width;
        var scaledWidth = cropImage.width * cropScaleBase * cropZoomValue * drawScale;
        var scaledHeight = cropImage.height * cropScaleBase * cropZoomValue * drawScale;
        var drawX = cropOffsetX * drawScale;
        var drawY = cropOffsetY * drawScale;

        exportCtx.drawImage(cropImage, drawX, drawY, scaledWidth, scaledHeight);
        exportCanvas.toBlob(function (blob) {
            if (!blob) {
                return;
            }

            var croppedFile = new File([blob], "profile-cropped.jpg", { type: "image/jpeg" });
            setProfilePictureFile(croppedFile);
            clearPendingProfilePhotoPreview();
            clearPendingCroppedPreview();
            pendingCroppedPreviewUrl = URL.createObjectURL(croppedFile);

            if (detailPhoto && detailPhoto.dataset.isme === "1") {
                detailPhoto.src = pendingCroppedPreviewUrl;
            }

            closePhotoCropModal();

            if (detailPhotoHint) {
                detailPhotoHint.textContent = "Photo ready. Click Save Profile to apply.";
            }
        }, "image/jpeg", 0.92);
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

        childParentingModeField.classList.toggle("hidden", relationTypeInput.value !== "child");
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

            button.classList.toggle("hidden", !shouldShowButton);
            button.textContent = isExpanded ? "Show less" : "View more";
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
                if (!url || treeToggleRequestInFlight) {
                    return;
                }

                var selectedCard = document.querySelector(".member-card.active");
                var selectedMemberId = selectedCard ? selectedCard.getAttribute("data-memberid") : "";

                treeToggleRequestInFlight = true;
                treeExpandButtons.forEach(function (item) {
                    item.disabled = true;
                });

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

                        syncTreeExpandButtons({
                            hasHiddenUpperTreeLevels: Boolean(data.has_hidden_upper_tree_levels),
                            hasHiddenLowerTreeLevels: Boolean(data.has_hidden_lower_tree_levels),
                            showUpperTree: Boolean(data.show_upper_tree),
                            showLowerTree: Boolean(data.show_lower_tree),
                            toggleUpperTreeUrl: data.toggle_upper_tree_url || "",
                            toggleLowerTreeUrl: data.toggle_lower_tree_url || ""
                        });
                        bindTreeSeeMoreButtons();
                        bindTreeCardClicks();

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

                        var browserUrl = new URL(url, window.location.origin);
                        browserUrl.searchParams.delete("ajax");
                        browserUrl.searchParams.delete("tree_section");
                        window.history.pushState({}, "", browserUrl.toString());
                    })
                    .catch(function () {})
                    .finally(function () {
                        treeToggleRequestInFlight = false;
                        treeExpandButtons.forEach(function (item) {
                            item.disabled = false;
                        });
                    });
            });
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

    function getAjaxUrl(url) {
        var target = new URL(url, window.location.origin);
        target.searchParams.set("ajax", "1");
        return target.toString();
    }

    function fetchUsersPage(url) {
        if (!userTableBody || !userPagination || !url) {
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
                    throw new Error("Failed to load users page.");
                }
                return response.json();
            })
            .then(function (data) {
                userTableBody.innerHTML = data.rows_html || "";
                userPagination.innerHTML = data.pagination_html || "";
                if (userTableCount && typeof data.total !== "undefined") {
                    userTableCount.textContent = "Total: " + data.total + " users";
                }
                var browserUrl = new URL(url, window.location.origin);
                browserUrl.searchParams.delete("ajax");
                window.history.pushState({}, "", browserUrl.toString());
            })
            .catch(function () {});
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
            })
            .catch(function () {});
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

                    accountAdminAjaxAlert.className = "alert-success";
                    accountAdminAjaxAlert.innerHTML = "<div>" + (result.data.message || "Profile updated successfully.") + "</div>";

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
                });
        });
    }

    if (systemLogoInput && systemLogoPreview) {
        systemLogoInput.addEventListener("change", function (event) {
            var file = event.target.files && event.target.files[0];
            if (!file) {
                return;
            }

            var objectUrl = URL.createObjectURL(file);
            systemLogoPreview.src = objectUrl;
            systemLogoPreview.classList.remove("hidden");
            if (systemLogoPlaceholder) {
                systemLogoPlaceholder.classList.add("hidden");
            }
        });
    }

    if (systemSettingsForm) {
        systemSettingsForm.addEventListener("submit", function (event) {
            event.preventDefault();

            if (settingsAjaxAlert) {
                settingsAjaxAlert.className = "alert-success hidden";
                settingsAjaxAlert.innerHTML = "";
            }

            var formData = new FormData(systemSettingsForm);

            fetch(systemSettingsForm.getAttribute("action"), {
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
                    if (!settingsAjaxAlert) {
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
                            html = "<div>" + (result.data.message || "Failed to save setting.") + "</div>";
                        }

                        settingsAjaxAlert.className = "alert-error";
                        settingsAjaxAlert.innerHTML = html;
                        return;
                    }

                    settingsAjaxAlert.className = "alert-success";
                    settingsAjaxAlert.innerHTML = "<div>" + (result.data.message || "Settings saved.") + "</div>";

                    if (result.data.settings) {
                        var websiteName = result.data.settings.website_name || "Family Tree System";
                        var brandTitle = document.querySelector(".brand h1");
                        if (brandTitle) {
                            brandTitle.textContent = websiteName;
                        }
                        document.title = "System Settings";

                        if (result.data.settings.logo_path && systemLogoPreview) {
                            systemLogoPreview.src = result.data.settings.logo_path;
                            systemLogoPreview.classList.remove("hidden");
                            if (systemLogoPlaceholder) {
                                systemLogoPlaceholder.classList.add("hidden");
                            }

                            var brandMark = document.querySelector(".brand-mark");
                            var navbarLogo = document.querySelector(".brand-mark .brand-logo");
                            if (navbarLogo) {
                                navbarLogo.src = result.data.settings.logo_path;
                            } else if (brandMark) {
                                brandMark.classList.add("has-logo");
                                brandMark.innerHTML = '<a href="/"><img class="brand-logo" src="' + result.data.settings.logo_path + '" alt="Logo"></a>';
                            }
                        }
                    }
                })
                .catch(function () {
                    if (settingsAjaxAlert) {
                        settingsAjaxAlert.className = "alert-error";
                        settingsAjaxAlert.innerHTML = "<div>Failed to save setting.</div>";
                    }
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
            var isLevelTwoFamilyMember = selectedLevelValue === "2";
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
                    option.hidden = isLevelTwoFamilyMember;
                    option.disabled = isLevelTwoFamilyMember;
                    return;
                }

                var optionGroup = option.getAttribute("data-role-group");
                if (!optionGroup) {
                    var optionValue = option.value;
                    optionGroup = (optionValue === "3" || optionValue === "4") ? "family" : "employer";
                }
                var isMatch = false;
                if (isLevelTwoFamilyMember) {
                    isMatch = option.value === "4";
                } else {
                    isMatch = hasLevel && optionGroup === targetRoleGroup;
                }
                option.hidden = !isMatch;
                option.disabled = !isMatch;
            });

            if (isLevelTwoFamilyMember) {
                newRoleSelect.disabled = false;
                newRoleSelect.value = "4";
            } else if (newRoleSelect.value) {
                var current = newRoleSelect.options[newRoleSelect.selectedIndex];
                if (current && current.disabled) {
                    newRoleSelect.value = "";
                }
            }

            if (newRoleField) {
                newRoleField.classList.toggle("hidden", isLevelTwoFamilyMember);
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
            newRoleSelect.required = hasLevel && !isLevelTwoFamilyMember;

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
        var currentTreeZoom = 1;
        var minTreeZoom = 0.6;
        var maxTreeZoom = 1.8;
        var treeZoomStep = 0.1;
        var isTreeDragging = false;
        var activeTreePointerId = null;
        var treeDragStartX = 0;
        var treeDragStartY = 0;
        var treeStartScrollLeft = 0;
        var treeStartScrollTop = 0;
        var treeMovedDistance = 0;
        var suppressTreeClick = false;
        var treeNoDragSelector = ".member-card, .tree-see-more-btn, button, input, select, textarea, a, label";

        treeContainer.classList.add("is-pannable");
        treeContainer.style.touchAction = "none";

        function refreshTreeZoomSize() {
            if (!treeCanvas || !treeZoomStage) {
                return;
            }

            treeCanvas.style.transformOrigin = "top left";
            treeCanvas.style.transform = "scale(" + currentTreeZoom + ")";
            treeZoomStage.style.width = Math.ceil(treeCanvas.offsetWidth * currentTreeZoom) + "px";
            treeZoomStage.style.height = Math.ceil(treeCanvas.offsetHeight * currentTreeZoom) + "px";

            if (treeZoomValue) {
                treeZoomValue.textContent = Math.round(currentTreeZoom * 100) + "%";
            }

            if (treeZoomInBtn) {
                treeZoomInBtn.disabled = currentTreeZoom >= maxTreeZoom;
            }

            if (treeZoomOutBtn) {
                treeZoomOutBtn.disabled = currentTreeZoom <= minTreeZoom;
            }
        }

        function setTreeZoom(nextZoom) {
            if (!treeCanvas || !treeZoomStage) {
                return;
            }

            var boundedZoom = Math.max(minTreeZoom, Math.min(maxTreeZoom, nextZoom));
            var safeZoom = Math.round(boundedZoom * 100) / 100;
            if (safeZoom === currentTreeZoom) {
                return;
            }

            currentTreeZoom = safeZoom;
            refreshTreeZoomSize();
        }

        refreshTreeZoomSize();

        if (treeZoomInBtn) {
            treeZoomInBtn.addEventListener("click", function () {
                setTreeZoom(currentTreeZoom + treeZoomStep);
            });
        }

        if (treeZoomOutBtn) {
            treeZoomOutBtn.addEventListener("click", function () {
                setTreeZoom(currentTreeZoom - treeZoomStep);
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

        window.addEventListener("resize", refreshTreeZoomSize);

        treeContainer.addEventListener("pointerdown", function (event) {
            if (event.pointerType === "mouse" && event.button !== 0) {
                return;
            }

            if (event.target && event.target.closest(treeNoDragSelector)) {
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
            if (!isTreeDragging || event.pointerId !== activeTreePointerId) {
                return;
            }

            var deltaX = event.clientX - treeDragStartX;
            var deltaY = event.clientY - treeDragStartY;
            treeMovedDistance = Math.max(treeMovedDistance, Math.abs(deltaX), Math.abs(deltaY));

            treeContainer.scrollLeft = treeStartScrollLeft - deltaX;
            treeContainer.scrollTop = treeStartScrollTop - deltaY;
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
            endTreeDrag(event.pointerId);
        });

        treeContainer.addEventListener("pointercancel", function (event) {
            endTreeDrag(event.pointerId);
        });

        // Horizontal movement must use drag, not wheel/trackpad horizontal scroll.
        treeContainer.addEventListener("wheel", function (event) {
            if (!treeCanvas || !treeZoomStage) {
                return;
            }

            var isHorizontalScroll = Math.abs(event.deltaX) > 0 || event.shiftKey;
            var isVerticalScroll = Math.abs(event.deltaY) > 0;

            if (isVerticalScroll) {
                var zoomDelta = event.deltaY > 0 ? -treeZoomStep : treeZoomStep;
                var nextZoom = Math.max(minTreeZoom, Math.min(maxTreeZoom, currentTreeZoom + zoomDelta));

                if (nextZoom !== currentTreeZoom) {
                    var rect = treeZoomStage.getBoundingClientRect();
                    var pointerX = event.clientX - rect.left + treeContainer.scrollLeft;
                    var pointerY = event.clientY - rect.top + treeContainer.scrollTop;
                    var oldZoom = currentTreeZoom;

                    setTreeZoom(nextZoom);

                    var zoomRatio = currentTreeZoom / oldZoom;
                    treeContainer.scrollLeft = Math.round(pointerX * zoomRatio - (event.clientX - rect.left));
                    treeContainer.scrollTop = Math.round(pointerY * zoomRatio - (event.clientY - rect.top));
                }
            }

            if (isHorizontalScroll || isVerticalScroll) {
                event.preventDefault();
            }
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

    if (profileForm && profilePictureInput) {
        profileForm.addEventListener("submit", function (event) {
            event.preventDefault();

            if (profileAjaxAlert) {
                profileAjaxAlert.className = "hidden";
                profileAjaxAlert.innerHTML = "";
            }

            var formData = new FormData(profileForm);
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
                    if (!profileAjaxAlert) {
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

                        profileAjaxAlert.className = "alert-error";
                        profileAjaxAlert.innerHTML = html;
                        return;
                    }

                    var familyMember = result.data.family_member || {};
                    updateNavbarWelcomeName(familyMember.name || "");
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
                    syncDetailPhotoEditable();
                    profileAjaxAlert.className = "alert-success";
                    profileAjaxAlert.innerHTML = "<div>" + (result.data.message || "Profile details updated successfully.") + "</div>";
                })
                .catch(function () {
                    if (profileAjaxAlert) {
                        profileAjaxAlert.className = "alert-error";
                        profileAjaxAlert.innerHTML = "<div>Failed to save profile.</div>";
                    }
                });
        });
    }

    refreshTreeDomState();

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

        var isMeCard = card && (card.dataset.isme || "0") === "1";
        addMemberPanelBtn.classList.toggle("hidden", !isMeCard);

        if (!isMeCard) {
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
        var showActionBlock = canDeletePartner || canDeleteChild || canUpdateLifeStatus;

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

    function setActive(card) {
        cards.forEach(function (item) {
            item.classList.remove("active");
        });
        card.classList.add("active");
        detailName.textContent = card.dataset.name || "-";
        detailRole.textContent = card.dataset.role || "-";
        detailAge.textContent = card.dataset.age || "-";
        if (detailBirthdate) {
            detailBirthdate.textContent = card.dataset.birthdate || "-";
        }
        if (detailBirthplace) {
            detailBirthplace.textContent = card.dataset.birthplace || "-";
        }
        detailStatus.textContent = card.dataset.status || "-";
        if (detailPhone) {
            detailPhone.textContent = card.dataset.phone || "-";
        }
        if (detailEmail) {
            detailEmail.textContent = card.dataset.email || "-";
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
            card.addEventListener("click", function () {
                setSidePanel("profile");
                setActive(card);
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
                syncDetailPhotoEditable();
                return;
            }

            if (detailPhoto.dataset.isme !== "1") {
                profilePictureInput.value = "";
                return;
            }

            openPhotoCropModal(file);
        });

        syncDetailPhotoEditable();
    }

    if (photoCropZoom) {
        photoCropZoom.addEventListener("input", function () {
            if (!cropImage || !photoCropCanvas) {
                return;
            }

            var previousZoom = cropZoomValue;
            cropZoomValue = parseFloat(photoCropZoom.value || "1");

            var centerX = photoCropCanvas.width / 2;
            var centerY = photoCropCanvas.height / 2;
            var oldScale = cropScaleBase * previousZoom;
            var newScale = cropScaleBase * cropZoomValue;
            var imageX = (centerX - cropOffsetX) / oldScale;
            var imageY = (centerY - cropOffsetY) / oldScale;

            cropOffsetX = centerX - imageX * newScale;
            cropOffsetY = centerY - imageY * newScale;
            clampCropOffsets();
            renderCropCanvas();
        });
    }

    if (photoCropCanvas) {
        var getPointer = function (event) {
            if (event.touches && event.touches[0]) {
                return { x: event.touches[0].clientX, y: event.touches[0].clientY };
            }
            return { x: event.clientX, y: event.clientY };
        };

        var beginDrag = function (event) {
            if (!cropImage) {
                return;
            }

            var point = getPointer(event);
            cropDragging = true;
            cropStartX = point.x;
            cropStartY = point.y;
            cropStartOffsetX = cropOffsetX;
            cropStartOffsetY = cropOffsetY;
            photoCropCanvas.classList.add("is-dragging");
        };

        var moveDrag = function (event) {
            if (!cropDragging || !cropImage) {
                return;
            }

            event.preventDefault();
            var point = getPointer(event);
            cropOffsetX = cropStartOffsetX + (point.x - cropStartX);
            cropOffsetY = cropStartOffsetY + (point.y - cropStartY);
            clampCropOffsets();
            renderCropCanvas();
        };

        var endDrag = function () {
            cropDragging = false;
            photoCropCanvas.classList.remove("is-dragging");
        };

        photoCropCanvas.addEventListener("mousedown", beginDrag);
        photoCropCanvas.addEventListener("mousemove", moveDrag);
        window.addEventListener("mouseup", endDrag);
        photoCropCanvas.addEventListener("touchstart", beginDrag, { passive: true });
        photoCropCanvas.addEventListener("touchmove", moveDrag, { passive: false });
        window.addEventListener("touchend", endDrag);
    }

    if (photoCropApplyBtn) {
        photoCropApplyBtn.addEventListener("click", applyCropSelection);
    }

    if (photoCropCancelBtn) {
        photoCropCancelBtn.addEventListener("click", function () {
            closePhotoCropModal();
            profilePictureInput.value = "";
            clearPendingProfilePhotoPreview();
            syncDetailPhotoEditable();
        });
    }

    if (photoCropModal) {
        photoCropModal.addEventListener("click", function (event) {
            if (event.target && event.target.classList.contains("photo-crop-backdrop")) {
                closePhotoCropModal();
                profilePictureInput.value = "";
                clearPendingProfilePhotoPreview();
                syncDetailPhotoEditable();
            }
        });
    }

    if (hasTreeMemberContext) {
        bindTreeSeeMoreButtons();
        bindTreeExpandButtons();
        bindTreeCardClicks();

        var initialActiveCard = document.querySelector(".member-card.active");
        if (initialActiveCard) {
            syncAddMemberAccessBySelectedCard(initialActiveCard);
            syncMemberActionAccessBySelectedCard(initialActiveCard);
        }
    } else {
        syncDetailPhotoEditable();
    }
})();
