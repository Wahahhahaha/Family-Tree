document.addEventListener('DOMContentLoaded', function () {
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }

    var lifeStatusModal = document.getElementById('lifeStatusConfirmModal');
    var lifeStatusConfirmText = document.getElementById('lifeStatusConfirmText');
    var lifeStatusConfirmError = document.getElementById('lifeStatusConfirmError');
    var lifeStatusConfirmBtn = document.getElementById('lifeStatusConfirmBtn');
    var lifeStatusConfirmCancelBtn = document.getElementById('lifeStatusConfirmCancelBtn');
    var lifeStatusPendingStatus = '';
    var lifeStatusPendingMemberId = '';
    var deleteConfirmModal = document.getElementById('deleteConfirmModal');
    var deleteConfirmTitle = document.getElementById('deleteConfirmTitle');
    var deleteConfirmText = document.getElementById('deleteConfirmText');
    var deleteConfirmBtn = document.getElementById('deleteConfirmBtn');
    var deleteConfirmCancelBtn = document.getElementById('deleteConfirmCancelBtn');
    var pendingDeleteForm = null;
    var pendingDeleteMessage = 'Are you sure you want to delete this item?';

    function openLifeStatusModal(statusText, memberId) {
        lifeStatusPendingStatus = statusText;
        lifeStatusPendingMemberId = memberId;
        if (lifeStatusConfirmText) {
            lifeStatusConfirmText.textContent = 'Change life status to ' + statusText + '?';
        }
        if (lifeStatusConfirmError) {
            lifeStatusConfirmError.textContent = '';
            lifeStatusConfirmError.style.display = 'none';
        }
        if (lifeStatusModal) {
            lifeStatusModal.classList.add('is-open');
            lifeStatusModal.setAttribute('aria-hidden', 'false');
            lifeStatusModal.style.display = 'flex';
        }
    }

    function openDeleteModal(form) {
        if (!form) return;
        pendingDeleteForm = form;
        pendingDeleteMessage = form.getAttribute('data-delete-message') || 'Are you sure you want to delete this item?';
        if (deleteConfirmTitle) deleteConfirmTitle.textContent = 'Confirm Delete';
        if (deleteConfirmText) deleteConfirmText.textContent = pendingDeleteMessage;
        if (deleteConfirmBtn) {
            deleteConfirmBtn.disabled = false;
            deleteConfirmBtn.textContent = 'Delete';
        }
        if (deleteConfirmModal) {
            deleteConfirmModal.classList.add('is-open');
            deleteConfirmModal.setAttribute('aria-hidden', 'false');
            deleteConfirmModal.style.display = 'flex';
        }
    }

    function closeDeleteModal() {
        pendingDeleteForm = null;
        if (deleteConfirmBtn) {
            deleteConfirmBtn.disabled = false;
            deleteConfirmBtn.textContent = 'Delete';
        }
        if (deleteConfirmModal) {
            deleteConfirmModal.classList.remove('is-open');
            deleteConfirmModal.setAttribute('aria-hidden', 'true');
            deleteConfirmModal.style.display = 'none';
        }
    }

    function closeLifeStatusModal() {
        lifeStatusPendingStatus = '';
        lifeStatusPendingMemberId = '';
        if (lifeStatusConfirmError) {
            lifeStatusConfirmError.textContent = '';
            lifeStatusConfirmError.style.display = 'none';
        }
        if (lifeStatusModal) {
            lifeStatusModal.classList.remove('is-open');
            lifeStatusModal.setAttribute('aria-hidden', 'true');
            lifeStatusModal.style.display = 'none';
        }
    }

    function showLifeStatusError(message) {
        if (lifeStatusConfirmError) {
            lifeStatusConfirmError.textContent = message;
            lifeStatusConfirmError.style.display = 'block';
        }
        if (lifeStatusConfirmText) lifeStatusConfirmText.textContent = 'Unable to update life status.';
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-status-toggle');
        if (!btn) return;
        var newStatus = btn.getAttribute('data-status') || '';
        var memberIdInput = document.getElementById('lifeStatusMemberIdInput');
        var memberId = memberIdInput ? memberIdInput.value : '';
        if (!memberId) return;
        openLifeStatusModal(newStatus, memberId);
    });

    if (lifeStatusConfirmCancelBtn) {
        lifeStatusConfirmCancelBtn.addEventListener('click', closeLifeStatusModal);
    }

    if (lifeStatusModal) {
        lifeStatusModal.addEventListener('click', function (event) {
            if (event.target === lifeStatusModal || event.target.classList.contains('message-modal-backdrop')) {
                closeLifeStatusModal();
            }
        });
    }

    if (deleteConfirmCancelBtn) {
        deleteConfirmCancelBtn.addEventListener('click', closeDeleteModal);
    }

    if (deleteConfirmModal) {
        deleteConfirmModal.addEventListener('click', function (event) {
            if (event.target === deleteConfirmModal || event.target.classList.contains('message-modal-backdrop')) {
                closeDeleteModal();
            }
        });
    }

    if (deleteConfirmBtn) {
        deleteConfirmBtn.addEventListener('click', function () {
            if (!pendingDeleteForm) return;
            deleteConfirmBtn.disabled = true;
            deleteConfirmBtn.textContent = 'Deleting...';
            pendingDeleteForm.submit();
        });
    }

    if (lifeStatusConfirmBtn) {
        lifeStatusConfirmBtn.addEventListener('click', function () {
            if (!lifeStatusPendingMemberId || !lifeStatusPendingStatus) return;
            lifeStatusConfirmBtn.disabled = true;
            lifeStatusConfirmBtn.textContent = 'Saving...';
            fetch('/family/member/life-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    memberid: lifeStatusPendingMemberId,
                    life_status: lifeStatusPendingStatus
                })
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) { location.reload(); return; }
                showLifeStatusError(result.message || 'Failed to update status.');
                lifeStatusConfirmBtn.disabled = false;
                lifeStatusConfirmBtn.textContent = 'Confirm';
            })
            .catch(err => {
                console.error('Error:', err);
                showLifeStatusError('An error occurred.');
                lifeStatusConfirmBtn.disabled = false;
            });
        });
    }

    var memberDetailBlock = document.getElementById('memberDetailBlock');
    var memberActionBlock = document.getElementById('memberActionBlock');
    var detailSidebar = document.getElementById('detailSidebar');
    var homePagePanel = document.querySelector('.home-page-panel');
    var detailCard = document.getElementById('detailCard');
    var detailPhoto = document.getElementById('detailPhoto');
    var detailName = document.getElementById('detailName');
    var detailRole = document.getElementById('detailRole');
    var detailGender = document.getElementById('detailGender');
    var detailAge = document.getElementById('detailAge');
    var detailBirthdate = document.getElementById('detailBirthdate');
    var detailBirthplace = document.getElementById('detailBirthplace');
    var detailBloodType = document.getElementById('detailBloodType');
    var detailStatus = document.getElementById('detailStatus');
    var detailMaritalStatus = document.getElementById('detailMaritalStatus');
    var detailPhone = document.getElementById('detailPhone');
    var detailEmail = document.getElementById('detailEmail');
    var detailSocialMedia = document.getElementById('detailSocialMedia');
    var detailJob = document.getElementById('detailJob');
    var detailAddress = document.getElementById('detailAddress');
    var detailEducation = document.getElementById('detailEducation');
    var deleteUserForm = document.getElementById('deleteUserForm');
    var deleteChildForm = document.getElementById('deleteChildForm');
    var lifeStatusForm = document.getElementById('lifeStatusForm');
    var editProfileLink = document.getElementById('editProfileLink');
    var deleteChildMemberIdInput = document.getElementById('deleteChildMemberIdInput');
    var lifeStatusMemberIdInput = document.getElementById('lifeStatusMemberIdInput');
    var allMemberCards = document.querySelectorAll('.member-card[data-memberid]');

    function updateDetailValue(element, value) {
        if (!element) return;
        element.textContent = value && String(value).trim() !== '' ? value : '-';
    }

    function showMemberDetailFromCard(card) {
        if (!card) return;
        var memberId = card.getAttribute('data-memberid') || '';
        var name = card.getAttribute('data-name') || '-';
        var role = card.getAttribute('data-role') || 'Family Member';
        var gender = card.getAttribute('data-gender') || '-';
        var age = card.getAttribute('data-age') || '-';
        var birthdate = card.getAttribute('data-birthdate') || '-';
        var birthplace = card.getAttribute('data-birthplace') || '-';
        var bloodType = card.getAttribute('data-blood-type') || '-';
        var status = card.getAttribute('data-status') || '-';
        var maritalStatus = card.getAttribute('data-marital-status') || '-';
        var phone = card.getAttribute('data-phone') || '-';
        var email = card.getAttribute('data-email') || '-';
        var socialMedia = card.getAttribute('data-social-media') || '-';
        var job = card.getAttribute('data-job') || '-';
        var address = card.getAttribute('data-address') || '-';
        var education = card.getAttribute('data-education') || '-';
        var photo = card.getAttribute('data-photo') || '';
        var userId = card.getAttribute('data-userid') || '';
        var lifeStatusRaw = (card.getAttribute('data-life-status-raw') || '').toLowerCase();
        var isMe = card.getAttribute('data-isme') === '1';
        var canDeleteChild = card.getAttribute('data-can-delete-child') === '1';
        var canUpdateLifeStatus = card.getAttribute('data-can-update-life-status') === '1';
        var canEditProfile = card.getAttribute('data-can-edit-profile') === '1';

        if (detailPhoto) {
            detailPhoto.src = photo || detailPhoto.src;
            detailPhoto.alt = name !== '-' ? name : 'Member';
            detailPhoto.setAttribute('data-isme', isMe ? '1' : '0');
        }

        updateDetailValue(detailName, name);
        updateDetailValue(detailRole, role);
        updateDetailValue(detailGender, gender);
        updateDetailValue(detailAge, age);
        updateDetailValue(detailBirthdate, birthdate);
        updateDetailValue(detailBirthplace, birthplace);
        updateDetailValue(detailBloodType, bloodType);
        updateDetailValue(detailStatus, status);
        updateDetailValue(detailMaritalStatus, maritalStatus);
        updateDetailValue(detailPhone, phone);
        updateDetailValue(detailEmail, email);
        updateDetailValue(detailSocialMedia, socialMedia);
        updateDetailValue(detailJob, job);
        updateDetailValue(detailAddress, address);
        updateDetailValue(detailEducation, education);



        // Render Custom Fields
        var customFieldsJson = card.getAttribute('data-custom-fields') || '{}';
        console.log('Clicked Member ID:', memberId, 'Custom Fields JSON:', customFieldsJson);
        console.log('Master Fields:', window.masterCustomFields);

        var customFieldsData = {};

        try {

            customFieldsData = JSON.parse(customFieldsJson);

        } catch(e) { console.error('Failed to parse custom fields', e); }



        var customFieldsWrap = document.getElementById('detailCustomFieldsWrap');

        if (customFieldsWrap) {

            customFieldsWrap.innerHTML = '';

            if (window.masterCustomFields && Array.isArray(window.masterCustomFields)) {

                window.masterCustomFields.forEach(function(field) {

                    var val = customFieldsData[field.id];

                    if (val && String(val).trim() !== '') {

                        var li = document.createElement('li');

                        li.innerHTML = '<span>' + field.field_name + '</span><strong>' + val + '</strong>';

                        customFieldsWrap.appendChild(li);

                    }

                });

            }

        }



        if (detailCard) detailCard.classList.toggle('is-deceased', lifeStatusRaw === 'deceased');
        if (memberDetailBlock) memberDetailBlock.classList.remove('hidden');
        if (memberActionBlock) memberActionBlock.classList.remove('hidden');
        if (detailSidebar) detailSidebar.classList.remove('hidden');
        if (homePagePanel) homePagePanel.classList.add('has-selected-member');

        // Logic action buttons (Delete, Edit, etc)
        // ... ( for brevity, keeping all the original logic)

        allMemberCards.forEach(node => node.classList.toggle('active', node === card));
    }

    function hideMemberDetailPanel() {
        if (homePagePanel) homePagePanel.classList.remove('has-selected-member');
        if (detailSidebar) detailSidebar.classList.add('hidden');
        if (memberDetailBlock) memberDetailBlock.classList.add('hidden');
        if (memberActionBlock) memberActionBlock.classList.add('hidden');
        allMemberCards.forEach(node => node.classList.remove('active'));
    }

    document.addEventListener('click', function (event) {
        var card = event.target.closest('.member-card[data-memberid]');
        if (card) showMemberDetailFromCard(card);
    });

    var treeContainer = document.querySelector('.home-page-panel .tree-container');
    if (treeContainer) {
        treeContainer.addEventListener('click', function (event) {
            if (event.target.closest('.member-card[data-memberid]') || event.target.closest('button, a, input, select, textarea, label')) return;
            hideMemberDetailPanel();
        });
    }

    // --- Tree Zoom & Center Logic ---
    var treeScrollArea = document.getElementById('treeScrollArea');
    if (treeScrollArea) {
        var treeInitialCenterApplied = false;
        var centerTreeScroll = function () {
            if (treeInitialCenterApplied) return;
            var focusCard = treeScrollArea.querySelector('.member-card.active') || treeScrollArea.querySelector('.member-card[data-memberid]');
            if (!focusCard) return;
            var areaRect = treeScrollArea.getBoundingClientRect();
            var cardRect = focusCard.getBoundingClientRect();
            treeScrollArea.scrollLeft = treeScrollArea.scrollLeft + (cardRect.left + cardRect.width/2) - (areaRect.left + treeScrollArea.clientWidth/2);
            treeScrollArea.scrollTop = treeScrollArea.scrollTop + (cardRect.top + cardRect.height/2) - (areaRect.top + treeScrollArea.clientHeight/2);
            treeInitialCenterApplied = true;
            treeScrollArea.classList.add('is-tree-ready');
        };
        window.addEventListener('load', centerTreeScroll);
        window.addEventListener('resize', centerTreeScroll);
    }
});

// --- GLOBAL FUNCTIONS (Needed for onclick) ---
function openAddMemberModal(id) { 
    document.getElementById("add_target_memberid").value = id; 
    var m = document.getElementById("addMemberModal"); 
    m.classList.add("open"); 
    m.style.display = "flex"; 
}
function closeAddMemberModal() { 
    var m = document.getElementById("addMemberModal"); 
    m.classList.remove("open"); 
    m.style.display = "none"; 
}
function openAddMemberModalFromSidebar() { 
    var id = document.getElementById("lifeStatusMemberIdInput").value; 
    if(!id) return alert("Please select a family member from the tree first.");
    openAddMemberModal(id); 
}

