# VALR Crypto Withdrawal Whitelist Troubleshooting Guide

## Error: "The withdraw address is not whitelisted"

Even if the address appears in your address book, this error can occur due to several reasons:

---

## Common Causes & Solutions

### 1. **Address Format Issues**

#### Problem:
- Extra whitespace (spaces, tabs, newlines)
- Wrong case sensitivity
- Invisible Unicode characters

#### Solution:
```bash
# Use the validation endpoint to check
POST /api/valr/crypto/validate-address
{
  "address": "YOUR_ADDRESS_HERE",
  "currencyCode": "USDT"
}
```

**Fix**: The service now automatically trims whitespace. Check logs for `addressLength` vs `addressTrimmedLength`.

---

### 2. **Currency Mismatch**

#### Problem:
The address is whitelisted for **BTC** but you're trying to withdraw **USDT**.

#### Solution:
Check the address book entry:
```bash
GET /api/valr/crypto/address-book
```

Look for:
```json
{
  "address": "0x123...",
  "currency": "USDT",  // <- Must match withdrawal currency
  "status": "APPROVED"
}
```

---

### 3. **Network Type Mismatch** ⚠️ MOST COMMON

#### Problem:
For currencies like **USDT** that support multiple networks:
- Address whitelisted for **ERC20** (Ethereum)
- Trying to withdraw on **TRC20** (Tron)
- Or vice versa

#### Solution:
**Always specify the `networkType` when withdrawing USDT or other multi-network tokens:**

```json
{
  "currencyCode": "USDT",
  "amount": "10",
  "address": "0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb",
  "networkType": "ERC20",  // <- REQUIRED for USDT!
  "beneficiaryName": "My Wallet",
  "isCorporate": false,
  "isSelfHosted": true,
  "serviceProviderName": "Personal"
}
```

Check the address book for the network:
```json
{
  "address": "0x123...",
  "currency": "USDT",
  "network": "ERC20",  // <- Must match
  "status": "APPROVED"
}
```

---

### 4. **Address Status Not Approved**

#### Problem:
Address exists in address book but not yet approved.

#### Solution:
Check `status` field:
```json
{
  "address": "0x123...",
  "status": "PENDING"  // <- Not APPROVED yet!
}
```

Wait for VALR to approve the address (usually 24-48 hours).

---

### 5. **Wrong Service Provider**

#### Problem:
The `serviceProviderName` might need to match exactly what's in the address book.

#### Solution:
Compare your request with the address book entry:

**Address Book:**
```json
{
  "serviceProvider": "Personal Wallet"
}
```

**Your Request:**
```json
{
  "serviceProviderName": "Personal Wallet"  // <- Must match exactly
}
```

---

### 6. **Address Book ID vs Address** (Advanced)

#### Problem:
Some APIs require the address book entry ID instead of the actual address.

#### Solution:
Check if VALR requires an `addressBookId` field instead of or in addition to `address`:

```json
{
  "addressBookId": "abc-123-def",  // <- ID from address book
  "address": "0x123...",
  "amount": "10"
}
```

---

## Debugging Steps

### Step 1: Get Address Book
```bash
GET /api/valr/crypto/address-book
```

Save the response and examine each entry.

---

### Step 2: Validate Your Address
```bash
POST /api/valr/crypto/validate-address
{
  "address": "YOUR_EXACT_ADDRESS",
  "currencyCode": "USDT"
}
```

This will tell you if the address is found and return the matching entry.

---

### Step 3: Check Enhanced Logs

After the validation runs, check your Laravel logs for:

```
VALR validateWhitelistedAddress: Address found in whitelist
VALR makeCryptoWithdrawal: Address validated in whitelist
```

Or:

```
VALR validateWhitelistedAddress: Address NOT found in whitelist
```

The logs will also show:
- `addressLength` - Original address length
- `addressTrimmedLength` - After trimming
- `hasWhitespace` - Whether whitespace was detected

---

### Step 4: Compare Exact Values

Use the validation response to compare:

**From Address Book:**
```json
{
  "address": "0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb",
  "currency": "USDT",
  "network": "ERC20"
}
```

**Your Withdrawal Request:**
```json
{
  "address": "0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb",  // Exact match?
  "currencyCode": "USDT",  // Exact match?
  "networkType": "ERC20"   // Exact match?
}
```

---

## Quick Checklist

- [ ] Address exactly matches (no spaces, correct case)
- [ ] Currency code matches
- [ ] Network type specified and matches (for USDT, XRP, etc.)
- [ ] Address status is "APPROVED" in address book
- [ ] Service provider name matches
- [ ] No invisible characters in address
- [ ] Tried copying address directly from address book API response

---

## Test Endpoint

Use this endpoint to test before making actual withdrawals:

```bash
POST /api/valr/crypto/validate-address

Request:
{
  "address": "0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb",
  "currencyCode": "USDT"
}

Success Response:
{
  "success": true,
  "message": "Address is whitelisted",
  "data": {
    "address": "0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb",
    "currency": "USDT",
    "isWhitelisted": true,
    "addressBookEntry": {
      // Full entry from address book
    }
  }
}

Failure Response:
{
  "success": false,
  "message": "Address is not whitelisted",
  "data": {
    "address": "0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb",
    "currency": "USDT",
    "isWhitelisted": false
  }
}
```

---

## Still Not Working?

Contact VALR support with:
1. Address book API response
2. Your withdrawal request payload
3. Error response from VALR
4. Screenshots showing address in address book

They can verify on their end if the address is truly whitelisted.
