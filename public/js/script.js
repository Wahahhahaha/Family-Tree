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
    var newLevelSelect = document.getElementById("newLevel");
    var dynamicFields = document.getElementById("dynamicFields");
    var newRoleSelect = document.getElementById("newRole");
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
    var systemLogoInput = document.getElementById("systemLogoInput");
    var systemLogoPreview = document.getElementById("systemLogoPreview");
    var systemLogoPlaceholder = document.getElementById("systemLogoPlaceholder");
    var systemSettingsForm = document.getElementById("systemSettingsForm");
    var settingsAjaxAlert = document.getElementById("settingsAjaxAlert");
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrfMeta ? csrfMeta.getAttribute("content") : "";
    var cards = Array.prototype.slice.call(document.querySelectorAll(".member-card"));
    var search = document.getElementById("searchMember");
    var detailName = document.getElementById("detailName");
    var detailRole = document.getElementById("detailRole");
    var detailAge = document.getElementById("detailAge");
    var detailStatus = document.getElementById("detailStatus");
    var detailJob = document.getElementById("detailJob");
    var detailAddress = document.getElementById("detailAddress");
    var detailEducation = document.getElementById("detailEducation");
    var detailGeneration = document.getElementById("detailGeneration");
    var detailPhoto = document.getElementById("detailPhoto");
    var profilePanelBtn = document.getElementById("profilePanelBtn");
    var addMemberPanelBtn = document.getElementById("addMemberPanelBtn");
    var profilePanel = document.getElementById("profilePanel");
    var addMemberPanel = document.getElementById("addMemberPanel");
    var memberDetailBlock = document.getElementById("memberDetailBlock");
    var relationTypeInput = document.getElementById("relationTypeInput");
    var targetMemberIdInput = document.getElementById("targetMemberIdInput");
    var targetMemberNameInput = document.getElementById("targetMemberNameInput");
    var relationButtons = Array.prototype.slice.call(document.querySelectorAll(".relation-btn"));

    function setSidePanel(panelName) {
        if (profilePanel && addMemberPanel) {
            if (panelName === "add-member") {
                profilePanel.classList.add("hidden");
                addMemberPanel.classList.remove("hidden");
            } else {
                profilePanel.classList.remove("hidden");
                addMemberPanel.classList.add("hidden");
            }
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

    if (relationButtons.length && relationTypeInput) {
        relationButtons.forEach(function (button) {
            button.addEventListener("click", function () {
                var relationType = button.getAttribute("data-relation-type") || "child";
                relationTypeInput.value = relationType;

                relationButtons.forEach(function (item) {
                    item.classList.remove("is-active");
                });
                button.classList.add("is-active");
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

    function getAjaxUrl(url) {
        var target = new URL(url, window.location.origin);
        target.searchParams.set("ajax", "1");
        return target.toString();
    }

    function bindPaginationLinks() {
        if (!userPagination) {
            return;
        }

        var links = userPagination.querySelectorAll("a.page-link");
        Array.prototype.forEach.call(links, function (link) {
            link.addEventListener("click", function (event) {
                event.preventDefault();
                fetchUsersPage(link.getAttribute("href"));
            });
        });
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
            .then(function (response) { return response.json(); })
            .then(function (data) {
                userTableBody.innerHTML = data.rows_html || "";
                userPagination.innerHTML = data.pagination_html || "";
                if (userTableCount && typeof data.total !== "undefined") {
                    userTableCount.textContent = "Total: " + data.total + " users";
                }
                bindPaginationLinks();
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

    bindPaginationLinks();

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
            var levelGroup = selectedLevelOption ? selectedLevelOption.getAttribute("data-level-group") : "";
            var hasLevel = Boolean(levelGroup);
            var isEmployer = levelGroup === "employer";
            var isFamily = levelGroup === "family";

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
                var isMatch = hasLevel && optionGroup === levelGroup;
                option.hidden = !isMatch;
                option.disabled = !isMatch;
            });

            if (newRoleSelect.value) {
                var current = newRoleSelect.options[newRoleSelect.selectedIndex];
                if (current && current.disabled) {
                    newRoleSelect.value = "";
                }
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

            updateFamilyAgeDisplay();
        }

        if (familyBirthdateInput) {
            familyBirthdateInput.addEventListener("change", updateFamilyAgeDisplay);
            familyBirthdateInput.addEventListener("input", updateFamilyAgeDisplay);
        }

        newLevelSelect.addEventListener("change", applyRoleFilter);
        applyRoleFilter();
    }

    if (!cards.length || !search || !detailName || !detailRole || !detailAge || !detailStatus || !detailPhoto) {
        return;
    }

    function setActive(card) {
        cards.forEach(function (item) {
            item.classList.remove("active");
        });
        card.classList.add("active");
        detailName.textContent = card.dataset.name || "-";
        detailRole.textContent = card.dataset.role || "-";
        detailAge.textContent = card.dataset.age || "-";
        detailStatus.textContent = card.dataset.status || "-";
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
        detailPhoto.src = card.dataset.photo || "";
        detailPhoto.alt = card.dataset.name || "Member Photo";

        if (targetMemberIdInput) {
            targetMemberIdInput.value = card.dataset.memberid || "";
        }

        if (targetMemberNameInput) {
            targetMemberNameInput.value = card.dataset.name || "-";
        }
    }

    cards.forEach(function (card) {
        card.addEventListener("click", function () {
            setActive(card);
        });
    });

    search.addEventListener("input", function (event) {
        var keyword = event.target.value.trim().toLowerCase();
        cards.forEach(function (card) {
            var name = (card.dataset.name || "").toLowerCase();
            var role = (card.dataset.role || "").toLowerCase();
            var visible = name.indexOf(keyword) > -1 || role.indexOf(keyword) > -1;
            card.style.display = visible ? "" : "none";
        });
    });
})();
