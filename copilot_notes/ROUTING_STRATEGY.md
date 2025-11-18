# Payment Routing Strategy

## Overview
This document outlines the routing strategy for payments and currency conversions in the Nuage Money API.

## Key Architectural Decision

### Problem Identified
The initial implementation incorrectly assumed that payout requests would contain a `source_currency` field, leading to currency conversion logic being applied in the payment router. However, **payout requests only contain a single `currency` field** (the destination currency).

### Solution: Separation of Concerns

We've separated the routing logic into two distinct routers:

1. **PaymentRouter** - Handles payouts (no currency conversion)
2. **ConversionRouter** - Handles currency conversions (with rate comparison)

---

## PaymentRouter (app/Classes/PaymentRouter.php)

### Purpose
Routes payout requests to the most appropriate Payment Service Provider (PSP) based on:
- Metadata requirements
- Provider capabilities  
- Provider configuration/availability

### Routing Strategy
**Priority-based selection** (no fee comparison at this time):

1. **VALR** - For crypto payouts
   - Requires: `crypto_address` and `crypto_currency` in metadata
   
2. **Bridge** - For multi-rail payouts (bank, wallet, crypto)
   - Requires: `dest_external_account_id` OR `dest_wallet_id` OR `dest_crypto_address`
   
3. **Fincra** - For fiat bank/mobile money payouts
   - Requires: (`bank_code` AND `dest_account_number`) OR (`MNO` AND `msisdn`)
   
4. **StartButton** - Fallback for bank/mobile money payouts
   - Requires: (`bank_code` AND `dest_account_number`) OR (`MNO` AND `msisdn`)

### Key Methods

```php
public function routePayout(array $data): JsonResponse
```
Main entry point that validates metadata and routes to the selected provider.

```php
private function selectBestProvider(array $data): string
```
Selects provider based on priority order and metadata requirements.

```php
public function getAvailableProviders(array $data): array
```
Returns list of configured providers that can handle the given payout based on metadata.

### Important Notes
- **No currency conversion** happens in PaymentRouter
- **No fee comparison** at this time (future enhancement)
- Payout requests have only ONE currency field
- Each provider has graceful fallback to StartButton on failure

---

## ConversionRouter (app/Classes/ConversionRouter.php)

### Purpose
Routes currency conversion requests to the provider offering the **best exchange rate**.

### Routing Strategy
**Rate-based selection** using `RateComparisonService`:

1. Queries all configured providers for rates
2. Selects provider with best rate for the currency pair
3. Routes conversion to that provider

### Supported Providers
1. **Fincra** - Fully implemented with quote/conversion API
2. **Bridge** - Not yet implemented (requires wallet-to-wallet transfers)
3. **VALR** - Not yet implemented (requires instant order API)
4. **StartButton** - Not available (no conversion API)

### Key Methods

```php
public function routeConversion(array $data): JsonResponse
```
Main entry point that gets best rate and executes conversion.

```php
private function convertViaFincra(array $data, array $rateInfo): JsonResponse
```
Executes conversion via Fincra, updates wallet balances, and records transaction.

```php
private function updateWalletBalances(array $data, float $amountReceived): void
```
Decreases source currency wallet, increases destination currency wallet.

### Conversion Flow
1. Extract `source_currency` and `dest_currency` from metadata
2. Get best rate from RateComparisonService
3. Execute conversion via selected provider
4. Record transaction in `Achat` table
5. Update user wallet balances

---

## Webhook Processing (app/Jobs/ProcessRehiveWebhook.php)

### Event Types

#### 1. WITHDRAW_MANUAL (Payouts)
```php
case RehiveEventType::WITHDRAW_MANUAL:
    $paymentRouter = new PaymentRouter();
    $result = $paymentRouter->routePayout($data);
```
- Uses **PaymentRouter**
- Single currency field from webhook: `$data['currency']`
- Routing based on metadata requirements

#### 2. CONVERSION (Currency Conversions)
```php
case RehiveEventType::CONVERSION:
    $conversionRouter = new ConversionRouter();
    $result = $conversionRouter->routeConversion($data);
```
- Uses **ConversionRouter**
- Requires `source_currency` and `dest_currency` in metadata
- Routing based on best exchange rate

---

## Future Enhancements

### 1. Fee-Based Routing for Payouts
Currently, PaymentRouter uses priority-based routing. Future enhancement could include:
- Query each PSP's transfer/payout fee API
- Calculate total cost (fee + FX spread if applicable)
- Select provider with lowest cost

**Challenge**: Not all PSPs expose fee information via API before execution.

### 2. Provider Fee APIs
Research needed for:
- **VALR**: Withdrawal fee structure
- **Bridge**: Transfer fee information
- **Fincra**: Payout fee query API
- **StartButton**: Fee information availability

### 3. Rate Caching
Implement caching for exchange rates to reduce API calls:
- Cache rates for 1-5 minutes
- Refresh on cache miss
- Consider rate volatility for cache duration

### 4. Provider Health Monitoring
Track provider success rates and response times:
- Automatic fallback on consistent failures
- Circuit breaker pattern implementation
- Provider performance dashboard

---

## Testing Strategy

### PaymentRouter Tests
- Test provider selection based on metadata
- Test fallback behavior when providers fail
- Test behavior with missing/incomplete metadata

### ConversionRouter Tests
- Test rate comparison across providers
- Test wallet balance updates
- Test conversion recording in database
- Test fallback when best provider fails

### Integration Tests
- Test full webhook → router → PSP flow
- Test multi-provider scenarios
- Test error handling and logging

---

## Related Files
- `app/Classes/PaymentRouter.php` - Payout routing
- `app/Classes/ConversionRouter.php` - Conversion routing
- `app/Jobs/ProcessRehiveWebhook.php` - Webhook processing
- `app/Services/RateComparisonService.php` - Rate comparison logic
- `app/Classes/ValrPaymentHelper.php` - VALR integration
- `app/Classes/BridgePaymentHelper.php` - Bridge integration
- `app/Classes/FincraPaymentHelper.php` - Fincra integration
- `app/Classes/StartButtonAfricaPaymentHelper.php` - StartButton integration

---

## Summary

**Payouts**: Routed by PaymentRouter based on metadata requirements (not fees or rates)
**Conversions**: Routed by ConversionRouter based on best exchange rate

This separation ensures:
✅ Clear separation of concerns
✅ Correct handling of single-currency payout requests
✅ Optimal rate selection for currency conversions
✅ Flexible architecture for future fee-based routing
