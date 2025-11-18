# Postman Test Payloads for Nuage Money API

## Important: Routing Architecture

**Two Separate Routers:**
1. **PaymentRouter** - Handles payouts (withdraw_manual)
   - Routes based on metadata requirements (NOT exchange rates)
   - Priority: VALR (crypto) → Bridge (multi-rail) → Fincra (fiat) → StartButton (fallback)
   - Single currency per request

2. **ConversionRouter** - Handles currency conversions
   - Routes based on BEST EXCHANGE RATE using RateComparisonService
   - Requires `source_currency` and `dest_currency` in metadata
   - Updates wallet balances

**See `ROUTING_STRATEGY.md` for detailed documentation.**

---

## Base Configuration

**Endpoint:** `POST http://your-domain.com/webhook/rehive`

**Headers:**
```json
{
  "Content-Type": "application/json",
  "Accept": "application/json"
}
```

---

## 1. Withdraw/Payout - With Complete Metadata (Bank Account)

### ✅ Should Route Based on Metadata Requirements

```json
{
  "id": 96325610,
  "event": "transaction.create",
  "company": "nuage_bridge_test",
  "data": {
    "id": "2509dc18-5b1e-48f8-b966-615e8e2b4024",
    "fee": 0,
    "fees": [],
    "note": null,
    "user": {
      "id": "f21e486e-6df9-472e-a6e9-7aa3f14851e5",
      "email": "helghardt+001@gmail.com",
      "groups": [
        {
          "name": "individual",
          "label": "Individual",
          "section": "user"
        }
      ],
      "mobile": null,
      "profile": null,
      "username": "helghardt001",
      "last_name": "Avenant",
      "temporary": false,
      "first_name": "Helghardt",
      "middle_name": null
    },
    "index": 0,
    "label": "Withdraw manual",
    "amount": -10000,
    "parent": null,
    "status": "Initiating",
    "account": "TU6VZM4BTJ",
    "balance": 0,
    "created": 1731497722757,
    "creator": {
      "id": "f4d69547-ec0d-48ca-a7a1-aa461cbd8548",
      "email": "mtawamba@nuage.money",
      "groups": [
        {
          "name": "admin",
          "label": "Admin",
          "section": "admin"
        }
      ],
      "mobile": null,
      "profile": null,
      "username": null,
      "last_name": null,
      "temporary": false,
      "first_name": null,
      "middle_name": null
    },
    "expires": 1731498322756,
    "partner": null,
    "subtype": "withdraw_manual",
    "tx_type": "debit",
    "updated": 1731497722756,
    "archived": false,
    "currency": {
      "code": "NGN",
      "icon": null,
      "unit": "naira",
      "symbol": "₦",
      "description": "Nigerian Naira",
      "display_code": "NGN",
      "divisibility": 2
    },
    "executed": null,
    "metadata": {
      "location": "NG",
      "bank_code": "044",
      "dest_account_number": "0123456789",
      "dest_account_name": "Helghardt Avenant"
    },
    "inclusive": false,
    "reference": null,
    "collection": "9a37d260-ff12-4d2e-b5c2-116a6c852252",
    "total_amount": -10000,
    "account_currency": {
      "id": "875c90fa-65b5-4de8-b67d-acf4c3ac7cfc",
      "account": {
        "name": "general",
        "label": "General",
        "primary": true,
        "reference": "TU6VZM4BTJ",
        "definition": "general"
      },
      "currency": {
        "code": "NGN",
        "icon": null,
        "unit": "naira",
        "symbol": "₦",
        "description": "Nigerian Naira",
        "display_code": "NGN",
        "divisibility": 2
      }
    }
  }
}
```

**Expected Result:**
- ✅ Validates metadata (has bank_code + dest_account_number)
- ✅ Priority check: VALR (no crypto_address) → Bridge (no dest_account_id) → Fincra/StartButton ✓
- ✅ Routes to Fincra or StartButton (both support bank transfers)
- ✅ Executes payout

---

## 2. Withdraw/Payout - Mobile Money

### ✅ Should Route to Provider Supporting Mobile Money

```json
{
  "id": 96325611,
  "event": "transaction.create",
  "company": "nuage_bridge_test",
  "data": {
    "id": "3519dc18-5b1e-48f8-b966-615e8e2b4025",
    "fee": 0,
    "fees": [],
    "note": null,
    "user": {
      "id": "f21e486e-6df9-472e-a6e9-7aa3f14851e5",
      "email": "helghardt+001@gmail.com",
      "groups": [
        {
          "name": "individual",
          "label": "Individual",
          "section": "user"
        }
      ],
      "mobile": "+237670000000",
      "profile": null,
      "username": "helghardt001",
      "last_name": "Avenant",
      "temporary": false,
      "first_name": "Helghardt",
      "middle_name": null
    },
    "index": 0,
    "label": "Withdraw manual",
    "amount": -5000,
    "parent": null,
    "status": "Initiating",
    "account": "TU6VZM4BTJ",
    "balance": 0,
    "created": 1731497722757,
    "creator": {
      "id": "f4d69547-ec0d-48ca-a7a1-aa461cbd8548",
      "email": "mtawamba@nuage.money",
      "groups": [
        {
          "name": "admin",
          "label": "Admin",
          "section": "admin"
        }
      ],
      "mobile": null,
      "profile": null,
      "username": null,
      "last_name": null,
      "temporary": false,
      "first_name": null,
      "middle_name": null
    },
    "expires": 1731498322756,
    "partner": null,
    "subtype": "withdraw_manual",
    "tx_type": "debit",
    "updated": 1731497722756,
    "archived": false,
    "currency": {
      "code": "XAF",
      "icon": null,
      "unit": "beac",
      "symbol": "FCFA",
      "description": "CFA Franc BEAC",
      "display_code": "XAF",
      "divisibility": 0
    },
    "executed": null,
    "metadata": {
      "location": "CMR",
      "MNO": "MTN",
      "msisdn": "+237670000000"
    },
    "inclusive": false,
    "reference": null,
    "collection": "9a37d260-ff12-4d2e-b5c2-116a6c852252",
    "total_amount": -5000,
    "account_currency": {
      "id": "875c90fa-65b5-4de8-b67d-acf4c3ac7cfc",
      "account": {
        "name": "general",
        "label": "General",
        "primary": true,
        "reference": "TU6VZM4BTJ",
        "definition": "general"
      },
      "currency": {
        "code": "XAF",
        "icon": null,
        "unit": "beac",
        "symbol": "FCFA",
        "description": "CFA Franc BEAC",
        "display_code": "XAF",
        "divisibility": 0
      }
    }
  }
}
```

**Expected Result:**
- ✅ Validates mobile money metadata (has MNO + msisdn)
- ✅ Routes to provider supporting XAF mobile money (Fincra or StartButton)
- ✅ Executes payout

---

## 3. Withdraw/Payout - NULL Metadata

### ⚠️ Should Default to StartButton (Will Fail Gracefully)

```json
{
  "id": 96325612,
  "event": "transaction.create",
  "company": "nuage_bridge_test",
  "data": {
    "id": "4519dc18-5b1e-48f8-b966-615e8e2b4026",
    "fee": 0,
    "fees": [],
    "note": null,
    "user": {
      "id": "f21e486e-6df9-472e-a6e9-7aa3f14851e5",
      "email": "helghardt+001@gmail.com",
      "groups": [
        {
          "name": "individual",
          "label": "Individual",
          "section": "user"
        }
      ],
      "mobile": null,
      "profile": null,
      "username": "helghardt001",
      "last_name": "Avenant",
      "temporary": false,
      "first_name": "Helghardt",
      "middle_name": null
    },
    "index": 0,
    "label": "Withdraw manual",
    "amount": -50,
    "parent": null,
    "status": "Initiating",
    "account": "TU6VZM4BTJ",
    "balance": 0,
    "created": 1731497722757,
    "creator": {
      "id": "f4d69547-ec0d-48ca-a7a1-aa461cbd8548",
      "email": "mtawamba@nuage.money",
      "groups": [
        {
          "name": "admin",
          "label": "Admin",
          "section": "admin"
        }
      ],
      "mobile": null,
      "profile": null,
      "username": null,
      "last_name": null,
      "temporary": false,
      "first_name": null,
      "middle_name": null
    },
    "expires": 1731498322756,
    "partner": null,
    "subtype": "withdraw_manual",
    "tx_type": "debit",
    "updated": 1731497722756,
    "archived": false,
    "currency": {
      "code": "XAF",
      "icon": null,
      "unit": "beac",
      "symbol": "FCFA",
      "description": "CFA Franc BEAC",
      "display_code": "XAF",
      "divisibility": 0
    },
    "executed": null,
    "metadata": null,
    "inclusive": false,
    "reference": null,
    "collection": "9a37d260-ff12-4d2e-b5c2-116a6c852252",
    "total_amount": -50,
    "account_currency": {
      "id": "875c90fa-65b5-4de8-b67d-acf4c3ac7cfc",
      "account": {
        "name": "general",
        "label": "General",
        "primary": true,
        "reference": "TU6VZM4BTJ",
        "definition": "general"
      },
      "currency": {
        "code": "XAF",
        "icon": null,
        "unit": "beac",
        "symbol": "FCFA",
        "description": "CFA Franc BEAC",
        "display_code": "XAF",
        "divisibility": 0
      }
    }
  }
}
```

**Expected Result:**
- ⚠️ Detects NULL metadata
- ⚠️ Defaults to StartButton
- ❌ StartButton returns error: "Missing bank or mobile money details"

---

## 4. Withdraw/Payout - Incomplete Metadata

### ❌ Should Return Error Immediately

```json
{
  "id": 96325613,
  "event": "transaction.create",
  "company": "nuage_bridge_test",
  "data": {
    "id": "5519dc18-5b1e-48f8-b966-615e8e2b4027",
    "fee": 0,
    "fees": [],
    "note": null,
    "user": {
      "id": "f21e486e-6df9-472e-a6e9-7aa3f14851e5",
      "email": "helghardt+001@gmail.com",
      "groups": [
        {
          "name": "individual",
          "label": "Individual",
          "section": "user"
        }
      ],
      "mobile": null,
      "profile": null,
      "username": "helghardt001",
      "last_name": "Avenant",
      "temporary": false,
      "first_name": "Helghardt",
      "middle_name": null
    },
    "index": 0,
    "label": "Withdraw manual",
    "amount": -1000,
    "parent": null,
    "status": "Initiating",
    "account": "TU6VZM4BTJ",
    "balance": 0,
    "created": 1731497722757,
    "creator": {
      "id": "f4d69547-ec0d-48ca-a7a1-aa461cbd8548",
      "email": "mtawamba@nuage.money",
      "groups": [
        {
          "name": "admin",
          "label": "Admin",
          "section": "admin"
        }
      ],
      "mobile": null,
      "profile": null,
      "username": null,
      "last_name": null,
      "temporary": false,
      "first_name": null,
      "middle_name": null
    },
    "expires": 1731498322756,
    "partner": null,
    "subtype": "withdraw_manual",
    "tx_type": "debit",
    "updated": 1731497722756,
    "archived": false,
    "currency": {
      "code": "NGN",
      "icon": null,
      "unit": "naira",
      "symbol": "₦",
      "description": "Nigerian Naira",
      "display_code": "NGN",
      "divisibility": 2
    },
    "executed": null,
    "metadata": {
      "location": "NG",
      "some_random_field": "value"
    },
    "inclusive": false,
    "reference": null,
    "collection": "9a37d260-ff12-4d2e-b5c2-116a6c852252",
    "total_amount": -1000,
    "account_currency": {
      "id": "875c90fa-65b5-4de8-b67d-acf4c3ac7cfc",
      "account": {
        "name": "general",
        "label": "General",
        "primary": true,
        "reference": "TU6VZM4BTJ",
        "definition": "general"
      },
      "currency": {
        "code": "NGN",
        "icon": null,
        "unit": "naira",
        "symbol": "₦",
        "description": "Nigerian Naira",
        "display_code": "NGN",
        "divisibility": 2
      }
    }
  }
}
```

**Expected Result:**
- ❌ Validates metadata
- ❌ No provider requirements met
- ❌ Returns error: "Missing required payout destination details"

---

## 5. Currency Conversion - USDT to NGN

### ✅ Should Use ConversionRouter with Rate Comparison

```json
{
  "id": 96325614,
  "event": "transaction.create",
  "company": "nuage_bridge_test",
  "data": {
    "id": "6519dc18-5b1e-48f8-b966-615e8e2b4028",
    "fee": 0,
    "fees": [],
    "note": "Currency conversion from USDT to NGN",
    "user": {
      "id": "f21e486e-6df9-472e-a6e9-7aa3f14851e5",
      "email": "helghardt+001@gmail.com",
      "groups": [
        {
          "name": "individual",
          "label": "Individual",
          "section": "user"
        }
      ],
      "mobile": "+2348012345678",
      "profile": null,
      "username": "helghardt001",
      "last_name": "Avenant",
      "temporary": false,
      "first_name": "Helghardt",
      "middle_name": null
    },
    "index": 0,
    "label": "Currency Conversion",
    "amount": -10,
    "parent": null,
    "status": "Initiating",
    "account": "TU6VZM4BTJ",
    "balance": 50,
    "created": 1731497722757,
    "creator": {
      "id": "f21e486e-6df9-472e-a6e9-7aa3f14851e5",
      "email": "helghardt+001@gmail.com",
      "groups": [
        {
          "name": "individual",
          "label": "Individual",
          "section": "user"
        }
      ],
      "mobile": null,
      "profile": null,
      "username": null,
      "last_name": null,
      "temporary": false,
      "first_name": null,
      "middle_name": null
    },
    "expires": 1731498322756,
    "partner": null,
    "subtype": "conversion",
    "tx_type": "debit",
    "updated": 1731497722756,
    "archived": false,
    "currency": {
      "code": "USDT",
      "icon": null,
      "unit": "tether",
      "symbol": "₮",
      "description": "Tether USD",
      "display_code": "USDT",
      "divisibility": 2
    },
    "executed": null,
    "metadata": {
      "source_currency": "USDT",
      "dest_currency": "NGN",
      "conversion_amount": 10,
      "expected_rate": 1580.50
    },
    "inclusive": false,
    "reference": null,
    "collection": "9a37d260-ff12-4d2e-b5c2-116a6c852252",
    "total_amount": -10,
    "account_currency": {
      "id": "875c90fa-65b5-4de8-b67d-acf4c3ac7cfc",
      "account": {
        "name": "general",
        "label": "General",
        "primary": true,
        "reference": "TU6VZM4BTJ",
        "definition": "general"
      },
      "currency": {
        "code": "USDT",
        "icon": null,
        "unit": "tether",
        "symbol": "₮",
        "description": "Tether USD",
        "display_code": "USDT",
        "divisibility": 2
      }
    }
  }
}
```

**Expected Result:**
- ✅ Detects conversion event (subtype: "conversion")
- ✅ Routes to ConversionRouter (NOT PaymentRouter)
- ✅ Gets best rate using RateComparisonService (likely Fincra)
- ✅ Executes conversion via best provider
- ✅ Updates wallets:
  - USDT: decreased by 10
  - NGN: increased by ~15,805
- ✅ Records conversion transaction in Achat table

---

## 6. Currency Conversion - USD to GHS

### ✅ Should Execute Conversion

```json
{
  "id": 96325615,
  "event": "transaction.create",
  "company": "nuage_bridge_test",
  "data": {
    "id": "7519dc18-5b1e-48f8-b966-615e8e2b4029",
    "fee": 0,
    "fees": [],
    "note": "Currency conversion from USD to GHS",
    "user": {
      "id": "f21e486e-6df9-472e-a6e9-7aa3f14851e5",
      "email": "helghardt+001@gmail.com",
      "groups": [
        {
          "name": "individual",
          "label": "Individual",
          "section": "user"
        }
      ],
      "mobile": "+233200000000",
      "profile": null,
      "username": "helghardt001",
      "last_name": "Avenant",
      "temporary": false,
      "first_name": "Helghardt",
      "middle_name": null
    },
    "index": 0,
    "label": "Currency Conversion",
    "amount": -100,
    "parent": null,
    "status": "Initiating",
    "account": "TU6VZM4BTJ",
    "balance": 500,
    "created": 1731497722757,
    "creator": {
      "id": "f21e486e-6df9-472e-a6e9-7aa3f14851e5",
      "email": "helghardt+001@gmail.com",
      "groups": [
        {
          "name": "individual",
          "label": "Individual",
          "section": "user"
        }
      ],
      "mobile": null,
      "profile": null,
      "username": null,
      "last_name": null,
      "temporary": false,
      "first_name": null,
      "middle_name": null
    },
    "expires": 1731498322756,
    "partner": null,
    "subtype": "conversion",
    "tx_type": "debit",
    "updated": 1731497722756,
    "archived": false,
    "currency": {
      "code": "USD",
      "icon": null,
      "unit": "dollar",
      "symbol": "$",
      "description": "US Dollar",
      "display_code": "USD",
      "divisibility": 2
    },
    "executed": null,
    "metadata": {
      "source_currency": "USD",
      "dest_currency": "GHS",
      "conversion_amount": 100,
      "expected_rate": 12.50
    },
    "inclusive": false,
    "reference": null,
    "collection": "9a37d260-ff12-4d2e-b5c2-116a6c852252",
    "total_amount": -100,
    "account_currency": {
      "id": "875c90fa-65b5-4de8-b67d-acf4c3ac7cfc",
      "account": {
        "name": "general",
        "label": "General",
        "primary": true,
        "reference": "TU6VZM4BTJ",
        "definition": "general"
      },
      "currency": {
        "code": "USD",
        "icon": null,
        "unit": "dollar",
        "symbol": "$",
        "description": "US Dollar",
        "display_code": "USD",
        "divisibility": 2
      }
    }
  }
}
```

**Expected Result:**
- ✅ Routes to ConversionRouter
- ✅ Uses RateComparisonService to get best rate
- ✅ Executes conversion via best provider
- ✅ Updates wallets:
  - USD: decreased by 100
  - GHS: increased by ~1,250

---

## 7. Currency Conversion - Missing Metadata

### ❌ Should Return Error

```json
{
  "id": 96325616,
  "event": "transaction.create",
  "company": "nuage_bridge_test",
  "data": {
    "id": "8519dc18-5b1e-48f8-b966-615e8e2b4030",
    "fee": 0,
    "fees": [],
    "note": "Currency conversion",
    "user": {
      "id": "f21e486e-6df9-472e-a6e9-7aa3f14851e5",
      "email": "helghardt+001@gmail.com",
      "groups": [
        {
          "name": "individual",
          "label": "Individual",
          "section": "user"
        }
      ],
      "mobile": null,
      "profile": null,
      "username": "helghardt001",
      "last_name": "Avenant",
      "temporary": false,
      "first_name": "Helghardt",
      "middle_name": null
    },
    "index": 0,
    "label": "Currency Conversion",
    "amount": -10,
    "parent": null,
    "status": "Initiating",
    "account": "TU6VZM4BTJ",
    "balance": 50,
    "created": 1731497722757,
    "creator": {
      "id": "f21e486e-6df9-472e-a6e9-7aa3f14851e5",
      "email": "helghardt+001@gmail.com",
      "groups": [],
      "mobile": null,
      "profile": null,
      "username": null,
      "last_name": null,
      "temporary": false,
      "first_name": null,
      "middle_name": null
    },
    "expires": 1731498322756,
    "partner": null,
    "subtype": "conversion",
    "tx_type": "debit",
    "updated": 1731497722756,
    "archived": false,
    "currency": {
      "code": "USDT",
      "icon": null,
      "unit": "tether",
      "symbol": "₮",
      "description": "Tether USD",
      "display_code": "USDT",
      "divisibility": 2
    },
    "executed": null,
    "metadata": {
      "some_field": "value"
    },
    "inclusive": false,
    "reference": null,
    "collection": "9a37d260-ff12-4d2e-b5c2-116a6c852252",
    "total_amount": -10,
    "account_currency": {
      "id": "875c90fa-65b5-4de8-b67d-acf4c3ac7cfc",
      "account": {
        "name": "general",
        "label": "General",
        "primary": true,
        "reference": "TU6VZM4BTJ",
        "definition": "general"
      },
      "currency": {
        "code": "USDT",
        "icon": null,
        "unit": "tether",
        "symbol": "₮",
        "description": "Tether USD",
        "display_code": "USDT",
        "divisibility": 2
      }
    }
  }
}
```

**Expected Result:**
- ❌ Detects missing currency metadata
- ❌ Returns error: "Missing source or destination currency in metadata"

---

## 8. Payout - Crypto Address (VALR)

### ✅ Should Route to VALR for Crypto Payout

```json
{
  "id": 96325617,
  "event": "transaction.create",
  "company": "nuage_bridge_test",
  "data": {
    "id": "9519dc18-5b1e-48f8-b966-615e8e2b4031",
    "fee": 0,
    "fees": [],
    "note": null,
    "user": {
      "id": "f21e486e-6df9-472e-a6e9-7aa3f14851e5",
      "email": "helghardt+001@gmail.com",
      "groups": [
        {
          "name": "individual",
          "label": "Individual",
          "section": "user"
        }
      ],
      "mobile": null,
      "profile": null,
      "username": "helghardt001",
      "last_name": "Avenant",
      "temporary": false,
      "first_name": "Helghardt",
      "middle_name": null
    },
    "index": 0,
    "label": "Withdraw manual",
    "amount": -1000,
    "parent": null,
    "status": "Initiating",
    "account": "TU6VZM4BTJ",
    "balance": 5000,
    "created": 1731497722757,
    "creator": {
      "id": "f4d69547-ec0d-48ca-a7a1-aa461cbd8548",
      "email": "mtawamba@nuage.money",
      "groups": [
        {
          "name": "admin",
          "label": "Admin",
          "section": "admin"
        }
      ],
      "mobile": null,
      "profile": null,
      "username": null,
      "last_name": null,
      "temporary": false,
      "first_name": null,
      "middle_name": null
    },
    "expires": 1731498322756,
    "partner": null,
    "subtype": "withdraw_manual",
    "tx_type": "debit",
    "updated": 1731497722756,
    "archived": false,
    "currency": {
      "code": "ZAR",
      "icon": null,
      "unit": "rand",
      "symbol": "R",
      "description": "South African Rand",
      "display_code": "ZAR",
      "divisibility": 2
    },
    "executed": null,
    "metadata": {
      "location": "ZA",
      "crypto_address": "0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb",
      "crypto_currency": "USDT"
    },
    "inclusive": false,
    "reference": null,
    "collection": "9a37d260-ff12-4d2e-b5c2-116a6c852252",
    "total_amount": -1000,
    "account_currency": {
      "id": "875c90fa-65b5-4de8-b67d-acf4c3ac7cfc",
      "account": {
        "name": "general",
        "label": "General",
        "primary": true,
        "reference": "TU6VZM4BTJ",
        "definition": "general"
      },
      "currency": {
        "code": "ZAR",
        "icon": null,
        "unit": "rand",
        "symbol": "R",
        "description": "South African Rand",
        "display_code": "ZAR",
        "divisibility": 2
      }
    }
  }
}
```

**Expected Result:**
- ✅ Detects crypto_address + crypto_currency in metadata
- ✅ Priority routing selects VALR (Priority 1 for crypto)
- ✅ Routes to VALR
- ✅ Executes crypto withdrawal (ZAR → USDT conversion if needed)

---

## 9. Payout - Bridge External Account

### ✅ Should Route to Bridge for Multi-Rail Payout

```json
{
  "id": 96325618,
  "event": "transaction.create",
  "company": "nuage_bridge_test",
  "data": {
    "id": "a519dc18-5b1e-48f8-b966-615e8e2b4032",
    "fee": 0,
    "fees": [],
    "note": null,
    "user": {
      "id": "f21e486e-6df9-472e-a6e9-7aa3f14851e5",
      "email": "helghardt+001@gmail.com",
      "groups": [
        {
          "name": "individual",
          "label": "Individual",
          "section": "user"
        }
      ],
      "mobile": null,
      "profile": null,
      "username": "helghardt001",
      "last_name": "Avenant",
      "temporary": false,
      "first_name": "Helghardt",
      "middle_name": null
    },
    "index": 0,
    "label": "Withdraw manual",
    "amount": -50000,
    "parent": null,
    "status": "Initiating",
    "account": "TU6VZM4BTJ",
    "balance": 100000,
    "created": 1731497722757,
    "creator": {
      "id": "f4d69547-ec0d-48ca-a7a1-aa461cbd8548",
      "email": "mtawamba@nuage.money",
      "groups": [
        {
          "name": "admin",
          "label": "Admin",
          "section": "admin"
        }
      ],
      "mobile": null,
      "profile": null,
      "username": null,
      "last_name": null,
      "temporary": false,
      "first_name": null,
      "middle_name": null
    },
    "expires": 1731498322756,
    "partner": null,
    "subtype": "withdraw_manual",
    "tx_type": "debit",
    "updated": 1731497722756,
    "archived": false,
    "currency": {
      "code": "USD",
      "icon": null,
      "unit": "dollar",
      "symbol": "$",
      "description": "US Dollar",
      "display_code": "USD",
      "divisibility": 2
    },
    "executed": null,
    "metadata": {
      "location": "US",
      "dest_external_account_id": "ba_1234567890abcdef"
    },
    "inclusive": false,
    "reference": null,
    "collection": "9a37d260-ff12-4d2e-b5c2-116a6c852252",
    "total_amount": -50000,
    "account_currency": {
      "id": "875c90fa-65b5-4de8-b67d-acf4c3ac7cfc",
      "account": {
        "name": "general",
        "label": "General",
        "primary": true,
        "reference": "TU6VZM4BTJ",
        "definition": "general"
      },
      "currency": {
        "code": "USD",
        "icon": null,
        "unit": "dollar",
        "symbol": "$",
        "description": "US Dollar",
        "display_code": "USD",
        "divisibility": 2
      }
    }
  }
}
```

**Expected Result:**
- ✅ Detects dest_external_account_id in metadata
- ✅ Priority routing selects Bridge (Priority 2 for multi-rail)
- ✅ Routes to Bridge
- ✅ Executes payout to external bank account

---

## Testing Tips

### 1. Check Logs
```bash
tail -f storage/logs/laravel.log | grep -E "(PaymentRouter|ConversionRouter|metadata|routing|selectBestProvider)"
```

### 2. Verify Database
```sql
-- Check if wallets were updated
SELECT * FROM wallets WHERE user_id = 'user-uuid';

-- Check conversion records
SELECT * FROM achats WHERE metadata LIKE '%conversion%';
```

### 3. Expected Response Structure

**Payout Success Response:**
```json
{
  "success": true,
  "provider": "fincra",
  "ref_id": "original-ref-id",
  "pay_token": "provider-transaction-id",
  "status": "success"
}
```

**Conversion Success Response:**
```json
{
  "success": true,
  "provider": "fincra",
  "rate": 1580.50,
  "source_amount": 10.00,
  "dest_amount": 15805.00,
  "conversion_reference": "CNV-ABC123",
  "ref_id": "original-ref-id"
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Missing required payout destination details...",
  "ref_id": "original-ref-id",
  "status": "failed"
}
```

---

## Common Issues & Solutions

### Issue: "Provider not configured"
**Solution:** Add API keys to `.env`:
```bash
FINCRA_API_KEY=your_key
VALR_API_KEY=your_key
VALR_API_SECRET=your_secret
BRIDGE_API_KEY=your_key
```

### Issue: "User not found for wallet update"
**Solution:** Ensure user exists in database or adjust `client_id` in payload

### Issue: "Missing required payout destination details"
**Solution:** Check metadata has one of:
- **Crypto (VALR):** `crypto_address` + `crypto_currency`
- **Bridge:** `dest_external_account_id` OR `dest_wallet_id` OR `dest_crypto_address`
- **Bank (Fincra/StartButton):** `bank_code` + `dest_account_number`
- **Mobile Money (Fincra/StartButton):** `MNO` + `msisdn`

### Issue: "Quote expired"
**Solution:** Conversion quotes expire after 30 seconds. Try again.

### Issue: Confusion between Payout and Conversion
**Solution:**
- **Payouts** (`subtype: "withdraw_manual"`): Single currency, routes by metadata requirements
- **Conversions** (`subtype: "conversion"`): Requires `source_currency` + `dest_currency`, routes by best rate

---

## Key Differences: Payout vs Conversion

| Aspect | Payout (withdraw_manual) | Conversion |
|--------|-------------------------|------------|
| **Router** | PaymentRouter | ConversionRouter |
| **Routing Logic** | Metadata requirements + priority | Best exchange rate |
| **Currency Fields** | Single `currency` field | `source_currency` + `dest_currency` in metadata |
| **Metadata Required** | Bank/mobile/crypto destination | Source and dest currencies |
| **Wallet Updates** | No wallet changes | Decreases source, increases dest |
| **Rate Comparison** | No | Yes (RateComparisonService) |

---

## Postman Collection

Save these payloads in Postman:
1. Create a new Collection: "Nuage Money API Tests"
2. Add requests for each scenario above
3. Set environment variable: `{{base_url}}` = your API domain
4. Run Collection to test all scenarios

---
**Happy Testing! 🚀**
