<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <div id="pwaInstallBanner" class="alert alert-info d-flex align-items-center justify-content-between mb-4" style="display:none;">
                <div>
                    <strong>Install the App!</strong> For a better experience, install our PWA.
                </div>
                <button id="pwaInstallButton" class="btn btn-primary ms-3">Install App</button>
            </div>
            <div id="pwaInstallNotAvailable" class="alert alert-warning mb-4" style="display:none;">
                <strong>PWA installation is not available on your device/browser.</strong>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center">
                            <img src="<?php echo e(asset($user->profile_picture ?? 'images/default-avatar.png')); ?>" class="rounded-circle me-3" width="60" height="60" alt="User Avatar" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#profilePictureModal">
                            <div>
                                <h4 class="mb-0"><?php echo e($user->name); ?></h4>
                                <small class="text-muted">
                                    Joined on <?php echo e($user->created_at->format('M d, Y')); ?>

                                    <?php if($user->email_verified_at): ?>
                                        <span class="badge bg-success ms-2">Verified</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning ms-2">Unverified</span>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#referralModal">
                                <i class="fas fa-user-plus"></i> Refer Friends
                            </button>
                            <a href="<?php echo e(route('logout')); ?>" class="btn btn-outline-danger"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                            <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="d-none">
                                <?php echo csrf_field(); ?>
                            </form>
                        </div>
                    </div>

                    
                    <?php if(session('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo e(session('success')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if(session('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo e(session('error')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    
                    <ul class="nav nav-tabs mb-4" id="myAccountTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">Profile</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab">Orders</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="offers-tab" data-bs-toggle="tab" data-bs-target="#offers" type="button" role="tab">Offers</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button" role="tab">Settings</button>
                        </li>
                    </ul>

                    
                    <div class="tab-content" id="myAccountTabsContent">
                        
                        <div class="tab-pane fade show active" id="profile" role="tabpanel">
                            <form method="POST" action="<?php echo e(route('my-account.update')); ?>" class="mb-4">
                                <?php echo csrf_field(); ?>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Name</label>
                                        <input name="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                               value="<?php echo e(old('name', $user->name)); ?>" required>
                                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input name="email" type="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                               value="<?php echo e(old('email', $user->email)); ?>" required>
                                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                </div>
                                <div class="mt-3 d-flex justify-content-between">
                                    <button class="btn btn-primary">Update Info</button>
                                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#passwordModal">
                                        Change Password
                                    </button>
                                </div>
                            </form>

                            
                            <h5 class="mb-3">Wallet Balance: <span class="text-success">Rs. <?php echo e($wallet ? number_format($wallet->balance, 2) : '0.00'); ?></span></h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <a href="<?php echo e(route('wallet.scan')); ?>" class="btn btn-dark w-100">
                                        <i class="fas fa-qrcode"></i> Top Up via QR Code
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <button class="btn btn-outline-success w-100" disabled>
                                        💡 Auto top-up coming soon
                                    </button>
                                </div>
                            </div>

                            
                            <h5>Recent Wallet Activity</h5>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Amount</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $txn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <tr>
                                                <td><?php echo e($txn->created_at->format('M d, Y H:i')); ?></td>
                                                <td><?php echo e(ucfirst($txn->type)); ?></td>
                                                <td class="<?php echo e($txn->type === 'credit' ? 'text-success' : 'text-danger'); ?>">
                                                    <?php echo e($txn->type === 'credit' ? '+' : '-'); ?>Rs. <?php echo e(number_format($txn->amount, 2)); ?>

                                                </td>
                                                <td><?php echo e($txn->description); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">No transactions yet.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        
                        <div class="tab-pane fade" id="orders" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Date</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                            <tr>
                                                <td>#<?php echo e($order->id); ?></td>
                                                <td><?php echo e($order->created_at->format('M d, Y H:i')); ?></td>
                                                <td>Rs. <?php echo e(number_format($order->total, 2)); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo e($order->status_color); ?>">
                                                        <?php echo e(ucfirst($order->status)); ?>

                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="<?php echo e(route('my-account.orders.show', $order)); ?>" class="btn btn-sm btn-outline-primary">
                                                        View Details
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No orders yet.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        
                        <div class="tab-pane fade" id="offers" role="tabpanel">
                            <div class="row g-4">
                                <?php $__empty_1 = true; $__currentLoopData = $offers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $offer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <div class="col-md-6">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo e($offer->title); ?></h5>
                                                <p class="card-text"><?php echo e($offer->description); ?></p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="badge bg-success"><?php echo e($offer->discount); ?>% OFF</span>
                                                    <small class="text-muted">Valid until <?php echo e($offer->valid_until->format('M d, Y')); ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            No active offers available at the moment.
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        
                        <div class="tab-pane fade" id="settings" role="tabpanel">
                            <form method="POST" action="<?php echo e(route('my-account.settings.update')); ?>">
                                <?php echo csrf_field(); ?>
                                <div class="mb-3">
                                    <label class="form-label">Email Notifications</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="notifications[orders]" id="notifyOrders" 
                                               <?php echo e($settings->notify_orders ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="notifyOrders">
                                            Order Updates
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="notifications[offers]" id="notifyOffers"
                                               <?php echo e($settings->notify_offers ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="notifyOffers">
                                            Special Offers
                                        </label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Save Settings</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="<?php echo e(route('my-account.password.update')); ?>">
            <?php echo csrf_field(); ?>
            <div class="modal-header">
                <h5 class="modal-title" id="passwordModalLabel">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="form-control <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                    <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="mb-3">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="form-control <?php $__errorArgs = ['new_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                    <?php $__errorArgs = ['new_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="mb-3">
                    <label>Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Password</button>
            </div>
        </form>
    </div>
</div>


<div class="modal fade" id="referralModal" tabindex="-1" aria-labelledby="referralModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="referralModalLabel">Refer Friends</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Share your referral link with friends and earn points for both of you!</p>
                <div class="mb-3">
                    <label class="form-label">Your Referral Link</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="referralLink" value="<?php echo e(url('/ref/' . $user->referral_code)); ?>" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyReferralLink()">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3 text-center">
                    <div id="referralQR"></div>
                    <small class="text-muted">Friends can scan this QR code to join and both of you will earn points!</small>
                </div>
                <div class="d-grid gap-2">
                    <button class="btn btn-primary" onclick="shareReferral()">
                        <i class="fas fa-share-alt me-2"></i>Share via WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="profilePictureModal" tabindex="-1" aria-labelledby="profilePictureModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="profilePictureModalLabel">Change Profile Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="profilePictureForm" method="POST" action="<?php echo e(route('my-account.update-profile-picture')); ?>" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <input type="file" name="profile_picture" id="profilePictureInput" class="d-none" accept="image/*">
                    <div class="text-center mb-3">
                        <img id="profilePicturePreview" src="<?php echo e(asset($user->profile_picture ?? 'images/default-avatar.png')); ?>" class="rounded-circle" width="150" height="150" alt="Profile Preview">
                    </div>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('profilePictureInput').click()">Choose Image</button>
                        <button type="submit" class="btn btn-primary">Save Profile Picture</button>
                    </div>
                </form>
                <div id="uploadLoading" class="text-center mt-3" style="display:none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Uploading...</p>
                </div>
                <div id="uploadMessage" class="alert mt-3" style="display:none;"></div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
// PWA Installation
let deferredPrompt;
const pwaInstallBanner = document.getElementById('pwaInstallBanner');
const pwaInstallButton = document.getElementById('pwaInstallButton');
const pwaInstallNotAvailable = document.getElementById('pwaInstallNotAvailable');

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    pwaInstallBanner.style.display = 'flex';
    pwaInstallNotAvailable.style.display = 'none';
});

if (!window.matchMedia('(display-mode: standalone)').matches && !window.deferredPrompt) {
    // If not installable, show not available message
    pwaInstallBanner.style.display = 'none';
    pwaInstallNotAvailable.style.display = 'block';
}

if (pwaInstallButton) {
    pwaInstallButton.addEventListener('click', async () => {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            if (outcome === 'accepted') {
                console.log('User accepted the install prompt');
            }
            deferredPrompt = null;
            pwaInstallBanner.style.display = 'none';
        }
    });
}

// Referral QR Code
function showReferralQR() {
    const referralLink = document.getElementById('referralLink').value;
    const qrDiv = document.getElementById('referralQR');
    qrDiv.innerHTML = '';
    new QRCode(qrDiv, {
        text: referralLink,
        width: 160,
        height: 160,
        colorDark : "#000000",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
    });
}
// Show QR when modal opens
$('#referralModal').on('shown.bs.modal', showReferralQR);

// Copy referral link
function copyReferralLink() {
    const referralLink = document.getElementById('referralLink');
    referralLink.select();
    referralLink.setSelectionRange(0, 99999);
    document.execCommand('copy');
    alert('Referral link copied to clipboard!');
}
// Share Referral
function shareReferral() {
    const referralLink = document.getElementById('referralLink').value;
    const message = `Join me on Momo Shop! Use my referral link: ${referralLink} to get points for both of us!`;
    const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
    window.open(whatsappUrl, '_blank');
}

// Profile Picture Upload
document.getElementById('profilePictureInput').addEventListener('change', function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profilePicturePreview').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// Handle profile picture form submission
document.getElementById('profilePictureForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const formData = new FormData(this);
    const uploadLoading = document.getElementById('uploadLoading');
    const uploadMessage = document.getElementById('uploadMessage');

    uploadLoading.style.display = 'block';
    uploadMessage.style.display = 'none';

    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        uploadLoading.style.display = 'none';
        uploadMessage.style.display = 'block';
        uploadMessage.className = 'alert alert-success';
        uploadMessage.textContent = data.message;
        setTimeout(() => {
            window.location.reload();
        }, 2000);
    })
    .catch(error => {
        uploadLoading.style.display = 'none';
        uploadMessage.style.display = 'block';
        uploadMessage.className = 'alert alert-danger';
        uploadMessage.textContent = 'An error occurred while uploading the profile picture.';
    });
});
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sabst\momo_shop\resources\views/user/my-account.blade.php ENDPATH**/ ?>