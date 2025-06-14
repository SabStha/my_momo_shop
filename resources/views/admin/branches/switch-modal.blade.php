<!-- Branch Switch Modal -->
<div id="branchSwitchModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 hidden" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full" role="document">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Switch Branch</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeBranchSwitchModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <div class="px-6 py-4">
                <div class="mb-4">
                    <label for="branchName" class="block text-sm font-medium text-gray-700">Selected Branch</label>
                    <input type="text" id="branchName" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm" readonly>
                </div>
                
                <div id="passwordField" class="mb-4 hidden">
                    <label for="branchPassword" class="block text-sm font-medium text-gray-700">Branch Password</label>
                    <input type="password" 
                           id="branchPassword"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           placeholder="Enter branch password">
                    <p id="passwordError" class="mt-1 text-sm text-red-600 hidden"></p>
                </div>
            </div>
            
            <div class="px-6 py-4 bg-gray-50 rounded-b-lg flex justify-end space-x-3">
                <button type="button" 
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        onclick="closeBranchSwitchModal()">
                    Cancel
                </button>
                <button type="button" 
                        id="switchBranchBtn"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        onclick="verifyAndSwitchBranch()">
                    Switch Branch
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let selectedBranchId = null;
let selectedBranchName = null;
let selectedBranchNeedsPassword = false;

function showBranchSwitchModal(branchId, branchName, needsPassword) {
    if (!branchId) {
        console.error('Invalid branch ID');
        return;
    }
    
    selectedBranchId = branchId;
    selectedBranchName = branchName;
    selectedBranchNeedsPassword = needsPassword;
    
    const modal = document.getElementById('branchSwitchModal');
    const branchNameInput = document.getElementById('branchName');
    const passwordField = document.getElementById('passwordField');
    
    if (!modal || !branchNameInput || !passwordField) {
        console.error('Required modal elements not found');
        return;
    }
    
    branchNameInput.value = branchName;
    passwordField.classList.toggle('hidden', !needsPassword);
    modal.classList.remove('hidden');
}

function closeBranchSwitchModal() {
    const modal = document.getElementById('branchSwitchModal');
    if (modal) {
        modal.classList.add('hidden');
    }
    selectedBranchId = null;
    selectedBranchName = null;
    selectedBranchNeedsPassword = false;
}

function verifyAndSwitchBranch() {
    if (!selectedBranchId) {
        alert('No branch selected');
        return;
    }

    if (selectedBranchNeedsPassword) {
        const password = document.getElementById('branchPassword').value;
        if (!password) {
            alert('Please enter the branch password');
            return;
        }

        console.log('Verifying password for branch:', selectedBranchId);

        // First verify the password
        fetch(`/admin/branches/${selectedBranchId}/verify`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ password: password })
        })
        .then(response => {
            console.log('Verification response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Verification response:', data);
            if (data.success) {
                // If password is verified, switch to the branch
                switchBranch();
            } else {
                alert(data.message || 'Invalid password');
            }
        })
        .catch(error => {
            console.error('Error during verification:', error);
            alert('Failed to verify password. Please try again.');
        });
    } else {
        // If no password required, switch directly
        switchBranch();
    }
}

function switchBranch() {
    if (!selectedBranchId) {
        alert('No branch selected');
        return;
    }

    console.log('Switching to branch:', selectedBranchId);

    fetch(`/admin/branches/${selectedBranchId}/switch`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        console.log('Switch response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Switch response:', data);
        if (data.success) {
            // Update the branch name in the UI
            const branchNameElements = document.querySelectorAll('[data-branch-name]');
            branchNameElements.forEach(element => {
                element.textContent = data.branch.name;
            });
            
            // Update any branch-specific data
            if (typeof updateBranchData === 'function') {
                updateBranchData(data.branch);
            }
            
            // Close the modal
            closeBranchSwitchModal();
            
            // Show success message
            const successMessage = document.createElement('div');
            successMessage.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded shadow-lg z-50';
            successMessage.textContent = data.message;
            document.body.appendChild(successMessage);
            
            // Remove success message after 3 seconds
            setTimeout(() => {
                successMessage.remove();
            }, 3000);
        } else {
            alert(data.message || 'Failed to switch branch');
        }
    })
    .catch(error => {
        console.error('Error during switch:', error);
        alert('Failed to switch branch. Please try again.');
    });
}
</script>
@endpush 