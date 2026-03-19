// Cash Drawer module

const _denomOrder = [1000, 500, 100, 50, 20, 10, 5, 2, 1];

// Store expected total so recalc can read it without re-fetching
let _drawerExpected = 0;

async function showPhysicalDrawerDenominationsModal() {
    const branchId = document.getElementById('paymentApp').dataset.branchId;
    try {
        const response = await fetch(`/api/admin/cash-drawer/status?branch_id=${branchId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            credentials: 'same-origin'
        });

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Server returned non-JSON response. Please check authentication.');
        }

        const data = await response.json();
        if (!response.ok) throw new Error(data.message || data.error || 'Failed to fetch denominations');

        const openingDenoms  = data.opening_denominations  || {};
        const currentDenoms  = data.current_denominations  || {};  // null = not yet counted
        const openingAmount  = data.opening_amount         || 0;
        const cashReceived   = data.total_cash_received    || 0;
        _drawerExpected = openingAmount + cashReceived;

        // Populate table rows
        let openingTotal = 0;
        _denomOrder.forEach(d => {
            const openCount = parseInt(openingDenoms[d] ?? openingDenoms[String(d)] ?? 0);
            const openVal   = openCount * d;
            openingTotal   += openVal;

            // Pre-fill current count — use saved count if available, else opening count
            const currCount = data.current_denominations
                ? parseInt(currentDenoms[d] ?? currentDenoms[String(d)] ?? 0)
                : openCount;

            const openEl    = document.getElementById('open-' + d);
            const openValEl = document.getElementById('open-val-' + d);
            const currInput = document.getElementById('curr-' + d);
            const currValEl = document.getElementById('curr-val-' + d);

            if (openEl)    openEl.textContent    = openCount;
            if (openValEl) openValEl.textContent  = 'Rs ' + openVal;
            if (currInput) currInput.value        = currCount;
            if (currValEl) currValEl.textContent  = 'Rs ' + (currCount * d);
        });

        document.getElementById('opening-total').textContent = 'Rs ' + openingTotal;
        document.getElementById('expected-total').textContent = 'Rs ' + _drawerExpected.toFixed(2);

        // Wire current-count inputs to live recalc
        _denomOrder.forEach(d => {
            const input = document.getElementById('curr-' + d);
            if (input) input.oninput = _recalcDenominations;
        });

        _recalcDenominations();

        document.getElementById('physicalDrawerDenominationsModal').classList.remove('hidden');
        initializePasswordValidation();
    } catch (error) {
        console.error('Error fetching denominations:', error);
        showErrorModal('Error', 'Failed to fetch denominations: ' + error.message);
    }
}
window.showPhysicalDrawerDenominationsModal = showPhysicalDrawerDenominationsModal;

function _recalcDenominations() {
    let currentTotal = 0;
    _denomOrder.forEach(d => {
        const input = document.getElementById('curr-' + d);
        const valEl = document.getElementById('curr-val-' + d);
        const count = parseInt(input ? input.value : 0) || 0;
        const val   = count * d;
        currentTotal += val;
        if (valEl) valEl.textContent = 'Rs ' + val;
    });

    const totalEl = document.getElementById('current-total');
    if (totalEl) totalEl.textContent = 'Rs ' + currentTotal;

    const diff    = currentTotal - _drawerExpected;
    const diffEl  = document.getElementById('difference');
    if (diffEl) {
        const abs = Math.abs(diff).toFixed(2);
        diffEl.textContent = diff === 0
            ? 'Rs 0 (balanced)'
            : 'Rs ' + abs + (diff > 0 ? ' (surplus)' : ' (shortage)');
        diffEl.style.color = diff >= 0 ? '#16a34a' : '#dc2626';
    }
}
window.recalcCurrentDenomsTotal = _recalcDenominations; // legacy alias

// Function to open cash adjustment modal for a specific denomination
window.adjustDenominationFromAlert = function(denomination) {
    // Show the cash adjustment modal
    if (typeof showCashAdjustmentModal === 'function') {
        showCashAdjustmentModal();
        // Prefill only the selected denomination for adjustment, others to 0
        [1000,500,100,50,20,10,5,2,1].forEach(denom => {
            const input = document.getElementById('adjust_' + denom);
            if (input) input.value = denom === denomination ? '' : 0;
        });
        // Focus the input for the selected denomination
        const selectedInput = document.getElementById('adjust_' + denomination);
        if (selectedInput) selectedInput.focus();
    }
};

// Wire up close buttons for the modal
function wirePhysicalDrawerDenominationsModalClose() {
    const closeBtns = [
        document.getElementById('closePhysicalDrawerDenominationsModalBtn'),
        document.getElementById('closePhysicalDrawerDenominationsModalBtn2')
    ];
    closeBtns.forEach(btn => {
        if (btn) btn.onclick = () => {
            document.getElementById('physicalDrawerDenominationsModal').classList.add('hidden');
        };
    });
}
document.addEventListener('DOMContentLoaded', wirePhysicalDrawerDenominationsModalClose);

// Update openPhysicalCashDrawer to just open the drawer without password
async function openPhysicalCashDrawer() {
    const branchId = document.getElementById('paymentApp').dataset.branchId;
    try {
        const response = await fetch(`/admin/cash-drawer/open-physical`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ branch_id: branchId }),
            credentials: 'same-origin'
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || data.error || 'Failed to open cash drawer');
        showSuccessModal('Success', 'Physical cash drawer opened!');
        showPhysicalDrawerDenominationsModal();
    } catch (error) {
        showErrorModal('Error', error.message);
    }
}
window.openPhysicalCashDrawer = openPhysicalCashDrawer;

function showCashAdjustmentModal() {
    const modal = document.getElementById('cashAdjustmentModal');
    if (modal) {
        modal.classList.remove('hidden');
        // Reset all adjustment fields to 0
        [1000,500,100,50,20,10,5,2,1].forEach(denom => {
            const input = document.getElementById('adjust_' + denom);
            if (input) input.value = 0;
        });
        // Optionally reset password and reason fields
        const pwd = document.getElementById('adjustmentPassword');
        if (pwd) pwd.value = '';
        const reason = document.getElementById('adjustmentReason');
        if (reason) reason.value = '';
    }
}
window.showCashAdjustmentModal = showCashAdjustmentModal;

// Alert Settings Modal Logic
function showAlertSettingsModal() {
    const modal = document.getElementById('alertSettingsModal');
    if (modal) {
        modal.classList.remove('hidden');
        // Fetch current thresholds from backend
        const branchId = document.getElementById('paymentApp').dataset.branchId;
        fetch(`/api/admin/cash-drawer/alerts?branch_id=${branchId}`)
            .then(res => res.json())
            .then(data => {
                const alerts = data.alerts || [];
                const alertMap = {};
                alerts.forEach(a => alertMap[a.denomination] = a);
                [1000,500,100,50,20,10,5,2,1].forEach(denom => {
                    const low = document.getElementById('low_' + denom);
                    const high = document.getElementById('high_' + denom);
                    if (low) low.value = alertMap[denom]?.low_threshold ?? '';
                    if (high) high.value = alertMap[denom]?.high_threshold ?? '';
                });
            });
    }
}
window.showAlertSettingsModal = showAlertSettingsModal;

function hideAlertSettingsModal() {
    const modal = document.getElementById('alertSettingsModal');
    if (modal) modal.classList.add('hidden');
}
window.hideAlertSettingsModal = hideAlertSettingsModal;

document.getElementById('closeAlertSettingsModalBtn')?.addEventListener('click', hideAlertSettingsModal);
document.getElementById('alertSettingsForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const branchId = document.getElementById('paymentApp').dataset.branchId;
    const spinner = document.getElementById('alertSettingsLoadingSpinner');
    if (spinner) spinner.classList.remove('hidden');
    try {
        const updates = [1000,500,100,50,20,10,5,2,1].map(denom => ({
            denomination: denom,
            low_threshold: document.getElementById('low_' + denom)?.value || null,
            high_threshold: document.getElementById('high_' + denom)?.value || null
        }));
        const response = await fetch('/admin/cash-drawer/alerts/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ branch_id: branchId, updates }),
            credentials: 'same-origin'
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || data.error || 'Failed to update alert settings');
        showSuccessModal('Success', 'Alert settings updated!');
        hideAlertSettingsModal();
    } catch (error) {
        showErrorModal('Error', error.message);
    } finally {
        if (spinner) spinner.classList.add('hidden');
    }
});

// Password validation for denominations
let isPasswordValidated = false;

// Function to initialize password validation
function initializePasswordValidation() {
    const validateBtn = document.getElementById('validatePasswordBtn');
    const saveBtn = document.getElementById('saveDenominationsBtn');
    const closeBtn1 = document.getElementById('closePhysicalDrawerDenominationsModalBtn');
    const closeBtn2 = document.getElementById('closePhysicalDrawerDenominationsModalBtn2');

    if (validateBtn && !validateBtn.hasAttribute('data-initialized')) {
        validateBtn.setAttribute('data-initialized', 'true');
        validateBtn.addEventListener('click', async function() {
            const password = document.getElementById('denominationPassword').value;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            try {
                const response = await fetch('/admin/cash-drawer/verify-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ password: password })
                });
                const data = await response.json();
                if (response.ok && data.valid) {
                    isPasswordValidated = true;
                    document.getElementById('saveDenominationsBtn').disabled = false;
                    document.getElementById('passwordError').classList.add('hidden');
                    document.getElementById('denominationPassword').disabled = true;
                    this.disabled = true;
                    this.textContent = 'Validated ✓';
                    this.classList.remove('bg-yellow-500', 'hover:bg-yellow-600');
                    this.classList.add('bg-green-500');
                } else {
                    document.getElementById('passwordError').classList.remove('hidden');
                    document.getElementById('denominationPassword').value = '';
                }
            } catch (error) {
                console.error('Error verifying password:', error);
                document.getElementById('passwordError').classList.remove('hidden');
                document.getElementById('denominationPassword').value = '';
            }
        });
    }

    if (saveBtn && !saveBtn.hasAttribute('data-initialized')) {
        saveBtn.setAttribute('data-initialized', 'true');
        saveBtn.addEventListener('click', async function() {
            console.log('Save button clicked');

            if (!isPasswordValidated) {
                alert('Please validate your password first.');
                return;
            }

            // Collect current denomination counts from table inputs
            const denominations = {};
            _denomOrder.forEach(d => {
                const input = document.getElementById('curr-' + d);
                denominations[d] = parseInt(input ? input.value : 0) || 0;
            });

            console.log('Collected denominations:', denominations);

            try {
                const requestBody = {
                    branch_id: document.getElementById('paymentApp').dataset.branchId,
                    password: document.getElementById('denominationPassword').value,
                    adjustments: denominations,
                    reason: 'Manual denomination update'
                };

                console.log('Sending request:', requestBody);

                const response = await fetch('/admin/cash-drawer/update-denominations', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify(requestBody),
                    credentials: 'same-origin'
                });

                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);

                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                console.log('Content-Type:', contentType);

                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    console.log('Response text:', text.substring(0, 200));
                    throw new Error('Server returned non-JSON response. Please check authentication.');
                }

                const data = await response.json();
                console.log('Response data:', data);

                if (data.success) {
                    console.log('Success! Showing success modal');
                    // Show success message
                    showSuccessModal('Success', 'Denominations updated successfully');
                    // Close the modal
                    document.getElementById('closePhysicalDrawerDenominationsModalBtn').click();
                    // Reset password validation state
                    resetDenominationPasswordValidation();
                } else {
                    console.log('Error from server:', data.message);
                    showErrorModal('Error', data.message || 'Failed to update denominations');
                }
            } catch (error) {
                console.error('Error updating denominations:', error);
                showErrorModal('Error', 'An error occurred while updating denominations: ' + error.message);
            }
        });
    }

    if (closeBtn1 && !closeBtn1.hasAttribute('data-initialized')) {
        closeBtn1.setAttribute('data-initialized', 'true');
        closeBtn1.addEventListener('click', resetDenominationPasswordValidation);
    }

    if (closeBtn2 && !closeBtn2.hasAttribute('data-initialized')) {
        closeBtn2.setAttribute('data-initialized', 'true');
        closeBtn2.addEventListener('click', resetDenominationPasswordValidation);
    }
}

// Reset password validation when modal is closed
function resetDenominationPasswordValidation() {
    isPasswordValidated = false;
    const passwordInput = document.getElementById('denominationPassword');
    const validateBtn = document.getElementById('validatePasswordBtn');
    const saveBtn = document.getElementById('saveDenominationsBtn');
    const errorMsg = document.getElementById('passwordError');

    if (passwordInput) passwordInput.value = '';
    if (passwordInput) passwordInput.disabled = false;
    if (validateBtn) {
        validateBtn.disabled = false;
        validateBtn.textContent = 'Validate';
        validateBtn.classList.remove('bg-green-500');
        validateBtn.classList.add('bg-yellow-500', 'hover:bg-yellow-600');
    }
    if (saveBtn) saveBtn.disabled = true;
    if (errorMsg) errorMsg.classList.add('hidden');
}

// Make functions globally available
window.initializePasswordValidation = initializePasswordValidation;
window.resetDenominationPasswordValidation = resetDenominationPasswordValidation;

// Check cash drawer status
async function checkCashDrawerStatus(branchId) {
    try {
        const response = await fetch(`/api/business/status/${branchId}`);
        const data = await response.json();
        return {
            isOpen: data.is_open,
            message: data.message
        };
    } catch (error) {
        console.error('Error checking cash drawer status:', error);
        return { isOpen: false, message: 'Unable to check drawer status' };
    }
}
