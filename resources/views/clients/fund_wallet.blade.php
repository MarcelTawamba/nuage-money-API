@extends('layouts.app')

@section('content')
    <div class="content px-0 px-md-3" style="background-color: white">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <h1>
                            <i class="fas fa-plus-circle text-success mr-2"></i>Fund Fiat Wallet via YellowCard
                        </h1>
                        <p class="text-muted">Submit a collection request to receive funds</p>
                    </div>
                </div>
            </div>
        </section>

        @include('adminlte-templates::common.errors')

        <div class="">
            {!! Form::open(['route' => ['apps.fund_fiat_wallet_post',$client->id], 'id' => 'yellowcard-collection-form']) !!}

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-money-check-alt mr-2"></i>Sender Details</h3>
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
                            <select name="country" id="country" class="form-control" required>
                                <option value="">-- Select Country --</option>
                            </select>
                            <small class="text-muted">Country where payment will be collected</small>
                        </div>

                        <!-- Hidden Channel ID -->
                        <input type="hidden" name="channelId" id="channelId" />

                        <!-- Amount Type -->
                        <div class="form-group col-md-6">
                            {!! Form::label('amount_type', 'Amount Type:') !!}
                            <select id="amount_type" class="form-control" required>
                                <option value="local">Local Currency Amount</option>
                                <option value="usd">USD Amount</option>
                            </select>
                        </div>

                        <!-- Amount Field -->
                        <div class="form-group col-md-6">
                            {!! Form::label('amount_field', 'Amount:') !!}
                            <input type="number" name="localAmount" id="amount_field" class="form-control" required step="0.01" min="0" />
                            <small class="text-muted" id="amount-hint">Enter the amount in local currency</small>
                        </div>

                        <!-- Account Type (Display) -->
                        <div class="form-group col-md-6">
                            {!! Form::label('display_accountType', 'Payment Method:') !!}
                            <select id="display_accountType" class="form-control" required>
                                <option value="">-- Select Payment Method --</option>
                            </select>
                            <small class="text-muted">Available payment methods for selected country</small>
                        </div>
                        <input type="hidden" name="source[accountType]" id="source_accountType" />

                        <!-- Network Selection (Hidden - Managed Dynamically) -->
                        <div class="form-group col-md-6" id="network-group" style="display: none;">
                            {!! Form::label('source[networkId]', 'Network:') !!}
                            {!! Form::select('source[networkId]', [], null, ['class' => 'form-control', 'id' => 'source_networkId']) !!}
                            <small class="text-muted">Select mobile money network</small>
                        </div>

                        <!-- Account Number (optional for some channels) -->
                        <div class="form-group col-md-6">
                            {!! Form::label('source[accountNumber]', 'Account Number / Sender Phone Number:') !!}
                            {!! Form::text('source[accountNumber]', null, ['class' => 'form-control', 'id' => 'source_accountNumber', 'placeholder' => 'Enter phone number or account']) !!}
                            <small class="text-muted">Optional for some channels</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user mr-2"></i>Recipient Details</h3>
                </div>
                <div class="card-body">
                    <!-- Customer Type -->
                    <div class="row">
                        <div class="form-group col-md-12">
                            {!! Form::label('customerType', 'Customer Type:') !!}
                            <select name="customerType" id="customerType" class="form-control" required>
                                <option value="retail">Individual (Retail)</option>
                                <option value="institution">Business (Institution)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Retail Customer Fields -->
                    <div id="retail-fields">
                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Form::label('recipient[firstName]', 'First Name:') !!}
                                {!! Form::text('recipient[firstName]', null, ['class' => 'form-control', 'id' => 'recipient_firstName', 'required' => true, 'placeholder' => 'Enter first name']) !!}
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('recipient[lastName]', 'Last Name:') !!}
                                {!! Form::text('recipient[lastName]', null, ['class' => 'form-control', 'id' => 'recipient_lastName', 'required' => true, 'placeholder' => 'Enter last name']) !!}
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('recipient[email]', 'Email:') !!}
                                {!! Form::email('recipient[email]', Auth::user()->email, ['class' => 'form-control', 'id' => 'recipient_email', 'required' => true]) !!}
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('recipient[phone]', 'Phone Number:') !!}
                                {!! Form::tel('recipient[phone]', auth()->user()->phone ?? null, ['class' => 'form-control', 'id' => 'recipient_phone', 'placeholder' => '+237XXXXXXXXX', 'required']) !!}
                                <small class="text-muted">Required - Include country code (e.g., +237)</small>
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('recipient[country]', 'Country (ISO Code):') !!}
                                {!! Form::text('recipient[country]', 'CM', ['class' => 'form-control', 'id' => 'recipient_country', 'maxlength' => '2', 'placeholder' => 'e.g., CM, NG, KE', 'required' => true]) !!}
                                <small class="text-muted">2-letter ISO country code</small>
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('recipient[dob]', 'Date of Birth:') !!}
                                {!! Form::date('recipient[dob]', null, ['class' => 'form-control', 'id' => 'recipient_dob', 'max' => date('Y-m-d'), 'required' => true]) !!}
                                <small class="text-muted">Select your date of birth</small>
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('recipient[address]', 'Address:') !!}
                                {!! Form::text('recipient[address]', null, ['class' => 'form-control', 'id' => 'recipient_address', 'required' => true]) !!}
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('recipient[idType]', 'ID Type:') !!}
                                {!! Form::select('recipient[idType]', [
                                    'national_id' => 'National ID',
                                    'passport' => 'Passport',
                                    'voters_card' => "Voter's Card"
                                ], null, ['class' => 'form-control', 'id' => 'recipient_idType', 'required' => true]) !!}
                                <small class="text-muted" id="idType-hint"></small>
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('recipient[idNumber]', 'ID Number:') !!}
                                {!! Form::text('recipient[idNumber]', null, ['class' => 'form-control', 'id' => 'recipient_idNumber', 'required' => true]) !!}
                            </div>

                            <!-- Nigeria Additional ID Fields (Required for NG) -->
                            <div class="form-group col-md-6 ng-fields" style="display: none;">
                                {!! Form::label('recipient[additionalIdType]', 'Additional ID Type (Required for Nigeria):') !!}
                                {!! Form::select('recipient[additionalIdType]', [
                                    '' => '-- Select --',
                                    'BVN' => 'BVN',
                                    'NIN' => 'NIN'
                                ], null, ['class' => 'form-control', 'id' => 'recipient_additionalIdType']) !!}
                                <small class="text-muted" id="additionalIdType-hint"></small>
                            </div>

                            <div class="form-group col-md-6 ng-fields" style="display: none;">
                                {!! Form::label('recipient[additionalIdNumber]', 'Additional ID Number (Required for Nigeria):') !!}
                                {!! Form::text('recipient[additionalIdNumber]', null, ['class' => 'form-control', 'id' => 'recipient_additionalIdNumber']) !!}
                            </div>
                        </div>
                    </div>

                    <!-- Institution Customer Fields -->
                    <div id="institution-fields" style="display: none;">
                        <div class="row">
                            <div class="form-group col-md-6">
                                {!! Form::label('recipient[businessName]', 'Business Name:') !!}
                                {!! Form::text('recipient[businessName]', null, ['class' => 'form-control', 'id' => 'recipient_businessName', 'required' => true]) !!}
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('recipient[businessId]', 'Business ID/Registration:') !!}
                                {!! Form::text('recipient[businessId]', null, ['class' => 'form-control', 'id' => 'recipient_businessId', 'required' => true]) !!}
                            </div>

                            <div class="form-group col-md-6">
                                {!! Form::label('institution_phone', 'Business Phone:') !!}
                                {!! Form::tel('recipient[phone]', null, ['class' => 'form-control business-phone', 'id' => 'institution_phone', 'required' => true]) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Options -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cog mr-2"></i>Additional Options</h3>
                </div>
                <div class="card-body">
                    <div class="form-check">
                        {!! Form::checkbox('forceAccept', true, false, ['class' => 'form-check-input', 'id' => 'forceAccept']) !!}
                        {!! Form::label('forceAccept', 'Skip Accept Step (Force Accept)', ['class' => 'form-check-label']) !!}
                        <small class="text-muted d-block">Automatically accept the collection without user confirmation</small>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                {!! Form::submit('Submit Collection Request', ['class' => 'btn btn-success btn-lg']) !!}
                <a href="{{ url()->previous() }}" class="btn btn-secondary btn-lg ml-2">Cancel</a>
            </div>

            {!! Form::close() !!}
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Parse channels and networks from controller
            let channels = {!! $channels ?? '[]' !!};
            let networks = {!! $networks ?? '[]' !!};
            let availableChannels = []; // Store filtered channels for current currency

            console.log('Loaded channels:', channels.length);
            console.log('Loaded networks:', networks.length);

            // Initialize on page load
            const initialCurrency = $('#currency').val();
            if (initialCurrency) {
                populateCountries(initialCurrency);
            }

            // When currency changes, update countries
            $('#currency').on('change', function() {
                const currency = $(this).val();
                if (currency) {
                    populateCountries(currency);
                    $('#country').val('').trigger('change');
                    $('#display_accountType').empty().append('<option value="">-- Select Payment Method --</option>');
                    $('#source_accountType').val('');
                }
            });

            // When country changes, update payment methods and sync recipient country
            $('#country').on('change', function() {
                const country = $(this).val();
                const currency = $('#currency').val();
                
                // Sync with recipient country
                if (country) {
                    $('#recipient_country').val(country).trigger('input');
                }
                
                if (country && currency) {
                    populatePaymentMethods(currency, country);
                }
            });

            // When payment method changes, select the appropriate channel
            $('#display_accountType').on('change', function() {
                const displayType = $(this).val();
                const country = $('#country').val();
                const currency = $('#currency').val();
                
                if (displayType && country && currency) {
                    selectChannel(currency, country, displayType);
                    
                    // Set the actual accountType for the API
                    // If it's p2p, we'll use channelType as default
                    // If it's bank or momo, we set it directly
                    if (displayType === 'p2p') {
                        // For p2p, default to bank (often true for NG)
                        $('#source_accountType').val('bank');
                    } else if (displayType === 'momo') {
                        $('#source_accountType').val('momo');
                    } else {
                        $('#source_accountType').val(displayType);
                    }
                    
                    // Note: networkId is now handled dynamically on the backend
                    $('#network-group').hide();
                    $('#source_networkId').prop('required', false);
                }
            });

            // When network changes, refine the accountType if needed
            $('#source_networkId').on('change', function() {
                const networkId = $(this).val();
                const network = networks.find(n => n.id === networkId);
                
                if (network) {
                    const networkType = (network.accountNumberType || '').toLowerCase();
                    if (networkType === 'momo' || networkType === 'mobilemoney' || 
                        (network.name || '').toLowerCase().includes('mobile money')) {
                        $('#source_accountType').val('momo');
                    } else {
                        $('#source_accountType').val('bank');
                    }
                }
            });

            // Toggle amount field name based on type
            $('#amount_type').on('change', function() {
                const amountField = $('#amount_field');
                const amountHint = $('#amount-hint');
                
                if ($(this).val() === 'usd') {
                    amountField.attr('name', 'amount');
                    amountHint.text('Enter the amount in USD');
                } else {
                    amountField.attr('name', 'localAmount');
                    amountHint.text('Enter the amount in local currency');
                }
            });

            // Toggle customer type fields
            $('#customerType').on('change', function() {
                if ($(this).val() === 'retail') {
                    $('#retail-fields').show();
                    $('#institution-fields').hide();
                    toggleRequiredFields('#retail-fields', true);
                    toggleRequiredFields('#institution-fields', false);
                } else {
                    $('#retail-fields').hide();
                    $('#institution-fields').show();
                    toggleRequiredFields('#retail-fields', false);
                    toggleRequiredFields('#institution-fields', true);
                }
            });

            // Trigger change to set initial state
            $('#customerType').trigger('change');

            // Form submission validation
            $('#yellowcard-collection-form').on('submit', function(e) {
                const country = $('#recipient_country').val().toUpperCase();
                
                // Validate Nigeria-specific requirements
                if (country === 'NG') {
                    const idType = $('#recipient_idType').val();
                    const additionalIdType = $('#recipient_additionalIdType').val();
                    const additionalIdNumber = $('#recipient_additionalIdNumber').val();
                    
                    // Check if both NIN and BVN are provided
                    if (!idType || !additionalIdType) {
                        e.preventDefault();
                        alert('For Nigeria: Both ID Type and Additional ID Type are required. You must provide both NIN and BVN.');
                        return false;
                    }
                    
                    // Check if they are different
                    if (idType === additionalIdType) {
                        e.preventDefault();
                        alert('For Nigeria: ID Type and Additional ID Type must be different. One must be NIN and the other must be BVN.');
                        return false;
                    }
                    
                    // Check if we have both NIN and BVN
                    const hasNIN = idType === 'NIN' || additionalIdType === 'NIN';
                    const hasBVN = idType === 'BVN' || additionalIdType === 'BVN';
                    
                    if (!hasNIN || !hasBVN) {
                        e.preventDefault();
                        alert('For Nigeria: You must provide both NIN and BVN. One as ID Type and the other as Additional ID Type.');
                        return false;
                    }
                    
                    // Check if additional ID number is provided
                    if (!additionalIdNumber || additionalIdNumber.trim() === '') {
                        e.preventDefault();
                        alert('For Nigeria: Additional ID Number is required.');
                        return false;
                    }
                }
                
                return true;
            });

            // Handle Nigeria-specific fields
            $('#recipient_country').on('input', function() {
                const country = $(this).val().toUpperCase();
                if (country === 'NG') {
                    // Show additional ID fields
                    $('.ng-fields').show();
                    
                    // Make additional ID fields required for Nigeria
                    $('#recipient_additionalIdType').prop('required', true);
                    $('#recipient_additionalIdNumber').prop('required', true);
                    
                    // Update ID Type options - Nigeria requires NIN or BVN
                    updateNigeriaIdOptions();
                } else {
                    // Hide additional ID fields
                    $('.ng-fields').hide();
                    
                    // Make additional ID fields optional for non-Nigeria countries
                    $('#recipient_additionalIdType').prop('required', false);
                    $('#recipient_additionalIdNumber').prop('required', false);
                    
                    // Reset ID Type options to default
                    resetDefaultIdOptions();
                }
            });
            
            // Function to update ID Type options for Nigeria
            function updateNigeriaIdOptions() {
                const $idType = $('#recipient_idType');
                const $additionalIdType = $('#recipient_additionalIdType');
                const currentValue = $idType.val();
                
                // Update ID Type dropdown to only NIN and BVN
                $idType.empty();
                $idType.append('<option value="">-- Select ID Type --</option>');
                $idType.append('<option value="NIN">NIN (National Identification Number)</option>');
                $idType.append('<option value="BVN">BVN (Bank Verification Number)</option>');
                
                // Restore selection if it was NIN or BVN
                if (currentValue === 'NIN' || currentValue === 'BVN') {
                    $idType.val(currentValue);
                }
                
                // Reset Additional ID Type dropdown
                $additionalIdType.empty();
                $additionalIdType.append('<option value="">-- Select --</option>');
                
                // If idType is already selected, filter additionalIdType options
                if (currentValue === 'NIN') {
                    $additionalIdType.append('<option value="BVN">BVN (Bank Verification Number)</option>');
                } else if (currentValue === 'BVN') {
                    $additionalIdType.append('<option value="NIN">NIN (National Identification Number)</option>');
                } else {
                    // Show both if nothing selected yet
                    $additionalIdType.append('<option value="BVN">BVN</option>');
                    $additionalIdType.append('<option value="NIN">NIN</option>');
                }
                
                $('#idType-hint').text('For Nigeria: Choose NIN or BVN. The other must be provided as Additional ID Type.');
            }
            
            // Function to reset ID Type options to default
            function resetDefaultIdOptions() {
                const $idType = $('#recipient_idType');
                const currentValue = $idType.val();
                
                // Reset to default options
                $idType.empty();
                $idType.append('<option value="">-- Select ID Type --</option>');
                $idType.append('<option value="national_id">National ID</option>');
                $idType.append('<option value="passport">Passport</option>');
                $idType.append('<option value="voters_card">Voter\'s Card</option>');
                
                // Try to restore selection if it still exists
                if (['national_id', 'passport', 'voters_card'].includes(currentValue)) {
                    $idType.val(currentValue);
                }
                
                $('#idType-hint').text('');
            }
            
            // Ensure Nigeria ID Type and Additional ID Type are complementary
            $('#recipient_idType').on('change', function() {
                const country = $('#recipient_country').val().toUpperCase();
                if (country !== 'NG') return;
                
                const idType = $(this).val();
                const $additionalIdType = $('#recipient_additionalIdType');
                const currentAdditionalValue = $additionalIdType.val();
                
                // Update additionalIdType options based on idType selection
                $additionalIdType.empty();
                $additionalIdType.append('<option value="">-- Select --</option>');
                
                if (idType === 'NIN') {
                    // If NIN is selected, only BVN is available for additional
                    $additionalIdType.append('<option value="BVN">BVN (Bank Verification Number)</option>');
                    // Auto-select BVN if nothing was selected
                    if (!currentAdditionalValue || currentAdditionalValue === 'NIN') {
                        $additionalIdType.val('BVN');
                    }
                } else if (idType === 'BVN') {
                    // If BVN is selected, only NIN is available for additional
                    $additionalIdType.append('<option value="NIN">NIN (National Identification Number)</option>');
                    // Auto-select NIN if nothing was selected
                    if (!currentAdditionalValue || currentAdditionalValue === 'BVN') {
                        $additionalIdType.val('NIN');
                    }
                } else {
                    // If nothing selected, show both options
                    $additionalIdType.append('<option value="BVN">BVN</option>');
                    $additionalIdType.append('<option value="NIN">NIN</option>');
                }
                
                // Update hints
                updateNigeriaIdHints();
            });
            
            // Update hints when additional ID type changes
            $('#recipient_additionalIdType').on('change', function() {
                const country = $('#recipient_country').val().toUpperCase();
                if (country !== 'NG') return;
                updateNigeriaIdHints();
            });
            
            // Function to update Nigeria ID hints
            function updateNigeriaIdHints() {
                const idType = $('#recipient_idType').val();
                const additionalIdType = $('#recipient_additionalIdType').val();
                
                // Update hint if both are selected and they're the same
                if (idType && additionalIdType && idType === additionalIdType) {
                    $('#additionalIdType-hint').text('⚠️ Additional ID Type must be different from ID Type').css('color', 'red');
                } else if (idType && additionalIdType) {
                    $('#additionalIdType-hint').text('✓ Both NIN and BVN provided').css('color', 'green');
                } else if (idType || additionalIdType) {
                    $('#additionalIdType-hint').text('You must provide both NIN and BVN for Nigeria').css('color', 'orange');
                } else {
                    $('#additionalIdType-hint').text('');
                }
            }

            // Handle amount type toggle
            $('#amount_type').on('change', function() {
                const type = $(this).val();
                if (type === 'usd') {
                    $('#amount').attr('name', 'amount');
                    $('#amount-hint').text('Enter amount in USD');
                } else {
                    $('#amount').attr('name', 'localAmount');
                    $('#amount-hint').text('Enter amount in local currency');
                }
            });

            // Populate countries dropdown based on currency
            function populateCountries(currency) {
                const $countrySelect = $('#country');
                $countrySelect.empty().append('<option value="">-- Select Country --</option>');
                
                // Filter channels for selected currency and deposit type
                availableChannels = channels.filter(ch => 
                    ch.currency === currency && 
                    ch.rampType === 'deposit' && 
                    (ch.apiStatus === 'active' || ch.status === 'active')
                );
                
                // Get unique countries
                const countries = [...new Set(availableChannels.map(ch => ch.country))];
                
                console.log('Available countries for ' + currency + ':', countries);
                
                if (countries.length === 0) {
                    $countrySelect.append('<option value="" disabled>No countries available for ' + currency + '</option>');
                } else {
                    countries.sort().forEach(country => {
                        $countrySelect.append(`<option value="${country}">${getCountryName(country)}</option>`);
                    });
                }
            }

            // Populate payment methods based on currency and country
            function populatePaymentMethods(currency, country) {
                const $methodSelect = $('#display_accountType');
                $methodSelect.empty().append('<option value="">-- Select Payment Method --</option>');
                
                // Filter channels for selected currency and country
                const countryChannels = availableChannels.filter(ch => ch.country === country);
                
                // Get unique channel types
                const methods = [...new Set(countryChannels.map(ch => ch.channelType))];
                
                console.log('Available methods for ' + country + ':', methods);
                
                methods.forEach(method => {
                    const methodName = method === 'momo' ? 'Mobile Money' : 
                                     method === 'bank' ? 'Bank Transfer' : 
                                     method.charAt(0).toUpperCase() + method.slice(1);
                    $methodSelect.append(`<option value="${method}">${methodName}</option>`);
                });
            }

            // Select appropriate channel based on selections
            function selectChannel(currency, country, accountType) {
                const matchingChannel = availableChannels.find(ch => 
                    ch.currency === currency && 
                    ch.country === country && 
                    ch.channelType === accountType
                );
                
                if (matchingChannel) {
                    $('#channelId').val(matchingChannel.id);
                    console.log('Selected channel:', matchingChannel.id, matchingChannel);
                } else {
                    $('#channelId').val('');
                    console.warn('No matching channel found');
                }
            }


            // Get country name from code
            function getCountryName(code) {
                const countryNames = {
                    'CM': 'Cameroon',
                    'GA': 'Gabon',
                    'CG': 'Congo (Brazzaville)',
                    'NG': 'Nigeria',
                    'KE': 'Kenya',
                    'GH': 'Ghana',
                    'UG': 'Uganda',
                    'TZ': 'Tanzania',
                    'ZA': 'South Africa',
                    'RW': 'Rwanda',
                    'ZM': 'Zambia',
                    'MW': 'Malawi',
                    'BW': 'Botswana',
                    'CD': 'Congo (DRC)',
                    'CI': 'Ivory Coast',
                    'SN': 'Senegal',
                    'TG': 'Togo',
                    'BJ': 'Benin',
                    'BF': 'Burkina Faso'
                };
                return countryNames[code] || code;
            }

            // Toggle required and disabled attributes
            function toggleRequiredFields(selector, active) {
                $(selector).find('input, select').each(function() {
                    if (active) {
                        $(this).removeAttr('disabled');
                    } else {
                        $(this).attr('disabled', 'disabled');
                    }
                });
            }
        });
    </script>

    <style>
        .card {
            margin-bottom: 2rem;
            transition: transform 0.2s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
        }

        .card-header h3 {
            font-size: 1.1rem;
            margin-bottom: 0;
            display: flex;
            align-items: center;
        }

        .card-header i {
            color: var(--primary-lilac);
            width: 24px;
        }

        #amount-hint {
            font-size: 0.8rem;
            margin-top: 0.5rem;
            color: var(--text-body);
        }

        .btn-success {
            padding: 1rem 2rem;
            font-size: 1.1rem;
        }
    </style>
@endsection
