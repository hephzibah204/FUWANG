<?php $__env->startSection('title', 'CMS Settings – Admin'); ?>

<?php $__env->startSection('content'); ?>
<div class="row mb-4">
    <div class="col-12 d-flex align-items-center">
        <a href="<?php echo e(route('admin.dashboard')); ?>" class="btn btn-dark rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(255,255,255,0.05) !important; border: 1px solid rgba(255,255,255,0.1);">
            <i class="fa fa-arrow-left text-white"></i>
        </a>
        <div>
            <h3 class="text-white mb-0 fw-bold"><i class="fa fa-sliders text-primary mr-2"></i> CMS Settings</h3>
            <p class="text-white-50 mb-0">Global configuration for the entire platform.</p>
        </div>
    </div>
</div>


<div class="tab-strip mb-4">
    <button class="s-tab active" onclick="switchTab('tab-notification', this)"><i class="fa fa-bell mr-2"></i>Notification</button>
    <button class="s-tab" onclick="switchTab('tab-pricing', this)"><i class="fa fa-tag mr-2"></i>Pricing (All Services)</button>
    <button class="s-tab" onclick="switchTab('tab-banking', this)"><i class="fa fa-building-columns mr-2"></i>Payment Info</button>
    <button class="s-tab" onclick="switchTab('tab-api-settings', this)"><i class="fa fa-toggle-on mr-2"></i>API Config</button>
    <button class="s-tab" onclick="switchTab('tab-api-keys', this)"><i class="fa fa-key mr-2"></i>API Keys</button>
    <button class="s-tab" onclick="switchTab('tab-theme', this)"><i class="fa fa-palette mr-2"></i>Theme</button>
    <button class="s-tab" onclick="switchTab('tab-notary', this)"><i class="fa fa-gavel mr-2"></i>Notary & Branding</button>
    <button class="s-tab" onclick="switchTab('tab-security', this)"><i class="fa fa-shield-halved mr-2"></i>Security</button>
    <button class="s-tab" onclick="switchTab('tab-gateways', this)"><i class="fa fa-credit-card mr-2"></i>Payment Gateways</button>
    <button class="s-tab" onclick="switchTab('tab-features', this)"><i class="fa fa-toggle-on mr-2"></i>Service Toggles</button>
    <button class="s-tab" onclick="switchTab('tab-referrals', this)"><i class="fa fa-users mr-2"></i>Referrals & MLM</button>
    <button class="s-tab" onclick="switchTab('tab-auction', this)"><i class="fa fa-gavel mr-2"></i>Auction Settings</button>
    <button class="s-tab" onclick="window.location.href='<?php echo e(route('admin.settings.whatsapp_widget')); ?>'"><i class="fa-brands fa-whatsapp mr-2 text-success"></i>WhatsApp Widget</button>
</div>

<div class="s-panel active" id="tab-notification">
    <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
        <h5 class="text-white mb-1 fw-bold">System Notification</h5>
        <p class="text-white-50 small mb-4">This message is shown prominently to all users on their dashboard.</p>
        <form id="notifForm">
            <?php echo csrf_field(); ?>
            <div class="form-group mb-4">
                <label class="text-white-50 small mb-2">Notification Message</label>
                <textarea name="notification" id="notifText" class="form-control text-white rounded-3" rows="4" maxlength="500" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); resize: vertical;"><?php echo e($notification->notification ?? ''); ?></textarea>
            </div>
            
            <hr style="border-color: rgba(255,255,255,0.07);" class="my-4">
            
            <h5 class="text-white mb-1 fw-bold">Balance Alerts</h5>
            <p class="text-white-50 small mb-4">Configure automated email alerts for low wallet balances.</p>
            
            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="text-white-50 small mb-2">Low Balance Threshold (₦)</label>
                    <input type="number" step="0.01" name="low_balance_threshold" class="form-control text-white rounded-3" value="<?php echo e(\App\Models\SystemSetting::get('low_balance_threshold', 500)); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    <small class="text-info mt-2 d-block">Users will receive an email when their balance drops below this amount. Throttled to once per week.</small>
                </div>
            </div>

            <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fa fa-floppy-disk mr-2"></i>Save Notification Settings</button>
        </form>
    </div>
</div>


<div class="s-panel" id="tab-pricing">
    <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
        <h5 class="text-white mb-1 fw-bold">Verification Pricing</h5>
        <p class="text-white-50 small mb-4">Set prices (₦) for all identity verification services.</p>
        <form id="pricingForm">
            <?php echo csrf_field(); ?>
            <div class="row">
                <?php $vp = $verifyPrices; ?>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">NIN by NIN (₦)</label>
                    <input type="number" name="nin_by_nin_price" class="form-control text-white rounded-3" value="<?php echo e($vp->nin_by_nin_price ?? 150); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">NIN by Phone (₦)</label>
                    <input type="number" name="nin_by_number_price" class="form-control text-white rounded-3" value="<?php echo e($vp->nin_by_number_price ?? 150); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">NIN by Demography (₦)</label>
                    <input type="number" name="nin_by_demography_price" class="form-control text-white rounded-3" value="<?php echo e($vp->nin_by_demography_price ?? 150); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">BVN by BVN (₦)</label>
                    <input type="number" name="bvn_by_bvn" class="form-control text-white rounded-3" value="<?php echo e($vp->bvn_by_bvn ?? 100); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">BVN by Phone (₦)</label>
                    <input type="number" name="bvn_by_number" class="form-control text-white rounded-3" value="<?php echo e($vp->bvn_by_number ?? 120); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Verify by Tracking ID (₦)</label>
                    <input type="number" name="verify_by_tracking_id" class="form-control text-white rounded-3" value="<?php echo e($vp->verify_by_tracking_id ?? 200); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Validation (₦)</label>
                    <input type="number" name="validation_price" class="form-control text-white rounded-3" value="<?php echo e($vp->validation_price ?? 700); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">IPE Clearance (₦)</label>
                    <input type="number" name="ipe_clearance_price" class="form-control text-white rounded-3" value="<?php echo e($vp->ipe_clearance_price ?? 400); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Personalization (₦)</label>
                    <input type="number" name="personalization_price" class="form-control text-white rounded-3" value="<?php echo e($vp->personalization_price ?? 100); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
            </div>
            <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fa fa-floppy-disk mr-2"></i>Save Pricing</button>
        </form>

        <hr style="border-color: rgba(255,255,255,0.07);" class="my-4">

        <h5 class="text-white mb-1 fw-bold">Extended Services Pricing</h5>
        <p class="text-white-50 small mb-4">Manage pricing for AI Legal Hub and other advanced modules.</p>
        <form id="systemPricingForm">
            <?php echo csrf_field(); ?>
            <div class="row">
                <?php
                    $pricing = [
                        'cac_verification_price' => 'CAC Verification',
                        'tin_verification_price' => 'TIN Verification',
                        'voters_card_price' => 'Voters Card (PVC)',
                        'passport_verification_price' => 'Passport Verification',
                        'nin_modification_price' => 'NIN Modification',
                        'nin_face_price' => 'NIN Face Verification',
                        'credit_report_price' => 'Credit Report',
                        'combined_verify_price' => 'Combined Verification',
                        'bvn_match_price' => 'BVN Match',
                        'address_verify_price' => 'Address Verification',
                        'drivers_license_price' => 'Drivers License Verify',
                        'biometric_verify_price' => 'Biometric Verification',
                        'plate_number_price' => 'Plate Number Verification',
                        'stamp_duty_price' => 'Stamp Duty Verification',
                        'legal_hub_base_price' => 'Legal Hub Base (Custom Docs)',
                        'nda_generation_price' => 'NDA Drafting',
                        'sales_agreement_price' => 'Sales Agreement Drafting',
                        'waec_result_price' => 'WAEC Result Checker',
                        'waec_reg_pin_price' => 'WAEC Registration PIN',
                        'motor_insurance_price' => 'Motor Insurance',
                        'agency_banking_price' => 'Agency Banking Fee',
                        'virtual_card_price' => 'Virtual Card Issuance',
                        'virtual_card_creation_fee_ngn' => 'Virtual Card Creation Fee',
                        'virtual_card_funding_fee_ngn' => 'Virtual Card Funding Fee',
                        'virtual_card_fx_rate_usd' => 'Virtual Card FX Rate (USD)',
                        'virtual_card_fx_rate_gbp' => 'Virtual Card FX Rate (GBP)',
                        'virtual_card_fx_rate_eur' => 'Virtual Card FX Rate (EUR)',
                        'fx_exchange_price' => 'FX Exchange Fee',
                        'invoicing_price' => 'Invoicing Service',
                        'logistics_price' => 'Post Office / Logistics',
                        'ticketing_price' => 'Ticketing Service',
                        'auction_price' => 'Auction Participation',
                    ];
                ?>
                <?php $__currentLoopData = $pricing; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="col-md-4 mb-4">
                        <label class="text-white-50 small mb-2"><?php echo e($label); ?> (₦)</label>
                        <input type="number" name="pricing[<?php echo e($key); ?>]" class="form-control text-white rounded-3" value="<?php echo e(\App\Models\SystemSetting::get($key, 500)); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fa-solid fa-floppy-disk mr-2"></i>Save Extended Pricing</button>
        </form>
    </div>
</div>


<div class="s-panel" id="tab-banking">
    <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
        <h5 class="text-white mb-1 fw-bold">Manual Funding Bank Details</h5>
        <p class="text-white-50 small mb-4">The bank account users are instructed to send money to for manual wallet funding.</p>
        <form id="bankForm">
            <?php echo csrf_field(); ?>
            <?php $mf = $manualFunding; ?>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Bank / Provider Name</label>
                    <input type="text" name="bank_name" class="form-control text-white rounded-3" value="<?php echo e($mf->bank_name ?? ''); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Account Number</label>
                    <input type="text" name="account_number" class="form-control text-white rounded-3" value="<?php echo e($mf->account_number ?? ''); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Account Name</label>
                    <input type="text" name="account_name" class="form-control text-white rounded-3" value="<?php echo e($mf->account_name ?? ''); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
            </div>
            <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fa fa-floppy-disk mr-2"></i>Save Bank Details</button>
        </form>
    </div>
</div>


<div class="s-panel" id="tab-api-settings">
    <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
        <h5 class="text-white mb-1 fw-bold">API Provider Selection</h5>
        <p class="text-white-50 small mb-4">Select which provider to use for each service type.</p>
        <form id="apiSettingsForm">
            <?php echo csrf_field(); ?>
            <?php $as = $apiSettings; ?>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="text-white-50 small mb-2">NIN Provider</label>
                    <input type="text" name="nin_search_type" class="form-control text-white rounded-3" value="<?php echo e($as->nin_search_type ?? ''); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);" placeholder="e.g. Dataverify">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="text-white-50 small mb-2">BVN Provider</label>
                    <input type="text" name="bvn_search_type" class="form-control text-white rounded-3" value="<?php echo e($as->bvn_search_type ?? ''); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);" placeholder="e.g. Dataverify">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="text-white-50 small mb-2">Data API Provider</label>
                    <input type="text" name="data_api_type" class="form-control text-white rounded-3" value="<?php echo e($as->data_api_type ?? ''); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);" placeholder="e.g. bilalsadasub">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="text-white-50 small mb-2">Airtime API Provider</label>
                    <input type="text" name="airtime_api_type" class="form-control text-white rounded-3" value="<?php echo e($as->airtime_api_type ?? ''); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);" placeholder="e.g. Sadeeqdata">
                </div>
            </div>
            <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fa fa-floppy-disk mr-2"></i>Save API Config</button>
        </form>
    </div>
</div>


<div class="s-panel" id="tab-api-keys">
    <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
        <h5 class="text-white mb-1 fw-bold"><i class="fa fa-triangle-exclamation text-warning mr-2"></i>API Keys & Secrets</h5>
        <p class="text-white-50 small mb-4">These are sensitive credentials. Keep them secure and never share them publicly.</p>
        <div class="mb-4 p-3 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
            <details>
                <summary class="text-white fw-bold" style="cursor: pointer;">Setup Guide: Endpoints, Webhooks, and Examples</summary>
                <div class="mt-3 text-white-50 small">
                    <div class="mb-3">
                        <div class="text-white fw-bold mb-1">Dataverify</div>
                        <div class="mb-2">Fill in your Dataverify API Key, then set the endpoints below. Recommended defaults are shown in the placeholders.</div>
                        <ul class="mb-2">
                            <li><span class="text-white">NIN Endpoint</span>: used for NIN search (payload: <span class="font-monospace">api_key</span> + <span class="font-monospace">nin</span>)</li>
                            <li><span class="text-white">Phone Endpoint</span>: used for NIN-by-phone (payload: <span class="font-monospace">api_key</span> + <span class="font-monospace">phone</span>)</li>
                            <li><span class="text-white">Tracking ID Endpoint</span>: used for tracking-id lookup (payload: <span class="font-monospace">api_key</span> + <span class="font-monospace">tracking_id</span>)</li>
                            <li><span class="text-white">Premium Slip Endpoint</span>: used when users request premium slips</li>
                            <li><span class="text-white">Standard/Regular/vNIN Slip Endpoints</span>: used when users request those slip types</li>
                        </ul>
                        <div class="mb-2">
                            <div class="text-white fw-bold mb-1">Premium Slip endpoint options</div>
                            <ul class="mb-2">
                                <li><span class="font-monospace">.../nin_slips/nin_premium</span> (Premium slip by NIN)</li>
                                <li><span class="font-monospace">.../nin_slips/nin_premium_phone</span> (Premium slip by Phone)</li>
                                <li><span class="font-monospace">.../nin_slips/nin_premium_demo.php</span> (Premium slip by Demographic Details)</li>
                            </ul>
                            <div class="mb-2">If you use the demographic premium demo endpoint, the system sends:</div>
                            <div class="font-monospace">api_key, firstname, lastname, dob (DD-MM-YYYY), gender (m/f or male/female)</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-white fw-bold mb-1">Payment Webhooks</div>
                        <div class="mb-2">Set your provider webhook URL(s) to these routes (use your production domain):</div>
                        <ul class="mb-2">
                            <li>Payvessel: <span class="font-monospace"><?php echo e(url('/webhooks/payvessel')); ?></span> or <span class="font-monospace"><?php echo e(url('/payvessel_webhook.php')); ?></span></li>
                            <li>Palmpay: <span class="font-monospace"><?php echo e(url('/webhooks/palmpay')); ?></span> or <span class="font-monospace"><?php echo e(url('/palmpay_webhook.php')); ?></span></li>
                            <li>Paystack: <span class="font-monospace"><?php echo e(url('/webhooks/paystack')); ?></span></li>
                            <li>Monnify: <span class="font-monospace"><?php echo e(url('/webhooks/monnify')); ?></span></li>
                            <li>Flutterwave: <span class="font-monospace"><?php echo e(url('/webhooks/flutterwave')); ?></span></li>
                        </ul>
                        <div>After saving keys/endpoints here, test webhooks from the provider dashboard to confirm a successful delivery.</div>
                    </div>

                    <div class="mb-0">
                        <div class="text-white fw-bold mb-1">Monnify / Payvessel Endpoints</div>
                        <div>These endpoint fields are optional. If left blank, the application will use its internal defaults. Only override them if your provider gave you a different base URL.</div>
                    </div>
                </div>
            </details>
        </div>
        <form id="apiKeysForm">
            <?php echo csrf_field(); ?>
            <?php $ac = $apiCenter; ?>
            <div class="row">
                <div class="col-12 mb-3">
                    <p class="text-white-50 text-uppercase small font-weight-bold mb-2" style="letter-spacing: 1px;">Dataverify</p>
                </div>
                <div class="col-12 mb-4">
                    <label class="text-white-50 small mb-2">API Key</label>
                    <input type="text" name="dataverify_api_key" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->dataverify_api_key ?? ''); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">NIN Endpoint</label>
                    <input type="text" name="dataverify_endpoint_nin" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->dataverify_endpoint_nin ?? 'https://dataverify.com.ng/developers/nin_slips/nin_premium'); ?>" placeholder="https://dataverify.com.ng/developers/nin_slips/nin_premium" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Phone Endpoint</label>
                    <input type="text" name="dataverify_endpoint_phone" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->dataverify_endpoint_phone ?? 'https://dataverify.com.ng/developers/nin_slips/nin_premium_phone'); ?>" placeholder="https://dataverify.com.ng/developers/nin_slips/nin_premium_phone" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Tracking ID Endpoint</label>
                    <input type="text" name="dataverify_endpoint_tid" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->dataverify_endpoint_tid ?? 'https://dataverify.com.ng/developers/nin_api/fetch_by_tid'); ?>" placeholder="https://dataverify.com.ng/developers/nin_api/fetch_by_tid" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Premium Slip Endpoint</label>
                    <input type="text" name="dataverify_endpoint_premium_slip" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->dataverify_endpoint_premium_slip ?? 'https://dataverify.com.ng/developers/nin_slips/nin_premium'); ?>" placeholder="https://dataverify.com.ng/developers/nin_slips/nin_premium" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Premium Slip (Phone) Endpoint</label>
                    <input type="text" name="dataverify_endpoint_premium_slip_phone" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->dataverify_endpoint_premium_slip_phone ?? 'https://dataverify.com.ng/developers/nin_slips/nin_premium_phone'); ?>" placeholder="https://dataverify.com.ng/developers/nin_slips/nin_premium_phone" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Standard Slip Endpoint</label>
                    <input type="text" name="dataverify_endpoint_standard_slip" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->dataverify_endpoint_standard_slip ?? 'https://dataverify.com.ng/developers/nin_slips/nin_standard'); ?>" placeholder="https://dataverify.com.ng/developers/nin_slips/nin_standard" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Regular Slip Endpoint</label>
                    <input type="text" name="dataverify_endpoint_regular_slip" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->dataverify_endpoint_regular_slip ?? 'https://dataverify.com.ng/developers/nin_slips/nin_regular'); ?>" placeholder="https://dataverify.com.ng/developers/nin_slips/nin_regular" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">vNIN Slip Endpoint</label>
                    <input type="text" name="dataverify_endpoint_vnin_slip" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->dataverify_endpoint_vnin_slip ?? ''); ?>" placeholder="Paste DataVerify vNIN slip endpoint" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-12 mb-4">
                    <div class="custom-control custom-switch">
                        <?php $dvPhoneToggle = \App\Models\SystemSetting::get('dataverify_use_phone_slip_for_phone_mode', 'false') === 'true'; ?>
                        <input type="checkbox" class="custom-control-input" id="dvPhoneSlipToggle" name="dataverify_use_phone_slip_for_phone_mode" value="1" <?php echo e($dvPhoneToggle ? 'checked' : ''); ?>>
                        <label class="custom-control-label text-white-50" for="dvPhoneSlipToggle">Use phone-based slip endpoint for phone verifications</label>
                    </div>
                    <small class="text-white-50">If enabled and a phone slip endpoint is configured, the system will request the premium slip using the subject’s phone number where appropriate.</small>
                </div>

                <div class="col-12 mb-3">
                    <hr style="border-color: rgba(255,255,255,0.07);">
                    <p class="text-white-50 text-uppercase small font-weight-bold mb-2" style="letter-spacing: 1px;">Paystack</p>
                </div>
                <div class="col-md-6 mb-4">
                    <label class="text-white-50 small mb-2">Public Key</label>
                    <input type="text" name="paystack_public_key" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->paystack_public_key ?? ''); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="text-white-50 small mb-2">Secret Key</label>
                    <input type="password" name="paystack_secret_key" autocomplete="new-password" class="form-control text-white rounded-3 font-monospace" value="" placeholder="Leave blank to keep existing" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>

                <div class="col-12 mb-3">
                    <hr style="border-color: rgba(255,255,255,0.07);">
                    <p class="text-white-50 text-uppercase small font-weight-bold mb-2" style="letter-spacing: 1px;">Flutterwave</p>
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Public Key</label>
                    <input type="text" name="flutterwave_public_key" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->flutterwave_public_key ?? ''); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Secret Key</label>
                    <input type="password" name="flutterwave_secret_key" autocomplete="new-password" class="form-control text-white rounded-3 font-monospace" value="" placeholder="Leave blank to keep existing" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Encryption Key</label>
                    <input type="password" name="flutterwave_encryption_key" autocomplete="new-password" class="form-control text-white rounded-3 font-monospace" value="" placeholder="Leave blank to keep existing" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>

                <div class="col-12 mb-3">
                    <hr style="border-color: rgba(255,255,255,0.07);">
                    <p class="text-white-50 text-uppercase small font-weight-bold mb-2" style="letter-spacing: 1px;">Vuvaa</p>
                </div>
                <div class="col-md-6 mb-4">
                    <label class="text-white-50 small mb-2">Username</label>
                    <input type="text" name="vuvaa_username" class="form-control text-white rounded-3 font-monospace" value="<?php echo e(\App\Models\SystemSetting::get('vuvaa_username', '')); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);" placeholder="Vuvaa username">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="text-white-50 small mb-2">Password</label>
                    <input type="password" name="vuvaa_password" autocomplete="new-password" class="form-control text-white rounded-3 font-monospace" value="" placeholder="Leave blank to keep existing" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="text-white-50 small mb-2">Encryption Key</label>
                    <input type="password" name="vuvaa_encryption_key" autocomplete="new-password" class="form-control text-white rounded-3 font-monospace" value="" placeholder="Leave blank to keep existing" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="text-white-50 small mb-2">Encryption IV</label>
                    <input type="password" name="vuvaa_encryption_iv" autocomplete="new-password" class="form-control text-white rounded-3 font-monospace" value="" placeholder="Leave blank to keep existing" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    <small class="text-white-50">IV is typically 16 characters for AES.</small>
                </div>

                <div class="col-12 mb-3">
                    <hr style="border-color: rgba(255,255,255,0.07);">
                    <p class="text-white-50 text-uppercase small font-weight-bold mb-2" style="letter-spacing: 1px;">Payvessel</p>
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">API Key</label>
                    <input type="text" name="payvessel_api_key" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->payvessel_api_key ?? ''); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Secret Key</label>
                    <input type="password" name="payvessel_secret_key" autocomplete="new-password" class="form-control text-white rounded-3 font-monospace" value="" placeholder="Leave blank to keep existing" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Business ID</label>
                    <input type="text" name="payvessel_businessid" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->payvessel_businessid ?? ''); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-12 mb-4">
                    <label class="text-white-50 small mb-2">Reserved Account Endpoint (optional)</label>
                    <input type="text" name="payvessel_endpoint" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->payvessel_endpoint ?? ''); ?>" placeholder="https://api.payvessel.com/api/external/request/customerReservedAccount/" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>

                <div class="col-12 mb-3">
                    <hr style="border-color: rgba(255,255,255,0.07);">
                    <p class="text-white-50 text-uppercase small font-weight-bold mb-2" style="letter-spacing: 1px;">Monnify</p>
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">API Key</label>
                    <input type="text" name="monnify_api_key" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->monnify_api_key ?? ''); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Secret Key</label>
                    <input type="password" name="monnify_secret_key" autocomplete="new-password" class="form-control text-white rounded-3 font-monospace" value="" placeholder="Leave blank to keep existing" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Contract Code</label>
                    <input type="text" name="monnify_contract_code" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->monnify_contract_code ?? ''); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="text-white-50 small mb-2">Auth Endpoint (optional)</label>
                    <input type="text" name="monnify_endpoint_auth" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->monnify_endpoint_auth ?? ''); ?>" placeholder="https://api.monnify.com/api/v1/auth/login" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="text-white-50 small mb-2">Reserved Accounts Endpoint (optional)</label>
                    <input type="text" name="monnify_endpoint_reserve" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->monnify_endpoint_reserve ?? ''); ?>" placeholder="https://api.monnify.com/api/v2/bank-transfer/reserved-accounts" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>

                <div class="col-12 mb-3">
                    <hr style="border-color: rgba(255,255,255,0.07);">
                    <p class="text-white-50 text-uppercase small font-weight-bold mb-2" style="letter-spacing: 1px;">Fuwa.NG Extended Services</p>
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Notary Service Key</label>
                    <input type="text" name="nexus_notary_key" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->nexus_notary_key ?? ''); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Logistics Service Key</label>
                    <input type="text" name="nexus_logistics_key" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->nexus_logistics_key ?? ''); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Master Secret</label>
                    <input type="password" name="nexus_api_secret" autocomplete="new-password" class="form-control text-white rounded-3 font-monospace" value="" placeholder="Leave blank to keep existing" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>

                <div class="col-12 mb-3">
                    <hr style="border-color: rgba(255,255,255,0.07);">
                    <p class="text-white-50 text-uppercase small font-weight-bold mb-2" style="letter-spacing: 1px;">Robosttech API</p>
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">API Key</label>
                    <input type="text" name="robosttech_api_key" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->robosttech_api_key ?? ''); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">NIN Endpoint</label>
                    <input type="text" name="robosttech_endpoint_nin" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->robosttech_endpoint_nin ?? 'https://robosttech.com/api'); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Validation Endpoint</label>
                    <input type="text" name="robosttech_endpoint_validation" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->robosttech_endpoint_validation ?? 'https://robosttech.com/api'); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Clearance Endpoint</label>
                    <input type="text" name="robosttech_endpoint_clearance" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->robosttech_endpoint_clearance ?? 'https://robosttech.com/api/clearance'); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Clearance Status Endpoint</label>
                    <input type="text" name="robosttech_endpoint_clearance_status" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->robosttech_endpoint_clearance_status ?? 'https://robosttech.com/api/clearance_status'); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Personalization Endpoint</label>
                    <input type="text" name="robosttech_endpoint_personalization" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->robosttech_endpoint_personalization ?? 'https://robosttech.com/api'); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>

                <div class="col-12 mb-3">
                    <hr style="border-color: rgba(255,255,255,0.07);">
                    <p class="text-white-50 text-uppercase small font-weight-bold mb-2" style="letter-spacing: 1px;">Google Gemini AI</p>
                </div>
                <div class="col-md-8 mb-4">
                    <label class="text-white-50 small mb-2">Gemini API Key</label>
                    <input type="text" name="gemini_api_key" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->gemini_api_key ?? ''); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);" placeholder="Enter your Google Gemini API Key">
                </div>
                
                <div class="col-12 mb-3">
                    <hr style="border-color: rgba(255,255,255,0.07);">
                    <p class="text-white-50 text-uppercase small font-weight-bold mb-2" style="letter-spacing: 1px;">Clubkonnect (VTU Hub)</p>
                </div>
                <div class="col-md-6 mb-4">
                    <label class="text-white-50 small mb-2">User ID</label>
                    <input type="text" name="clubkonnect_userid" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->clubkonnect_userid ?? ''); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);" placeholder="e.g. CK100287809">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="text-white-50 small mb-2">API Key</label>
                    <input type="password" name="clubkonnect_apikey" autocomplete="new-password" class="form-control text-white rounded-3 font-monospace" value="" placeholder="Leave blank to keep existing" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>

                <div class="col-12 mb-3">
                    <hr style="border-color: rgba(255,255,255,0.07);">
                    <p class="text-white-50 text-uppercase small font-weight-bold mb-2" style="letter-spacing: 1px;">SMS AI</p>
                </div>
                <div class="col-md-8 mb-4">
                    <label class="text-white-50 small mb-2">SMS AI Key</label>
                    <input type="text" name="sms_ai_key" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->sms_ai_key ?? ''); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);" placeholder="Paste your SMS AI key">
                </div>
                <div class="col-md-8 mb-4">
                    <label class="text-white-50 small mb-2">SMS AI Endpoint</label>
                    <input type="text" name="sms_ai_endpoint" class="form-control text-white rounded-3 font-monospace" value="<?php echo e($ac->sms_ai_endpoint ?? ''); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);" placeholder="https://example.com/api/sms/send">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Sender ID (optional)</label>
                    <input type="text" name="sms_ai_sender" class="form-control text-white rounded-3" value="<?php echo e($ac->sms_ai_sender ?? ''); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);" placeholder="FUWA">
                </div>
            </div>
            <button type="submit" class="btn btn-warning rounded-pill px-4 text-dark font-weight-bold"><i class="fa fa-key mr-2"></i>Save API Keys</button>
        </form>
    </div>
</div>


<div class="s-panel" id="tab-theme">
    <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
        <h5 class="text-white mb-1 fw-bold">Website Theme</h5>
        <p class="text-white-50 small mb-4">Update platform colors globally.</p>

        <form id="themeForm">
            <?php echo csrf_field(); ?>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Primary</label>
                    <input type="color" name="theme_primary" class="form-control p-1" value="<?php echo e(\App\Models\SystemSetting::get('theme_primary', '#3b82f6')); ?>" style="height: 46px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Primary Hover</label>
                    <input type="color" name="theme_primary_hover" class="form-control p-1" value="<?php echo e(\App\Models\SystemSetting::get('theme_primary_hover', '#2563eb')); ?>" style="height: 46px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Accent 1</label>
                    <input type="color" name="theme_accent_1" class="form-control p-1" value="<?php echo e(\App\Models\SystemSetting::get('theme_accent_1', '#10b981')); ?>" style="height: 46px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Accent 2</label>
                    <input type="color" name="theme_accent_2" class="form-control p-1" value="<?php echo e(\App\Models\SystemSetting::get('theme_accent_2', '#8b5cf6')); ?>" style="height: 46px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
                <div class="col-md-4 mb-4">
                    <label class="text-white-50 small mb-2">Accent 3</label>
                    <input type="color" name="theme_accent_3" class="form-control p-1" value="<?php echo e(\App\Models\SystemSetting::get('theme_accent_3', '#f59e0b')); ?>" style="height: 46px; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                </div>
            </div>

            <button type="submit" class="btn btn-primary rounded-pill px-4">
                <i class="fa fa-floppy-disk mr-2"></i>Save Theme
            </button>
        </form>
    </div>
</div>


<div class="s-panel" id="tab-notary">
    <div class="row">
        <div class="col-md-7">
            <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
                <h5 class="text-white mb-1 fw-bold">Document Pricing & Logic</h5>
                <p class="text-white-50 small mb-4">Manage document types and whether they require official court stamping.</p>
                
                <div class="table-responsive">
                    <table class="table table-borderless text-white small">
                        <thead>
                            <tr class="text-white-50 border-bottom border-white-10">
                                <th>Type</th>
                                <th>Price (₦)</th>
                                <th>Court?</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $notaryDocs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="border-bottom border-white-10">
                                <td class="py-3 fw-bold"><?php echo e(ucwords(str_replace('_', ' ', $doc->document_type))); ?></td>
                                <td class="py-3 text-primary">₦<?php echo e(number_format($doc->price, 2)); ?></td>
                                <td class="py-3">
                                    <span class="badge <?php echo e($doc->requires_court_stamp ? 'badge-warning' : 'badge-info'); ?>">
                                        <?php echo e($doc->requires_court_stamp ? 'Required' : 'None'); ?>

                                    </span>
                                </td>
                                <td class="py-3">
                                    <button class="btn btn-sm btn-outline-light rounded-pill px-3" onclick='editNotaryDoc(<?php echo json_encode($doc, 15, 512) ?>)'>Edit</button>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-white-50 italic">No document types configured.</td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td colspan="4" class="py-3 text-center">
                                    <button class="btn btn-sm btn-primary rounded-pill px-4" onclick="showAddDocModal()"><i class="fa fa-plus mr-2"></i>Add New Doc Type</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
                <h5 class="text-white mb-1 fw-bold">Website & Brand Identity</h5>
                <p class="text-white-50 small mb-4">Core details used across the platform and communications.</p>
                <form id="brandingForm" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <div class="form-group mb-3">
                        <label class="text-white-50 small mb-2">Website Name</label>
                        <input type="text" name="site_name" class="form-control text-white rounded-3" value="<?php echo e(\App\Models\SystemSetting::get('site_name', config('app.name'))); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                    
                    <div class="row">
                        <div class="col-12 col-md-6 form-group mb-3">
                            <label class="text-white-50 small mb-2">Contact Email</label>
                            <input type="email" name="contact_email" class="form-control text-white rounded-3" value="<?php echo e(\App\Models\SystemSetting::get('contact_email', 'support@fuwa.ng')); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                        </div>
                        <div class="col-12 col-md-6 form-group mb-3">
                            <label class="text-white-50 small mb-2">Contact Phone</label>
                            <input type="text" name="contact_phone" class="form-control text-white rounded-3" value="<?php echo e(\App\Models\SystemSetting::get('contact_phone', '+234 800 000 0000')); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                        </div>
                    </div>
                    
                    <div class="form-group mb-4">
                        <label class="text-white-50 small mb-2">Contact Address</label>
                        <textarea name="contact_address" class="form-control text-white rounded-3" rows="2" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);"><?php echo e(\App\Models\SystemSetting::get('contact_address', 'Lagos, Nigeria')); ?></textarea>
                    </div>

                    <hr style="border-color: rgba(255,255,255,0.07);" class="mb-4">

                    <div class="row mb-4">
                        <div class="col-12 col-md-6 mb-3 mb-md-0">
                            <label class="text-white-50 small mb-2">Site Logo</label>
                            <div class="custom-file-upload p-3 rounded-3 text-center" style="border: 2px dashed rgba(255,255,255,0.1); background: rgba(255,255,255,0.02);">
                                <?php $logoUrl = \App\Models\SystemSetting::get('site_logo_url'); ?>
                                <?php if($logoUrl): ?>
                                    <img src="<?php echo e($logoUrl); ?>" class="mb-2 d-block mx-auto" style="max-height: 40px;">
                                <?php endif; ?>
                                <input type="file" name="site_logo" accept="image/*" class="form-control-file text-white-50 small mt-2">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="text-white-50 small mb-2">Favicon</label>
                            <div class="custom-file-upload p-3 rounded-3 text-center" style="border: 2px dashed rgba(255,255,255,0.1); background: rgba(255,255,255,0.02);">
                                <?php $favUrl = \App\Models\SystemSetting::get('site_favicon_url'); ?>
                                <?php if($favUrl): ?>
                                    <img src="<?php echo e($favUrl); ?>" class="mb-2 d-block mx-auto" style="max-height: 40px; border-radius: 8px;">
                                <?php endif; ?>
                                <input type="file" name="site_favicon" accept="image/*" class="form-control-file text-white-50 small mt-2">
                            </div>
                        </div>
                    </div>

                    <hr style="border-color: rgba(255,255,255,0.07);" class="mb-4">
                    <h6 class="text-white mb-3">Global SEO Settings</h6>

                    <div class="form-group mb-3">
                        <label class="text-white-50 small mb-2">SEO Meta Title</label>
                        <input type="text" name="seo_title" class="form-control text-white rounded-3" value="<?php echo e(\App\Models\SystemSetting::get('seo_title', '')); ?>" placeholder="Default title for search engines" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="text-white-50 small mb-2">SEO Meta Description</label>
                        <textarea name="seo_description" class="form-control text-white rounded-3" rows="2" placeholder="Brief description of your site for search results" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);"><?php echo e(\App\Models\SystemSetting::get('seo_description', '')); ?></textarea>
                    </div>

                    <div class="form-group mb-4">
                        <label class="text-white-50 small mb-2">SEO Meta Keywords</label>
                        <input type="text" name="seo_keywords" class="form-control text-white rounded-3" value="<?php echo e(\App\Models\SystemSetting::get('seo_keywords', '')); ?>" placeholder="e.g. verification, identity, legal hub" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    </div>

                    <hr style="border-color: rgba(255,255,255,0.07);" class="mb-4">
                    <h6 class="text-white mb-3">Notary / Legal Document Signatures</h6>

                    <div class="form-group mb-4">
                        <label class="text-white-50 small mb-2">Organization Stamp (PNG)</label>
                        <div class="custom-file-upload p-3 rounded-3 text-center" style="border: 2px dashed rgba(255,255,255,0.1); background: rgba(255,255,255,0.02);">
                            <?php 
                                $stampUrl = \App\Models\SystemSetting::get('default_stamp_url');
                                $sigPrefix = \App\Models\SystemSetting::get('default_signature_prefix', 'Fuwa.NG Legal');
                            ?>
                            <?php if($stampUrl): ?>
                                <img src="<?php echo e($stampUrl); ?>" class="mb-2 d-block mx-auto" style="max-height: 80px;">
                            <?php endif; ?>
                            <input type="file" name="stamp" accept="image/png" class="form-control-file text-white-50 small">
                        </div>
                    </div>
                    <div class="form-group mb-4">
                        <label class="text-white-50 small mb-2">Signature Text Representation</label>
                        <input type="text" name="signature" class="form-control text-white rounded-3" value="<?php echo e($sigPrefix); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block rounded-pill py-2"><i class="fa fa-upload mr-2"></i>Update Website Identity</button>
                </form>
            </div>
        </div>

        <?php if($canManageSecurity): ?>
        <div class="col-12 mt-4">
            <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
                <h6 class="text-white font-weight-bold mb-3"><i class="fa fa-vault text-primary mr-2"></i> Admin Self-Funding Limit</h6>
                <p class="text-white-50 small mb-4">Configure the maximum amount allowed per internal self-funding transaction for Super Admins.</p>
                
                <form id="adminSelfFundingLimitForm">
                    <?php echo csrf_field(); ?>
                    <div class="row align-items-end">
                        <div class="col-md-6 form-group mb-0">
                            <label class="text-white-50 small mb-2">Max Limit per Transaction (₦)</label>
                            <input type="number" step="0.01" name="self_funding_limit" class="form-control text-white rounded-3" value="<?php echo e(\App\Models\SystemSetting::get('self_funding_limit', 10000000)); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fa fa-floppy-disk mr-2"></i>Update Limit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>


<div class="s-panel" id="tab-security">
    <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div>
                <h5 class="text-white mb-1 fw-bold">Webhook Security</h5>
                <p class="text-white-50 small mb-0">Signature validation is mandatory. Manage rotation and allowlist records here.</p>
            </div>
            <?php if(!$canManageSecurity): ?>
                <span class="badge badge-warning text-dark">Read-only</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7 col-12 mb-4">
            <div class="card border-0 rounded-4 p-4 h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
                <h6 class="text-white font-weight-bold mb-2">VerifyMe IP Allowlist</h6>
                <p class="text-white-50 small mb-3">Store known VerifyMe IPs for operational visibility and incident response.</p>

                <?php
                    $ipsList = collect(preg_split('/[\s,]+/', (string)($verifymeWebhookIps ?? '')) ?: [])
                        ->map(fn ($v) => trim((string)$v))
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();
                ?>

                <form id="verifymeIpsForm">
                    <?php echo csrf_field(); ?>
                    <div class="form-group mb-3">
                        <label class="text-white-50 small mb-2">Add IP (IPv4/IPv6)</label>
                        <div class="d-flex" style="gap: 10px;">
                            <input id="verifymeIpInput" type="text" class="form-control text-white rounded-3 font-monospace" placeholder="e.g. 3.255.23.38 or 2001:db8::1" <?php echo e($canManageSecurity ? '' : 'disabled'); ?> style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                            <button id="verifymeAddIpBtn" type="button" class="btn btn-outline-primary rounded-pill px-4" <?php echo e($canManageSecurity ? '' : 'disabled'); ?>>Add</button>
                        </div>
                        <div id="verifymeIpHint" class="text-danger small mt-2" style="display:none;"></div>
                    </div>

                    <div class="mb-3">
                        <label class="text-white-50 small mb-2">Current Allowlist</label>
                        <div id="verifymeIpChips" class="d-flex flex-wrap" style="gap: 10px;">
                            <?php $__empty_1 = true; $__currentLoopData = $ipsList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <span class="badge badge-pill badge-dark font-monospace px-3 py-2" data-ip="<?php echo e($ip); ?>" style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.08);">
                                    <?php echo e($ip); ?>

                                    <?php if($canManageSecurity): ?>
                                        <button type="button" class="ml-2 text-white-50" data-remove-ip="<?php echo e($ip); ?>" style="background: transparent; border: 0; padding: 0;">&times;</button>
                                    <?php endif; ?>
                                </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <span class="text-white-50 small">No IPs configured.</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <textarea id="verifymeIpsField" name="verifyme_webhook_ips" class="d-none"><?php echo e(implode("\n", $ipsList)); ?></textarea>

                    <button type="submit" class="btn btn-primary rounded-pill px-4" <?php echo e($canManageSecurity ? '' : 'disabled'); ?>>
                        <i class="fa fa-floppy-disk mr-2"></i>Save Allowlist
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-5 col-12 mb-4">
            <div class="card border-0 rounded-4 p-4 h-100" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
                <h6 class="text-white font-weight-bold mb-2">VerifyMe Webhook Secret</h6>
                <p class="text-white-50 small mb-3">
                    Status:
                    <?php if($verifymeWebhookSecretSet ?? false): ?>
                        <span class="text-success font-weight-bold">Configured</span>
                    <?php else: ?>
                        <span class="text-danger font-weight-bold">Not configured</span>
                    <?php endif; ?>
                    <?php if(!empty($verifymeWebhookSecretUpdatedAt)): ?>
                        <span class="text-white-50">• Updated <?php echo e(\Illuminate\Support\Carbon::parse($verifymeWebhookSecretUpdatedAt)->diffForHumans()); ?></span>
                    <?php endif; ?>
                </p>

                <form id="verifymeSecretForm">
                    <?php echo csrf_field(); ?>
                    <div class="form-group mb-3">
                        <label class="text-white-50 small mb-2">New Secret</label>
                        <input id="verifymeSecretInput" type="password" name="verifyme_webhook_secret" autocomplete="new-password" class="form-control text-white rounded-3 font-monospace" placeholder="Paste or generate a new secret" <?php echo e($canManageSecurity ? '' : 'disabled'); ?> style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                        <div class="text-white-50 small mt-2">Rotating secrets requires updating the upstream webhook sender.</div>
                    </div>

                    <div class="d-flex" style="gap: 10px;">
                        <button id="verifymeGenerateSecretBtn" type="button" class="btn btn-outline-warning rounded-pill px-4" <?php echo e($canManageSecurity ? '' : 'disabled'); ?>>
                            <i class="fa fa-dice mr-2"></i>Generate
                        </button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4" <?php echo e($canManageSecurity ? '' : 'disabled'); ?>>
                            <i class="fa fa-rotate mr-2"></i>Rotate Secret
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
                <h6 class="text-white font-weight-bold mb-3">Security Audit Log</h6>
                <div class="table-responsive">
                    <table class="table table-borderless text-white small mb-0">
                        <thead>
                            <tr class="text-white-50 border-bottom border-white-10">
                                <th>Time</th>
                                <th>Admin</th>
                                <th>Action</th>
                                <th>Meta</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = ($securityAuditLogs ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="border-bottom border-white-10">
                                    <td class="py-3 text-white-50"><?php echo e($log->created_at->format('Y-m-d H:i:s')); ?></td>
                                    <td class="py-3"><?php echo e($log->admin?->email ?? $log->admin?->username ?? '—'); ?></td>
                                    <td class="py-3 font-monospace text-primary"><?php echo e($log->action); ?></td>
                                    <td class="py-3 text-white-50"><?php echo e($log->meta ? json_encode($log->meta) : '—'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="4" class="text-center py-4 text-white-50">No security events yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="s-panel" id="tab-gateways">
    <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
        <h5 class="text-white mb-1 fw-bold">Payment Provider Controls</h5>
        <p class="text-white-50 small mb-4">Toggle visibility of payment gateways for users. Inactive providers will not appear in the "Fund Wallet" modal.</p>
        <?php if(!$canManageSecurity): ?>
            <div class="mb-4">
                <span class="badge badge-warning text-dark">Limited access</span>
                <span class="text-white-50 small ml-2">Some API key and security actions still require Super Admin. Gateway on/off is available to all admins who can open this page.</span>
            </div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table table-borderless text-white align-middle">
                <thead>
                    <tr class="text-white-50 border-bottom border-white-10">
                        <th class="py-3">Provider</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Priority</th>
                        <th class="py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $gateways; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gateway): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="border-bottom border-white-10">
                        <td class="py-3">
                            <div class="d-flex align-items-center">
                                <?php if($gateway->logo_url): ?>
                                    <img src="<?php echo e($gateway->logo_url); ?>" alt="<?php echo e($gateway->display_name); ?>" style="height: 24px; width: 24px; object-fit: contain; margin-right: 12px;">
                                <?php else: ?>
                                    <div style="width: 24px; height: 24px; background: rgba(255,255,255,0.1); border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 0.6rem; margin-right: 12px;"><?php echo e(substr($gateway->display_name, 0, 1)); ?></div>
                                <?php endif; ?>
                                <div>
                                    <div class="fw-bold"><?php echo e($gateway->display_name); ?></div>
                                    <div class="text-white-50 x-small font-monospace"><?php echo e(strtoupper($gateway->name)); ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3">
                            <?php if($gateway->is_active): ?>
                                <span class="badge badge-success px-3 py-2 rounded-pill"><i class="fa fa-check-circle mr-1"></i> Active</span>
                            <?php else: ?>
                                <span class="badge badge-secondary px-3 py-2 rounded-pill" style="background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.5);"><i class="fa fa-times-circle mr-1"></i> Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3">
                            <span class="text-white-50 small">Order: <?php echo e($gateway->priority); ?></span>
                        </td>
                        <td class="py-3 text-right">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input gateway-toggle" 
                                       id="gw_toggle_<?php echo e($gateway->id); ?>" 
                                       data-gateway-id="<?php echo e($gateway->id); ?>" 
                                       <?php echo e($gateway->is_active ? 'checked' : ''); ?>>
                                <label class="custom-control-label" for="gw_toggle_<?php echo e($gateway->id); ?>"></label>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="4" class="text-center py-5 text-white-50">
                            <i class="fa fa-credit-card fa-3x mb-3 opacity-25"></i>
                            <p>No payment gateways found in database.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="s-panel" id="tab-features">
    <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
        <h5 class="text-white mb-1 fw-bold">Service Visibility & Toggles</h5>
        <p class="text-white-50 small mb-4">Enable or disable entire service modules across the platform.</p>
        
        <form id="featureTogglesForm">
            <?php echo csrf_field(); ?>
            <div class="row">
                <?php
                    $toggles = [
                        'nin_service_enabled' => ['label' => 'NIN Verification', 'icon' => 'fa-id-card'],
                        'bvn_service_enabled' => ['label' => 'BVN Verification', 'icon' => 'fa-building-columns'],
                        'legal_service_enabled' => ['label' => 'AI Legal Hub', 'icon' => 'fa-gavel'],
                        'auction_service_enabled' => ['label' => 'Auction Services', 'icon' => 'fa-gavel'],
                        'logistics_service_enabled' => ['label' => 'Logistics & Delivery', 'icon' => 'fa-truck'],
                        'airtime_data_enabled' => ['label' => 'Airtime & Data', 'icon' => 'fa-mobile-screen'],
                        'education_service_enabled' => ['label' => 'Education Services', 'icon' => 'fa-graduation-cap'],
                        'insurance_service_enabled' => ['label' => 'Insurance Services', 'icon' => 'fa-shield-heart'],
                        'maintenance_mode' => ['label' => 'Global Maintenance Mode', 'icon' => 'fa-screwdriver-wrench'],
                    ];
                ?>

                <?php $__currentLoopData = $toggles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-6 mb-4">
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                        <div class="d-flex align-items-center">
                            <div class="mr-3 text-primary" style="font-size: 1.2rem; width: 30px; text-align: center;">
                                <i class="fa <?php echo e($info['icon']); ?>"></i>
                            </div>
                            <div>
                                <p class="text-white mb-0 font-weight-bold"><?php echo e($info['label']); ?></p>
                                <p class="text-white-50 small mb-0"><?php echo e($key === 'maintenance_mode' ? 'Take site offline for users' : 'Hide this service from dashboard'); ?></p>
                            </div>
                        </div>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" name="<?php echo e($key); ?>" class="custom-control-input" id="toggle_<?php echo e($key); ?>" <?php echo e(\App\Models\SystemSetting::get($key, 'true') === 'true' ? 'checked' : ''); ?>>
                            <label class="custom-control-label" for="toggle_<?php echo e($key); ?>"></label>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold">
                    <i class="fa fa-floppy-disk mr-2"></i>Apply Changes
                </button>
            </div>
        </form>
    </div>
</div>


<div class="modal fade" id="notaryDocModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0" style="background: #1a1f2e; border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-white fw-bold" id="notaryModalTitle">Manage Document</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="notaryDocForm">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="form-group mb-4">
                        <label class="text-white-50 small mb-2">Document Type Key</label>
                        <input type="text" name="document_type" id="doc_type" class="form-control text-white rounded-3" placeholder="e.g. affidavit_age" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                    <div class="form-group mb-4">
                        <label class="text-white-50 small mb-2">Price (₦)</label>
                        <input type="number" name="price" id="doc_price" class="form-control text-white rounded-3" value="0" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    </div>
                    <div class="form-group mb-4">
                        <label class="text-white-50 small mb-2">Description</label>
                        <textarea name="description" id="doc_desc" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);"></textarea>
                    </div>
                    <div class="custom-control custom-switch mb-3">
                        <input type="checkbox" class="custom-control-input" id="court_required" name="requires_court_stamp">
                        <label class="custom-control-label text-white-50" for="court_required">Requires Official Court Stamping</label>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="s-panel" id="tab-referrals">
    <form id="referralSettingsForm">
        <?php echo csrf_field(); ?>
        <div class="card border-0 rounded-4 p-4 mb-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
            <h5 class="text-white mb-1 fw-bold">Basic Referral Program</h5>
            <p class="text-white-50 small mb-4">Flat reward issued to referrers when their referrals fund their wallets for the first time.</p>
            
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                        <div class="d-flex align-items-center">
                            <div class="mr-3 text-primary" style="font-size: 1.2rem; width: 30px; text-align: center;">
                                <i class="fa fa-gift"></i>
                            </div>
                            <div>
                                <p class="text-white mb-0 font-weight-bold">Enable Referral Rewards</p>
                                <p class="text-white-50 small mb-0">Issue automatic wallet credits to referrers</p>
                            </div>
                        </div>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" name="referral_reward_enabled" class="custom-control-input" id="toggle_referral_reward_enabled" <?php echo e(\App\Models\SystemSetting::get('referral_reward_enabled', 'false') === 'true' ? 'checked' : ''); ?>>
                            <label class="custom-control-label" for="toggle_referral_reward_enabled"></label>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                        <label class="text-white small mb-2 font-weight-bold">Reward Amount (₦)</label>
                        <input type="number" name="referral_reward_amount" class="form-control text-white rounded-3" value="<?php echo e(\App\Models\SystemSetting::get('referral_reward_amount', 0)); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);" min="0" step="0.01">
                        <p class="text-white-50 x-small mt-2 mb-0">Amount credited to the referrer's wallet after the first successful funding by a referral.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h5 class="text-white mb-1 fw-bold">Matrix Compensation Plan (MLM)</h5>
                    <p class="text-white-50 small mb-0">Multi-level commission system based on transaction percentages.</p>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" name="matrix_enabled" class="custom-control-input" id="toggle_matrix_enabled" <?php echo e(\App\Models\SystemSetting::get('matrix_enabled', 'false') === 'true' ? 'checked' : ''); ?>>
                    <label class="custom-control-label" for="toggle_matrix_enabled"></label>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="text-white small mb-2 font-weight-bold">Matrix Depth (Levels)</label>
                    <select name="matrix_depth" class="form-control text-white rounded-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                        <?php for($i = 0; $i <= 10; $i++): ?>
                            <option value="<?php echo e($i); ?>" <?php echo e(\App\Models\SystemSetting::get('matrix_depth', 0) == $i ? 'selected' : ''); ?>><?php echo e($i); ?> Level<?php echo e($i != 1 ? 's' : ''); ?></option>
                        <?php endfor; ?>
                    </select>
                    <small class="text-white-50 d-block mt-2">Number of upline levels to pay commission to.</small>
                </div>
            </div>

            <div class="row">
                <?php for($i = 1; $i <= 10; $i++): ?>
                <div class="col-md-3 mb-4 matrix-level-input" data-level="<?php echo e($i); ?>" style="<?php echo e(\App\Models\SystemSetting::get('matrix_depth', 0) < $i ? 'display:none;' : ''); ?>">
                    <label class="text-white-50 small mb-2">Level <?php echo e($i); ?> (%)</label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="matrix_level_<?php echo e($i); ?>_percentage" class="form-control text-white rounded-left-3" value="<?php echo e(\App\Models\SystemSetting::get('matrix_level_' . $i . '_percentage', 0)); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                        <div class="input-group-append">
                            <span class="input-group-text bg-dark border-0 text-white">%</span>
                        </div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
            
            <div class="mt-2">
                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold">
                    <i class="fa fa-floppy-disk mr-2"></i>Save Referral & Matrix Settings
                </button>
            </div>
        </div>
    </form>
</div>


<div class="s-panel" id="tab-auction">
    <div class="card border-0 rounded-4 p-4" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.07) !important;">
        <h5 class="text-white mb-1 fw-bold">Auction Settings</h5>
        <p class="text-white-50 small mb-4">Configure the commission percentage for successful auctions.</p>
        <form id="auctionSettingsForm">
            <?php echo csrf_field(); ?>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="text-white-50 small mb-2">Commission Percentage (%)</label>
                    <input type="number" step="0.01" name="auction_commission_percentage" class="form-control text-white rounded-3" value="<?php echo e(\App\Models\SystemSetting::get('auction_commission_percentage', 5)); ?>" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);">
                    <small class="text-info mt-2 d-block">The percentage of the final sale price that Fuwa.NG will take as a commission.</small>
                </div>
            </div>

            <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fa fa-floppy-disk mr-2"></i>Save Auction Settings</button>
        </form>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .tab-strip { display: flex; flex-wrap: wrap; gap: 0; border-bottom: 2px solid rgba(255,255,255,0.06); margin-bottom: 0; }
    .s-tab { padding: 12px 22px; background: none; border: none; color: var(--clr-text-muted, #94a3b8); cursor: pointer; font-size: 0.875rem; font-weight: 600; border-bottom: 2px solid transparent; transition: all 0.2s; white-space: nowrap; }
    .s-tab.active { color: var(--clr-primary, #6366f1); border-bottom-color: var(--clr-primary, #6366f1); }
    .s-panel { display: none; padding-top: 24px; }
    .s-panel.active { display: block; }
    .form-control:focus { background: rgba(99,102,241,0.08) !important; border-color: rgba(99,102,241,0.4) !important; box-shadow: 0 0 0 3px rgba(99,102,241,0.12); color: #fff; outline: none; }
    .font-monospace { font-family: 'Courier New', monospace !important; font-size: 0.875rem !important; letter-spacing: 0.5px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Image Preview functionality
    const fileInputs = document.querySelectorAll('input[type="file"][accept^="image/"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const container = input.closest('.custom-file-upload');
                    let img = container.querySelector('img');
                    if (!img) {
                        img = document.createElement('img');
                        img.className = 'mb-2 d-block mx-auto';
                        if (input.name === 'site_favicon') {
                            img.style.maxHeight = '40px';
                            img.style.borderRadius = '8px';
                        } else if (input.name === 'stamp') {
                            img.style.maxHeight = '80px';
                        } else {
                            img.style.maxHeight = '40px';
                        }
                        container.insertBefore(img, input);
                    }
                    img.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    });

    // Matrix Depth dynamic fields
    const depthSelect = document.querySelector('select[name="matrix_depth"]');
    if (depthSelect) {
        depthSelect.addEventListener('change', function() {
            const depth = parseInt(this.value);
            document.querySelectorAll('.matrix-level-input').forEach(div => {
                const level = parseInt(div.dataset.level);
                if (level <= depth) {
                    div.style.display = 'block';
                } else {
                    div.style.display = 'none';
                }
            });
        });
    }
});

function switchTab(id, btn) {
    document.querySelectorAll('.s-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.s-tab').forEach(b => b.classList.remove('active'));
    document.getElementById(id).classList.add('active');
    btn.classList.add('active');
}

function handleSettingsForm(formId, url) {
    const form = document.getElementById(formId);
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const data = new FormData(form);
        const btn = form.querySelector('[type="submit"]');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i>Saving…';

        $.ajax({
            url: url,
            method: 'POST',
            data: data,
            processData: false,
            contentType: false,
            success(res) {
                Swal.fire({ icon: 'success', title: 'Saved!', text: res.message, background: '#141826', color: '#fff', timer: 2500, showConfirmButton: false });
            },
            error(xhr) {
                const errs = xhr.responseJSON?.errors;
                const msg  = errs ? Object.values(errs).flat().join('\n') : (xhr.responseJSON?.message || 'Save failed.');
                Swal.fire({ icon: 'error', title: 'Error', text: msg, background: '#141826', color: '#fff' });
            },
            complete() {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    });
}

handleSettingsForm('notifForm',      '<?php echo e(route("admin.settings.notification")); ?>');
handleSettingsForm('pricingForm',    '<?php echo e(route("admin.settings.pricing")); ?>');
handleSettingsForm('bankForm',       '<?php echo e(route("admin.settings.manual_funding")); ?>');
handleSettingsForm('apiSettingsForm','<?php echo e(route("admin.settings.api_settings")); ?>');
handleSettingsForm('apiKeysForm',    '<?php echo e(route("admin.settings.api_keys")); ?>');
handleSettingsForm('brandingForm',   '<?php echo e(route("admin.settings.branding")); ?>');
handleSettingsForm('systemPricingForm', '<?php echo e(route("admin.settings.system_pricing")); ?>');
handleSettingsForm('themeForm',      '<?php echo e(route("admin.settings.theme")); ?>');
handleSettingsForm('adminSelfFundingLimitForm', '<?php echo e(route("admin.settings.admin_security")); ?>');
handleSettingsForm('verifymeIpsForm','<?php echo e(route("admin.settings.security.verifyme_ips")); ?>');
handleSettingsForm('featureTogglesForm', '<?php echo e(route("admin.settings.features")); ?>');
handleSettingsForm('referralSettingsForm', '<?php echo e(route("admin.settings.referrals")); ?>');
handleSettingsForm('auctionSettingsForm', '<?php echo e(route("admin.settings.auction")); ?>');

document.addEventListener('DOMContentLoaded', function () {
    const tab = new URLSearchParams(window.location.search).get('tab');
    if (!tab) {
        return;
    }
    const panel = document.getElementById(tab);
    if (!panel) {
        return;
    }
    const btn = Array.from(document.querySelectorAll('.s-tab')).find(b => (b.getAttribute('onclick') || '').includes("switchTab('" + tab + "'"));
    if (btn) {
        switchTab(tab, btn);
    } else {
        document.querySelectorAll('.s-panel').forEach(p => p.classList.remove('active'));
        panel.classList.add('active');
    }
});

function normalizeIpList(raw) {
    const parts = raw.split(/[\s,]+/).map(v => v.trim()).filter(Boolean);
    return Array.from(new Set(parts));
}

function isValidIPv4(ip) {
    const m = ip.match(/^(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/);
    if (!m) return false;
    return m.slice(1).every(n => Number(n) >= 0 && Number(n) <= 255);
}

function isValidIPv6(ip) {
    if (!ip.includes(':')) return false;
    if (!/^[0-9a-fA-F:]+$/.test(ip)) return false;
    return ip.length <= 45;
}

function isValidIp(ip) {
    return isValidIPv4(ip) || isValidIPv6(ip);
}

function setVerifymeIps(list) {
    const field = document.getElementById('verifymeIpsField');
    field.value = list.join('\n');
}

function getVerifymeIps() {
    const field = document.getElementById('verifymeIpsField');
    return normalizeIpList(field.value || '');
}

function renderVerifymeChips() {
    const list = getVerifymeIps();
    const container = document.getElementById('verifymeIpChips');
    container.innerHTML = '';
    if (list.length === 0) {
        const span = document.createElement('span');
        span.className = 'text-white-50 small';
        span.textContent = 'No IPs configured.';
        container.appendChild(span);
        return;
    }
    list.forEach(ip => {
        const badge = document.createElement('span');
        badge.className = 'badge badge-pill badge-dark font-monospace px-3 py-2';
        badge.style.background = 'rgba(255,255,255,0.06)';
        badge.style.border = '1px solid rgba(255,255,255,0.08)';
        badge.dataset.ip = ip;
        badge.textContent = ip + ' ';

        const rm = document.createElement('button');
        rm.type = 'button';
        rm.className = 'ml-2 text-white-50';
        rm.style.background = 'transparent';
        rm.style.border = '0';
        rm.style.padding = '0';
        rm.textContent = '×';
        rm.addEventListener('click', () => {
            const next = getVerifymeIps().filter(v => v !== ip);
            setVerifymeIps(next);
            renderVerifymeChips();
        });

        if (!document.getElementById('verifymeAddIpBtn')?.disabled) {
            badge.appendChild(rm);
        }
        container.appendChild(badge);
    });
}

const verifymeIpInput = document.getElementById('verifymeIpInput');
const verifymeIpHint = document.getElementById('verifymeIpHint');
const verifymeAddBtn = document.getElementById('verifymeAddIpBtn');

if (verifymeIpInput && verifymeAddBtn) {
    renderVerifymeChips();

    verifymeIpInput.addEventListener('input', () => {
        const v = (verifymeIpInput.value || '').trim();
        if (!v) {
            verifymeIpHint.style.display = 'none';
            return;
        }
        if (!isValidIp(v)) {
            verifymeIpHint.textContent = 'Invalid IP format.';
            verifymeIpHint.style.display = 'block';
        } else {
            verifymeIpHint.style.display = 'none';
        }
    });

    verifymeAddBtn.addEventListener('click', () => {
        const v = (verifymeIpInput.value || '').trim();
        if (!v) return;
        if (!isValidIp(v)) {
            verifymeIpHint.textContent = 'Invalid IP format.';
            verifymeIpHint.style.display = 'block';
            return;
        }
        const list = getVerifymeIps();
        if (!list.includes(v)) {
            list.push(v);
            setVerifymeIps(list);
            renderVerifymeChips();
        }
        verifymeIpInput.value = '';
        verifymeIpHint.style.display = 'none';
    });
}

const generateBtn = document.getElementById('verifymeGenerateSecretBtn');
if (generateBtn) {
    generateBtn.addEventListener('click', () => {
        Swal.fire({
            icon: 'warning',
            title: 'Generate secret?',
            text: 'This will generate a new secret. It is not saved until you rotate.',
            showCancelButton: true,
            confirmButtonText: 'Generate',
            background: '#141826',
            color: '#fff',
        }).then((r) => {
            if (!r.isConfirmed) return;
            $.ajax({
                url: '<?php echo e(route("admin.settings.security.verifyme_secret.generate")); ?>',
                method: 'POST',
                data: {_token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')},
                success(res) {
                    document.getElementById('verifymeSecretInput').value = res.secret;
                    Swal.fire({ icon: 'success', title: 'Generated', text: 'Secret generated. Click “Rotate Secret” to apply it.', background: '#141826', color: '#fff' });
                },
                error() {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to generate secret.', background: '#141826', color: '#fff' });
                }
            });
        });
    });
}

const secretForm = document.getElementById('verifymeSecretForm');
if (secretForm) {
    secretForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const secret = (document.getElementById('verifymeSecretInput').value || '').trim();
        if (!secret) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Secret cannot be empty.', background: '#141826', color: '#fff' });
            return;
        }
        Swal.fire({
            icon: 'warning',
            title: 'Rotate VerifyMe secret?',
            text: 'This will immediately invalidate the old secret.',
            showCancelButton: true,
            confirmButtonText: 'Rotate',
            confirmButtonColor: '#ef4444',
            background: '#141826',
            color: '#fff',
        }).then((r) => {
            if (!r.isConfirmed) return;
            const btn = secretForm.querySelector('[type="submit"]');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i>Rotating…';
            $.ajax({
                url: '<?php echo e(route("admin.settings.security.verifyme_secret")); ?>',
                method: 'POST',
                data: new FormData(secretForm),
                processData: false,
                contentType: false,
                success(res) {
                    Swal.fire({ icon: 'success', title: 'Rotated!', text: res.message, background: '#141826', color: '#fff' });
                    setTimeout(() => location.reload(), 1200);
                },
                error(xhr) {
                    const errs = xhr.responseJSON?.errors;
                    const msg  = errs ? Object.values(errs).flat().join('\n') : (xhr.responseJSON?.message || 'Rotate failed.');
                    Swal.fire({ icon: 'error', title: 'Error', text: msg, background: '#141826', color: '#fff' });
                },
                complete() {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            });
        });
    });
}

// Notary modals + payment gateway toggles use jQuery/Bootstrap from Vite (deferred). Bind after jQuery exists.
(function initAdminSettingsJqueryBindings() {
    if (typeof window.jQuery === 'undefined') {
        window.setTimeout(initAdminSettingsJqueryBindings, 50);
        return;
    }
    var $ = window.jQuery;

    window.showAddDocModal = function () {
        $('#notaryModalTitle').text('Add Document Type');
        $('#notaryDocForm')[0].reset();
        $('#notaryDocModal').modal('show');
    };

    window.editNotaryDoc = function (doc) {
        $('#notaryModalTitle').text('Edit ' + doc.document_type);
        $('#doc_type').val(doc.document_type).prop('readonly', true);
        $('#doc_price').val(doc.price);
        $('#doc_desc').val(doc.description);
        $('#court_required').prop('checked', doc.requires_court_stamp);
        $('#notaryDocModal').modal('show');
    };

    $('#notaryDocForm').on('submit', function(e) {
        e.preventDefault();
        const data = new FormData(this);
        const btn = $(this).find('[type="submit"]');
        const originalText = btn.html();
        btn.disabled = true;
        btn.html('<i class="fa fa-spinner fa-spin mr-2"></i>Saving…');

        $.ajax({
            url: '<?php echo e(route("admin.settings.notary_docs")); ?>',
            method: 'POST',
            data: data,
            processData: false,
            contentType: false,
            success(res) {
                Swal.fire({ icon: 'success', title: 'Saved!', text: res.message, background: '#141826', color: '#fff' });
                setTimeout(() => location.reload(), 1500);
            },
            error(xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to save.', background: '#141826', color: '#fff' });
            },
            complete() {
                btn.disabled = false;
                btn.html(originalText);
            }
        });
    });

    $(document).on('change', '.gateway-toggle', function() {
        const id = $(this).attr('data-gateway-id');
        const isActive = $(this).is(':checked') ? 1 : 0;
        const $toggle = $(this);
        if (!id) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Missing gateway id.', background: '#141826', color: '#fff' });
            return;
        }

        $toggle.prop('disabled', true);

        $.ajax({
            url: '<?php echo e(route("admin.settings.gateways.toggle")); ?>',
            method: 'POST',
            dataType: 'json',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            data: {
                _token: '<?php echo e(csrf_token()); ?>',
                id: id,
                is_active: isActive
            },
            success(res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Updated',
                    text: res.message,
                    background: '#141826',
                    color: '#fff',
                    timer: 1500,
                    showConfirmButton: false
                });
                const $row = $toggle.closest('tr');
                const $badgeCell = $row.find('td:nth-child(2)');
                if (res.is_active) {
                    $badgeCell.html('<span class="badge badge-success px-3 py-2 rounded-pill"><i class="fa fa-check-circle mr-1"></i> Active</span>');
                } else {
                    $badgeCell.html('<span class="badge badge-secondary px-3 py-2 rounded-pill" style="background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.5);"><i class="fa fa-times-circle mr-1"></i> Inactive</span>');
                }
            },
            error(xhr) {
                $toggle.prop('checked', !isActive);
                const msg = xhr.responseJSON?.message
                    || (xhr.responseJSON?.errors && Object.values(xhr.responseJSON.errors).flat().join(' '))
                    || (xhr.status === 419 ? 'Session expired. Refresh the page and try again.' : null)
                    || 'Failed to toggle gateway status.';
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: msg,
                    background: '#141826',
                    color: '#fff'
                });
            },
            complete() {
                $toggle.prop('disabled', false);
            }
        });
    });
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.nexus', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/fuwa.ng/html/resources/views/admin/settings/index.blade.php ENDPATH**/ ?>