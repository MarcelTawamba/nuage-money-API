@extends('layouts.app')

@section('content')
    <div class="content px-0 px-md-3" style="background-color: white">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1>
                            <i class="fas fa-paper-plane text-primary mr-2"></i>Send Funds via YellowCard
                        </h1>
                        <p class="text-muted">Submit a payment request to send funds to a recipient</p>
                    </div>
                </div>
            </div>
        </section>

        @include('adminlte-templates::common.errors')

        <div class="">
            {!! Form::open(['route' => ['apps.withdraw_post',$client->id], 'id' => 'yellowcard-payment-form']) !!}
                <input type="hidden" id="customer_uid" name="customerUID" value="{{ (string) Auth::user()->id }}">

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-exchange-alt mr-2"></i>Transaction Details</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Currency Selection -->
                        <div class="form-group col-md-6">
                            {!! Form::label('currency', 'Currency:') !!}
                            {!! Form::select('currency', $currency, $defaultCurrency ?? null, ['class' => 'form-control', 'required', 'id' => 'currency']) !!}
                            <small class="text-muted">Select the currency for this transaction</small>
                        </div>

                        <!-- Country Selection -->
                        <div class="form-group col-md-6">
                            {!! Form::label('country', 'Country:') !!}
                            <select name="sender[country]" id="country" class="form-control" required>
                                <option value="">-- Select Country --</option>
                            </select>
                            <small class="text-muted">Country where payment will be sent</small>
                        </div>

                        <!-- Amount Type -->
                        <div class="form-group col-md-6">
                            {!! Form::label('amount_type', 'Amount Type:') !!}
                            <select id="amount_type" class="form-control" required>
                                <option value="local">Local Currency Amount</option>
                                <option value="usd">USD Amount</option>
                            </select>
                            <small class="text-muted">Choose whether amount is in local currency or USD</small>
                        </div>

                        <!-- Amount Field -->
                        <div class="form-group col-md-6">
                            {!! Form::label('amount', 'Amount:') !!}
                            <input type="number" name="localAmount" id="amount_field" class="form-control" required step="0.01" min="1" />
                            <small class="text-muted" id="amount-hint">Enter the amount in local currency</small>
                        </div>

                        <!-- Reason for Payment -->
                        <div class="form-group col-md-6">
                            {!! Form::label('reason', 'Reason for Payment:') !!}
                            {!! Form::select('reason', [
                                'entertainment' => 'Entertainment',
                                'education' => 'Education',
                                'family_support' => 'Family Support',
                                'investment' => 'Investment',
                                'personal' => 'Personal',
                                'business' => 'Business',
                                'salary' => 'Salary',
                                'gift' => 'Gift',
                                'other' => 'Other'
                            ], 'entertainment', ['class' => 'form-control', 'required']) !!}
                            <small class="text-muted">Select the purpose of this payment</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sender Information (Your Business) -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-building mr-2"></i>Sender Details (Your Business)</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Customer Type -->
                        <div class="form-group col-md-12">
                            {!! Form::label('customerType', 'Sender Type:') !!}
                            <select name="customerType" id="customerType" class="form-control" required>
                                <option value="retail">Individual (Retail)</option>
                                <option value="institution">Business (Institution)</option>
                            </select>
                            <small class="text-muted">Select whether you're sending as an individual or business</small>
                        </div>
                    </div>

                    <!-- Retail Sender Fields -->
                    <div id="retail-sender-fields">
                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Form::label('sender[name]', 'Full Name:') !!}
                                {!! Form::text('sender[name]', Auth::user()->name, ['class' => 'form-control', 'id' => 'sender_name', 'required' => true]) !!}
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('sender[email]', 'Email:') !!}
                                {!! Form::email('sender[email]', Auth::user()->email, ['class' => 'form-control', 'id' => 'sender_email']) !!}
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('sender[phone]', 'Phone Number:') !!}
                                {!! Form::tel('sender[phone]', Auth::user()->phone_number ?? null, ['class' => 'form-control', 'id' => 'sender_phone', 'placeholder' => '+234XXXXXXXXX', 'required']) !!}
                                <small class="text-muted">Include country code (e.g., +234)</small>
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('sender[dob]', 'Date of Birth:') !!}
                                {!! Form::date('sender[dob]', null, ['class' => 'form-control', 'id' => 'sender_dob', 'max' => date('Y-m-d'), 'required' => true]) !!}
                            </div>

                            <div class="form-group col-md-12">
                                {!! Form::label('sender[address]', 'Address:') !!}
                                {!! Form::text('sender[address]', null, ['class' => 'form-control', 'id' => 'sender_address', 'required' => true]) !!}
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('sender[idType]', 'ID Type:') !!}
                                <select name="sender[idType]" id="sender_idType" class="form-control" required>
                                    <option value="">-- Select ID Type --</option>
                                </select>
                                <small class="text-muted" id="sender-idType-hint">Government-issued identification</small>
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('sender[idNumber]', 'ID Number:') !!}
                                {!! Form::text('sender[idNumber]', null, ['class' => 'form-control', 'id' => 'sender_idNumber', 'required' => true]) !!}
                            </div>

                            <!-- Additional ID Fields (for Nigeria) -->
                            <div class="form-group col-md-6" id="sender-additional-id-group" style="display: none;">
                                {!! Form::label('sender[additionalIdType]', 'Additional ID Type (Nigeria):') !!}
                                {!! Form::select('sender[additionalIdType]', [
                                    '' => '-- Select Additional ID Type --',
                                    'NIN' => 'National Identification Number (NIN)',
                                    'BVN' => 'Bank Verification Number (BVN)'
                                ], null, ['class' => 'form-control', 'id' => 'sender_additionalIdType']) !!}
                                <small class="text-muted">Nigeria requires both NIN and BVN (they must be different)</small>
                            </div>

                            <div class="form-group col-md-6" id="sender-additional-id-number-group" style="display: none;">
                                {!! Form::label('sender[additionalIdNumber]', 'Additional ID Number:') !!}
                                {!! Form::text('sender[additionalIdNumber]', null, ['class' => 'form-control', 'id' => 'sender_additionalIdNumber']) !!}
                                <small class="text-muted">Enter the number for your additional ID type</small>
                            </div>
                        </div>
                    </div>

                    <!-- Institution Sender Fields -->
                    <div id="institution-sender-fields" style="display: none;">
                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Form::label('sender[businessName]', 'Business Name:') !!}
                                {!! Form::text('sender[businessName]', null, ['class' => 'form-control', 'id' => 'sender_businessName']) !!}
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('sender[businessId]', 'Business Registration Number:') !!}
                                {!! Form::text('sender[businessId]', null, ['class' => 'form-control', 'id' => 'sender_businessId']) !!}
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('sender[phone]', 'Business Phone:') !!}
                                {!! Form::tel('sender[phone]', null, ['class' => 'form-control business-phone', 'placeholder' => '+234XXXXXXXXX']) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recipient/Destination Information -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user mr-2"></i>Recipient Details (Customer)</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Account Type -->
                        <div class="form-group col-md-6">
                            {!! Form::label('destination[accountType]', 'Account Type:') !!}
                            <select name="destination[accountType]" id="destination_accountType" class="form-control" required>
                                <option value="">-- Select Account Type --</option>
                                <option value="bank">Bank Account</option>
                                <option value="momo">Mobile Money</option>
                            </select>
                            <small class="text-muted">Where should the money be sent?</small>
                        </div>

                        <!-- Network Selection (dynamic based on country/accountType) -->
                        <div class="form-group col-md-6" id="network-group" style="display: none;">
                            {!! Form::label('destination[networkId]', 'Bank / Network:') !!}
                            <select name="destination[networkId]" id="destination_networkId" class="form-control">
                                <option value="">-- Select Network --</option>
                            </select>
                            <small class="text-muted">Select the bank or mobile money network</small>
                        </div>

                        <!-- Account Number -->
                        <div class="form-group col-md-6">
                            {!! Form::label('destination[accountNumber]', 'Account Number:') !!}
                            {!! Form::text('destination[accountNumber]', null, ['class' => 'form-control', 'id' => 'destination_accountNumber', 'required' => true, 'placeholder' => 'Enter account number or phone']) !!}
                            <small class="text-muted">Bank account number or mobile money number</small>
                        </div>

                        <!-- Account Name (optional - can be verified via API) -->
                        <div class="form-group col-md-6">
                            {!! Form::label('destination[accountName]', 'Account Name:') !!}
                            {!! Form::text('destination[accountName]', null, ['class' => 'form-control', 'id' => 'destination_accountName', 'placeholder' => 'Optional - will be verified']) !!}
                            <small class="text-muted">Leave empty to auto-verify from bank</small>
                            <button type="button" id="verify-account-btn" class="btn btn-sm btn-info mt-2" style="display: none;">
                                <i class="fas fa-check-circle mr-1"></i>Verify Account
                            </button>
                            <span id="verification-status" class="ml-2"></span>
                        </div>

                        <!-- Phone Number (for momo) -->
                        <div class="form-group col-md-6" id="destination-phone-group" style="display: none;">
                            {!! Form::label('destination[phoneNumber]', 'Phone Number:') !!}
                            {!! Form::tel('destination[phoneNumber]', null, ['class' => 'form-control', 'id' => 'destination_phoneNumber', 'placeholder' => '+234XXXXXXXXX']) !!}
                            <small class="text-muted">Mobile money phone number</small>
                        </div>
                    </div>

                    <!-- Verification Alert -->
                    <div class="alert alert-info" id="verification-alert" style="display: none;">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Account Verification:</strong> 
                        <span id="verification-message">Enter account details to verify</span>
                    </div>
                </div>
            </div>

            <!-- Hidden Fields -->
            <input type="hidden" name="forceAccept" value="1" />

            <!-- Submit Buttons -->
            <div class="card mt-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <button type="submit" class="btn btn-primary btn-lg" id="submit-btn">
                                <i class="fas fa-paper-plane mr-2"></i>Send Payment
                            </button>
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-lg ml-2">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </a>
                        </div>
                        <div class="text-muted">
                            <small><i class="fas fa-shield-alt mr-1"></i>Secure payment via YellowCard</small>
                        </div>
                    </div>
                </div>
            </div>

            {!! Form::close() !!}
        </div>
    </div>

<script>
// @ts-nocheck
document.addEventListener('DOMContentLoaded', function() {
    // Parse channels and networks from backend
    const allChannels = {!! json_encode($channels ?? []) !!};
    const withdrawChannels = allChannels.filter(c => 
        c.status === 'active' && c.rampType === 'withdraw'
    );
    
    // Filter for active networks only
    const allNetworks = {!! json_encode($networks ?? []) !!};
    const activeNetworks = allNetworks.filter(n => n.status === 'active');
    
    // Get all channel IDs that have active networks
    const channelIdsWithNetworks = new Set();
    activeNetworks.forEach(network => {
        if (network.channelIds && Array.isArray(network.channelIds)) {
            network.channelIds.forEach(id => channelIdsWithNetworks.add(id));
        }
    });
    
    // DOM elements
    const currencySelect = document.getElementById('currency');
    const countrySelect = document.getElementById('country');
    const accountTypeSelect = document.getElementById('destination_accountType');
    const networkGroup = document.getElementById('network-group');
    const networkSelect = document.getElementById('destination_networkId');
    const customerTypeSelect = document.getElementById('customerType');
    const retailFields = document.getElementById('retail-sender-fields');
    const institutionFields = document.getElementById('institution-sender-fields');
    const destinationPhoneGroup = document.getElementById('destination-phone-group');
    const verifyAccountBtn = document.getElementById('verify-account-btn');
    const accountNumberInput = document.getElementById('destination_accountNumber');
    const accountNameInput = document.getElementById('destination_accountName');
    const verificationStatus = document.getElementById('verification-status');
    
    // Country code to name mapping
    const countryNames = {
        'NG': 'Nigeria',
        'KE': 'Kenya',
        'GH': 'Ghana',
        'ZA': 'South Africa',
        'UG': 'Uganda',
        'TZ': 'Tanzania',
        'RW': 'Rwanda',
        'ZM': 'Zambia',
        'MW': 'Malawi',
        'CM': 'Cameroon',
        'CI': 'Côte d\'Ivoire',
        'SN': 'Senegal',
        'BW': 'Botswana',
        'TG': 'Togo',
        'BF': 'Burkina Faso',
        'GA': 'Gabon',
        'CD': 'Democratic Republic of Congo',
        'CG': 'Republic of Congo',
        'BJ': 'Benin'
    };
    
    // Currency to country mapping (default country for each currency)
    const currencyToCountry = {
        'NGN': 'NG',
        'KES': 'KE',
        'GHS': 'GH',
        'ZAR': 'ZA',
        'UGX': 'UG',
        'TZS': 'TZ',
        'RWF': 'RW',
        'ZMW': 'ZM',
        'MWK': 'MW',
        'XAF': 'CM',
        'XOF': 'SN',
        'BWP': 'BW'
    };
    
    // Populate countries from withdraw channels (only those with active networks)
    function updateCountries() {
        // Filter withdraw channels that have active networks
        const channelsWithNetworks = withdrawChannels.filter(c => 
            channelIdsWithNetworks.has(c.id)
        );
        
        const uniqueCountries = [...new Set(channelsWithNetworks.map(c => c.country))];
        countrySelect.innerHTML = '<option value="">-- Select Country --</option>';
        
        uniqueCountries.sort((a, b) => {
            const nameA = countryNames[a] || a;
            const nameB = countryNames[b] || b;
            return nameA.localeCompare(nameB);
        });
        
        uniqueCountries.forEach(country => {
            const option = document.createElement('option');
            option.value = country;
            const countryName = countryNames[country] || country;
            option.textContent = `${countryName} (${country})`;
            countrySelect.appendChild(option);
        });
        
        // Set default country based on selected currency
        const selectedCurrency = currencySelect.value;
        const defaultCountry = currencyToCountry[selectedCurrency];
        if (defaultCountry && uniqueCountries.includes(defaultCountry)) {
            countrySelect.value = defaultCountry;
            updateNetworks();
        }
    }
    
    // Update networks based on country and account type
    function updateNetworks() {
        const country = countrySelect.value;
        const accountType = accountTypeSelect.value;
        const currency = currencySelect.value;
        
        if (!country || !accountType) {
            networkGroup.style.display = 'none';
            return;
        }
        
        // Find matching channels
        const matchingChannels = withdrawChannels.filter(c => 
            c.country === country && 
            c.currency === currency &&
            c.channelType === accountType
        );
        
        if (matchingChannels.length === 0) {
            networkGroup.style.display = 'none';
            return;
        }
        
        // Get channel IDs
        const channelIds = matchingChannels.map(c => c.id);
        
        // Filter networks that support these channels
        const supportedNetworks = activeNetworks.filter(n => 
            n.channelIds && 
            n.channelIds.some(id => channelIds.includes(id))
        );
        
        // Populate network dropdown
        networkSelect.innerHTML = '<option value="">-- Select Network --</option>';
        supportedNetworks.forEach(network => {
            const option = document.createElement('option');
            option.value = network.id;
            option.textContent = network.name || network.code;
            option.dataset.code = network.code;
            option.dataset.country = network.country;
            networkSelect.appendChild(option);
        });
        
        networkGroup.style.display = 'block';
        verifyAccountBtn.style.display = 'inline-block';
    }
    
    // Toggle sender type fields
    customerTypeSelect.addEventListener('change', function() {
        if (this.value === 'retail') {
            retailFields.style.display = 'block';
            institutionFields.style.display = 'none';
            // Enable retail fields and set as required
            document.querySelectorAll('#retail-sender-fields input, #retail-sender-fields select').forEach(el => {
                el.disabled = false;
                if (el.hasAttribute('data-required')) {
                    el.required = true;
                }
            });
            // Disable institution fields
            document.querySelectorAll('#institution-sender-fields input, #institution-sender-fields select').forEach(el => {
                el.disabled = true;
                el.required = false;
            });
        } else {
            retailFields.style.display = 'none';
            institutionFields.style.display = 'block';
            // Disable retail fields
            document.querySelectorAll('#retail-sender-fields input, #retail-sender-fields select').forEach(el => {
                el.disabled = true;
                el.required = false;
            });
            // Enable institution fields and set as required
            document.querySelectorAll('#institution-sender-fields input, #institution-sender-fields select').forEach(el => {
                el.disabled = false;
                if (el.hasAttribute('data-required')) {
                    el.required = true;
                }
            });
            document.querySelector('.business-phone').required = true;
        }
    });
    
    // Function to update ID Type options for Nigeria
    function updateNigeriaIdOptions() {
        const idTypeSelect = document.getElementById('sender_idType');
        const additionalIdTypeSelect = document.getElementById('sender_additionalIdType');
        const currentValue = idTypeSelect.value;
        
        // Update ID Type dropdown to only NIN and BVN for Nigeria
        idTypeSelect.innerHTML = '';
        idTypeSelect.innerHTML = '<option value="">-- Select ID Type --</option>';
        idTypeSelect.innerHTML += '<option value="NIN">NIN (National Identification Number)</option>';
        idTypeSelect.innerHTML += '<option value="BVN">BVN (Bank Verification Number)</option>';
        
        // Restore selection if it was NIN or BVN
        if (currentValue === 'NIN' || currentValue === 'BVN') {
            idTypeSelect.value = currentValue;
        }
        
        // Reset Additional ID Type dropdown
        additionalIdTypeSelect.innerHTML = '<option value="">-- Select Additional ID Type --</option>';
        
        // Filter additionalIdType based on what's selected in idType
        if (currentValue === 'NIN') {
            additionalIdTypeSelect.innerHTML += '<option value="BVN">BVN (Bank Verification Number)</option>';
        } else if (currentValue === 'BVN') {
            additionalIdTypeSelect.innerHTML += '<option value="NIN">NIN (National Identification Number)</option>';
        } else {
            // Show both if nothing selected yet
            additionalIdTypeSelect.innerHTML += '<option value="BVN">BVN (Bank Verification Number)</option>';
            additionalIdTypeSelect.innerHTML += '<option value="NIN">NIN (National Identification Number)</option>';
        }
    }
    
    // Function to reset ID Type options to default (non-Nigeria countries)
    function resetDefaultIdOptions() {
        const idTypeSelect = document.getElementById('sender_idType');
        const currentValue = idTypeSelect.value;
        
        // Reset to default options
        idTypeSelect.innerHTML = '';
        idTypeSelect.innerHTML = '<option value="">-- Select ID Type --</option>';
        idTypeSelect.innerHTML += '<option value="national_id">National ID</option>';
        idTypeSelect.innerHTML += '<option value="passport">Passport</option>';
        idTypeSelect.innerHTML += '<option value="drivers_license">Driver&#39;s License</option>';
        idTypeSelect.innerHTML += '<option value="voters_card">Voter&#39;s Card</option>';
        
        // Try to restore selection if it still exists
        if (['national_id', 'passport', 'drivers_license', 'voters_card'].includes(currentValue)) {
            idTypeSelect.value = currentValue;
        }
    }
    
    // Handle country change for Nigeria (show additional ID fields)
    countrySelect.addEventListener('change', function() {
        const country = this.value;
        const additionalIdGroup = document.getElementById('sender-additional-id-group');
        const additionalIdNumberGroup = document.getElementById('sender-additional-id-number-group');
        
        if (country === 'NG') {
            additionalIdGroup.style.display = 'block';
            additionalIdNumberGroup.style.display = 'block';
            document.getElementById('sender_additionalIdType').required = true;
            document.getElementById('sender_additionalIdNumber').required = true;
            
            // Update ID Type options for Nigeria
            updateNigeriaIdOptions();
        } else {
            additionalIdGroup.style.display = 'none';
            additionalIdNumberGroup.style.display = 'none';
            document.getElementById('sender_additionalIdType').required = false;
            document.getElementById('sender_additionalIdNumber').required = false;
            
            // Reset ID Type options to default
            resetDefaultIdOptions();
        }
        
        updateNetworks();
    });
    
    // Handle ID Type change (for Nigeria - update additional ID Type options)
    document.getElementById('sender_idType').addEventListener('change', function() {
        const country = countrySelect.value;
        if (country === 'NG') {
            updateNigeriaIdOptions();
        }
    });
    
    // Account type change
    accountTypeSelect.addEventListener('change', function() {
        if (this.value === 'momo') {
            destinationPhoneGroup.style.display = 'block';
            document.getElementById('destination_phoneNumber').required = true;
        } else {
            destinationPhoneGroup.style.display = 'none';
            document.getElementById('destination_phoneNumber').required = false;
        }
        
        updateNetworks();
    });
    
    // Currency change
    currencySelect.addEventListener('change', function() {
        // Update default country based on new currency selection
        const selectedCurrency = this.value;
        const defaultCountry = currencyToCountry[selectedCurrency];
        const uniqueCountries = [...new Set(withdrawChannels.map(c => c.country))];
        
        if (defaultCountry && uniqueCountries.includes(defaultCountry)) {
            countrySelect.value = defaultCountry;
        }
        
        updateNetworks();
    });
    
    // Amount type change handler
    const amountTypeSelect = document.getElementById('amount_type');
    const amountField = document.getElementById('amount_field');
    const amountHint = document.getElementById('amount-hint');
    
    amountTypeSelect.addEventListener('change', function() {
        if (this.value === 'usd') {
            amountField.setAttribute('name', 'amount');
            amountHint.textContent = 'Enter the amount in USD';
        } else {
            amountField.setAttribute('name', 'localAmount');
            amountHint.textContent = 'Enter the amount in local currency';
        }
    });
    
    // Network change - enable verification
    networkSelect.addEventListener('change', function() {
        if (this.value && accountNumberInput.value) {
            verifyAccountBtn.style.display = 'inline-block';
        }
    });
    
    // Account number input
    accountNumberInput.addEventListener('input', function() {
        if (this.value && networkSelect.value) {
            verifyAccountBtn.style.display = 'inline-block';
        }
        verificationStatus.innerHTML = '';
    });
    
    // Verify account button
    verifyAccountBtn.addEventListener('click', function() {
        const accountNumber = accountNumberInput.value;
        const networkId = networkSelect.value;
        
        if (!accountNumber || !networkId) {
            alert('Please enter account number and select network');
            return;
        }
        
        verificationStatus.innerHTML = '<span class="text-info"><i class="fas fa-spinner fa-spin mr-1"></i>Verifying...</span>';
        
        // Call verification API
        fetch('/yc/verify-bank-details', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                accountNumber: accountNumber,
                networkId: networkId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.accountName) {
                accountNameInput.value = data.data.accountName;
                verificationStatus.innerHTML = '<span class="text-success"><i class="fas fa-check-circle mr-1"></i>Verified: ' + data.data.accountName + '</span>';
            } else {
                verificationStatus.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle mr-1"></i>Could not verify account</span>';
            }
        })
        .catch(error => {
            verificationStatus.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle mr-1"></i>Verification failed</span>';
        });
    });
    
    // Form validation before submit
    document.getElementById('yellowcard-payment-form').addEventListener('submit', function(e) {
        const country = countrySelect.value;
        
        // Nigeria validation
        if (country === 'NG' && customerTypeSelect.value === 'retail') {
            const idType = document.getElementById('sender_idType').value;
            const additionalIdType = document.getElementById('sender_additionalIdType').value;
            
            if (idType === additionalIdType) {
                e.preventDefault();
                alert('For Nigeria: ID Type and Additional ID Type must be different (one NIN, one BVN)');
                return false;
            }
            
            const hasNIN = idType === 'NIN' || additionalIdType === 'NIN';
            const hasBVN = idType === 'BVN' || additionalIdType === 'BVN';
            
            if (!hasNIN || !hasBVN) {
                e.preventDefault();
                alert('For Nigeria: You must provide both NIN and BVN');
                return false;
            }
        }
        
        // Show loading state
        const submitBtn = document.getElementById('submit-btn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
    });
    
    // Initialize
    updateCountries();
    
    // Initialize sender fields - ensure retail fields are enabled by default, institution disabled
    document.querySelectorAll('#retail-sender-fields input, #retail-sender-fields select').forEach(el => {
        el.disabled = false;
    });
    document.querySelectorAll('#institution-sender-fields input, #institution-sender-fields select').forEach(el => {
        el.disabled = true;
    });
    
    // Initialize ID Type dropdown based on default country (if Nigeria is pre-selected)
    const initialCountry = countrySelect.value;
    if (initialCountry === 'NG') {
        updateNigeriaIdOptions();
        document.getElementById('sender-additional-id-group').style.display = 'block';
        document.getElementById('sender-additional-id-number-group').style.display = 'block';
        document.getElementById('sender_additionalIdType').required = true;
        document.getElementById('sender_additionalIdNumber').required = true;
    } else if (initialCountry) {
        resetDefaultIdOptions();
    } else {
        // No country selected yet, set default options
        resetDefaultIdOptions();
    }
});
</script>
@endsection
