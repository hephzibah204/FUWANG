<?php $__env->startSection('title', 'Create Logistics Account'); ?>

<?php $__env->startSection('content'); ?>
<div class="row align-items-center" style="min-height: calc(100vh - 180px);">
    <div class="col-lg-6 mb-4 mb-lg-0">
        <div class="mb-3">
            <span class="badge badge-pill px-3 py-2" style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.25); color: var(--po-primary);">
                FuwaPost Logistics
            </span>
        </div>
        <h1 class="font-weight-bold mb-3" style="font-size: 2.5rem; line-height: 1.1;">
            Create your <span style="color:var(--po-primary)">Logistics</span> account
        </h1>
        <p class="text-white-50 mb-4" style="max-width: 560px;">
            Book shipments, generate waybills instantly, and track deliveries with clear status updates.
        </p>
        <div class="glass-card p-3 d-inline-flex align-items-center">
            <img src="<?php echo e(\App\Support\TestimonialAvatars::url('Seamless tracking')); ?>" alt="Customer" class="rounded-circle mr-3" style="width: 42px; height: 42px; object-fit: cover;">
            <div>
                <div class="font-weight-bold">Seamless tracking</div>
                <div class="text-white-50 small">Know where your package is, at every step.</div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="glass-card p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="font-weight-bold mb-0">Create account</h4>
                <a href="<?php echo e(route('logistics.login')); ?>" class="text-white-50 text-decoration-none small">
                    Already have an account?
                    <span style="color:var(--po-primary); font-weight: 600;">Login</span>
                </a>
            </div>

            <div class="mb-4">
                <div class="text-white-50 small mb-2">Already have a Fuwa.NG account? Login with your account</div>
                <div class="d-flex flex-wrap">
                    <a href="<?php echo e(route('login')); ?>?service=logistics&redirect=<?php echo e(urlencode('/logistics/dashboard')); ?>"
                       class="btn btn-outline-light mr-3 mb-2"
                       style="border-radius: 12px;">
                        <i class="fa fa-shield-halved mr-2" style="color: var(--po-primary);"></i> Continue with Fuwa.NG
                    </a>
                    <a href="<?php echo e(route('auth.google.redirect', ['service' => 'logistics', 'redirect' => '/logistics/dashboard'])); ?>"
                       class="btn btn-outline-light mb-2"
                       style="border-radius: 12px;">
                        <i class="fa-brands fa-google mr-2" style="color: var(--po-primary);"></i> Continue with Google
                    </a>
                </div>
            </div>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger border-0 mb-4" style="background: rgba(220, 38, 38, 0.15); color: #fff;">
                    <div class="font-weight-bold mb-2">Please fix the errors below</div>
                    <ul class="mb-0 pl-3">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="small"><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('logistics.register')); ?>" id="registerForm">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="service" value="logistics">
                <input type="hidden" name="apply_as_agent" value="0">

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="fullname" class="text-white-50 small">Full name</label>
                        <input id="fullname" name="fullname" type="text" class="form-control tracking-input" required autofocus value="<?php echo e(old('fullname')); ?>">
                        <?php $__errorArgs = ['fullname'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="text-danger d-block mt-1"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="username" class="text-white-50 small">Username</label>
                        <input id="username" name="username" type="text" class="form-control tracking-input" required value="<?php echo e(old('username')); ?>">
                        <?php $__errorArgs = ['username'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="text-danger d-block mt-1"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email" class="text-white-50 small">Email address</label>
                    <input id="email" name="email" type="email" class="form-control tracking-input" required value="<?php echo e(old('email')); ?>">
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="text-danger d-block mt-1"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="form-group">
                    <label for="referral_code" class="text-white-50 small">Invite code (optional)</label>
                    <input id="referral_code" name="referral_code" type="text" class="form-control tracking-input" value="<?php echo e(old('referral_code', request('ref') ?? request('referral_code'))); ?>" placeholder="Enter invite code">
                    <?php $__errorArgs = ['referral_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="text-danger d-block mt-1"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="password" class="text-white-50 small">Password</label>
                        <input id="password" name="password" type="password" class="form-control tracking-input" required>
                        <small class="text-white-50 d-block mt-1">Minimum 8 characters with mixed case, numbers, and symbols.</small>
                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="text-danger d-block mt-1"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="password_confirmation" class="text-white-50 small">Confirm password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" class="form-control tracking-input" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="transaction_pin" class="text-white-50 small">Transaction PIN</label>
                        <input id="transaction_pin" name="transaction_pin" type="text" class="form-control tracking-input" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" required value="<?php echo e(old('transaction_pin')); ?>">
                        <?php $__errorArgs = ['transaction_pin'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="text-danger d-block mt-1"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="form-group col-md-6 d-flex align-items-end">
                        <div class="p-3 rounded-lg w-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 16px;">
                            <div class="text-white-50 small">Tip</div>
                            <div class="small">Use a 4‑digit PIN you can remember.</div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" type="checkbox" name="apply_as_agent" id="apply_as_agent" value="1" <?php echo e(old('apply_as_agent') ? 'checked' : ''); ?>>
                        <label class="custom-control-label text-white-50 small" for="apply_as_agent">
                            Apply as Delivery Agent
                        </label>
                    </div>
                </div>

                <div id="agent-fields" class="<?php echo e(old('apply_as_agent') ? '' : 'd-none'); ?>">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="state" class="text-white-50 small">State</label>
                            <select id="state" name="state" class="form-control tracking-input" <?php echo e(old('apply_as_agent') ? '' : 'disabled'); ?>>
                                <option value="">Select state</option>
                                <?php $__currentLoopData = $nigeriaStates ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stateName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($stateName); ?>" <?php if(old('state') === $stateName): echo 'selected'; endif; ?>><?php echo e($stateName); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['state'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="text-danger d-block mt-1"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="city" class="text-white-50 small">City or town</label>
                            <select id="city" name="city" class="form-control tracking-input" <?php echo e(old('state') ? '' : 'disabled'); ?>>
                                <option value="">Select city or town</option>
                            </select>
                            <?php $__errorArgs = ['city'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="text-danger d-block mt-1"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="address" class="text-white-50 small">Address</label>
                            <input id="address" name="address" type="text" class="form-control tracking-input" value="<?php echo e(old('address')); ?>" placeholder="Enter your full address">
                            <?php $__errorArgs = ['address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="text-danger d-block mt-1"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="phone_number" class="text-white-50 small">Phone number</label>
                            <input id="phone_number" name="phone_number" type="text" class="form-control tracking-input" value="<?php echo e(old('phone_number')); ?>" placeholder="e.g., 08012345678">
                            <?php $__errorArgs = ['phone_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><small class="text-danger d-block mt-1"><?php echo e($message); ?></small><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input id="terms" name="terms" type="checkbox" class="custom-control-input" required>
                        <label class="custom-control-label text-white-50 small" for="terms">
                            I agree to the <a href="<?php echo e(route('pages.show', ['slug' => 'terms'])); ?>" class="text-decoration-none" style="color: var(--po-primary);">Terms of Service</a>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-po-primary btn-block">
                    <i class="fa fa-user-plus mr-2"></i> Create account
                </button>
                <div class="text-center mt-3 text-white-50 small">
                    By signing up, you’ll be redirected to your Logistics dashboard.
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    window.NIGERIA_CITIES_JSON_URL = <?php echo json_encode(asset('data/nigeria-state-cities.json')); ?>;
    window.OLD_AGENT_CITY = <?php echo json_encode(old('city')); ?>;
</script>
<script>
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var agentCb = document.getElementById('apply_as_agent');
        var wrap = document.getElementById('agent-fields');
        var stateEl = document.getElementById('state');
        var cityEl = document.getElementById('city');
        var agentRequiredIds = ['address', 'phone_number'];
        if (!agentCb || !wrap || !stateEl || !cityEl) {
            return;
        }

        var locMap = null;
        var locPromise = null;

        function loadLocations() {
            if (locMap) {
                return Promise.resolve(locMap);
            }
            if (locPromise) {
                return locPromise;
            }
            var url = window.NIGERIA_CITIES_JSON_URL;
            if (!url) {
                locMap = {};
                return Promise.resolve(locMap);
            }
            locPromise = fetch(url, { credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (arr) {
                    locMap = {};
                    if (Array.isArray(arr)) {
                        arr.forEach(function (row) {
                            if (!row || !row.state) {
                                return;
                            }
                            var list = row.cities || row.lgas;
                            if (Array.isArray(list)) {
                                locMap[row.state] = list;
                            }
                        });
                    }
                    return locMap;
                })
                .catch(function () {
                    locMap = {};
                    return locMap;
                });
            return locPromise;
        }

        function fillCities(stateName) {
            cityEl.innerHTML = '';
            var opt0 = document.createElement('option');
            opt0.value = '';
            opt0.textContent = 'Select city or town';
            cityEl.appendChild(opt0);
            if (!stateName || !locMap || !locMap[stateName]) {
                cityEl.disabled = true;
                return;
            }
            locMap[stateName].forEach(function (name) {
                var o = document.createElement('option');
                o.value = name;
                o.textContent = name;
                cityEl.appendChild(o);
            });
            cityEl.disabled = false;
            var preferred = window.OLD_AGENT_CITY;
            if (preferred && locMap[stateName].indexOf(preferred) !== -1) {
                cityEl.value = preferred;
            }
        }

        function syncAgentPanel() {
            if (agentCb.checked) {
                wrap.classList.remove('d-none');
                stateEl.disabled = false;
                stateEl.setAttribute('required', 'required');
                cityEl.setAttribute('required', 'required');
                agentRequiredIds.forEach(function (id) {
                    var el = document.getElementById(id);
                    if (el) {
                        el.setAttribute('required', 'required');
                    }
                });
                loadLocations().then(function () {
                    fillCities(stateEl.value);
                });
            } else {
                wrap.classList.add('d-none');
                stateEl.removeAttribute('required');
                cityEl.removeAttribute('required');
                cityEl.disabled = true;
                stateEl.disabled = true;
                agentRequiredIds.forEach(function (id) {
                    var el = document.getElementById(id);
                    if (el) {
                        el.removeAttribute('required');
                    }
                });
            }
        }

        agentCb.addEventListener('change', syncAgentPanel);
        stateEl.addEventListener('change', function () {
            loadLocations().then(function () {
                fillCities(stateEl.value);
            });
        });

        loadLocations().then(function () {
            if (stateEl.value) {
                fillCities(stateEl.value);
            } else {
                cityEl.disabled = true;
            }
        });

        syncAgentPanel();
    });
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.postoffice', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/fuwa.ng/html/resources/views/logistics/auth/register.blade.php ENDPATH**/ ?>