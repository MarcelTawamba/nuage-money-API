# VALR API Testing Guide

## Overview
This guide explains how to test the VALR API integration locally using Postman and Docker.

## Prerequisites
- Docker container running
- VALR API credentials configured in `.env`:
  ```
  VALR_BASE_URL=https://api.valr.com
  VALR_API_KEY=your_api_key
  VALR_API_SECRET=your_api_secret
  ```

## Setup

### 1. Start Docker Container
```bash
docker-compose up -d
```

### 2. Import Postman Collection
1. Open Postman
2. Click **Import** button
3. Select `documentation/VALR_API_Postman_Collection.json`
4. Update the `base_url` variable to match your Docker container URL (default: `http://localhost:8000/api`)

## API Endpoints

### Market Data (Public - No Auth Required)

#### Get All Markets
```
GET /api/valr/markets
```
Returns list of all supported trading pairs.

#### Get Market Data
```
GET /api/valr/market-data
```
Returns current market data for all pairs (prices, volumes, etc.).

#### Get Market Summary
```
GET /api/valr/market-summary
```
Returns 24-hour summary statistics for all pairs.

#### Get Order Book
```
GET /api/valr/order-book/{currencyPair}
```
Example: `GET /api/valr/order-book/BTCZAR`

Returns current order book (bids and asks) for a specific pair.

---

### Account Endpoints (Requires VALR API Credentials)

#### Get Balances
```
GET /api/valr/balances
```
Returns account balances for all currencies.

**Response Example:**
```json
{
  "success": true,
  "data": [
    {
      "currency": "BTC",
      "available": "0.123456",
      "reserved": "0.0",
      "total": "0.123456"
    },
    {
      "currency": "ZAR",
      "available": "10000.00",
      "reserved": "0.0",
      "total": "10000.00"
    }
  ]
}
```

#### Get Deposit Address
```
GET /api/valr/deposit-address/{currency}
```
Example: `GET /api/valr/deposit-address/BTC`

Returns the deposit address for a specific cryptocurrency.

---

### Trading Endpoints

#### Get Open Orders
```
GET /api/valr/orders/open
```
Returns all currently open orders.

#### Get Order Status
```
GET /api/valr/orders/{orderId}
```
Returns status of a specific order.

#### Place Limit Order
```
POST /api/valr/orders/limit
Content-Type: application/json

{
  "side": "BUY",
  "quantity": 0.001,
  "price": 1000000,
  "pair": "BTCZAR",
  "postOnly": "false",
  "customerOrderId": "optional-custom-id"
}
```

**Parameters:**
- `side`: "BUY" or "SELL"
- `quantity`: Amount of base currency (e.g., BTC)
- `price`: Price per unit in quote currency (e.g., ZAR)
- `pair`: Trading pair (e.g., "BTCZAR")
- `postOnly`: "true" or "false" (optional)
- `customerOrderId`: Your custom order ID (optional)

#### Place Market Order
```
POST /api/valr/orders/market
Content-Type: application/json

{
  "side": "BUY",
  "pair": "BTCZAR",
  "quoteAmount": 1000
}
```

**For BUY orders, use either:**
- `quoteAmount`: Amount in quote currency (ZAR)

**For SELL orders, use either:**
- `baseAmount`: Amount in base currency (BTC)

#### Cancel Order
```
DELETE /api/valr/orders/cancel
Content-Type: application/json

{
  "orderId": "order-id-here"
}
```

---

### Simple Trading (Simplified Buy/Sell)

#### Get Simple Quote
```
POST /api/valr/simple/quote
Content-Type: application/json

{
  "currencyPair": "BTCZAR",
  "payInCurrency": "ZAR",
  "payAmount": 1000,
  "side": "BUY"
}
```

Returns a quote for how much crypto you'll receive.

**Response Example:**
```json
{
  "success": true,
  "data": {
    "currencyPair": "BTCZAR",
    "payInCurrency": "ZAR",
    "payAmount": "1000",
    "receiveAmount": "0.00098765",
    "fee": "10.00",
    "feeCurrency": "ZAR",
    "id": "quote-id",
    "expiresAt": "2025-12-04T04:00:00Z"
  }
}
```

#### Place Simple Order
```
POST /api/valr/simple/order
Content-Type: application/json

{
  "currencyPair": "BTCZAR",
  "payInCurrency": "ZAR",
  "payAmount": 1000,
  "side": "BUY"
}
```

Executes a simple buy or sell order immediately at market price.

---

## Testing Workflow

### 1. Test Market Data (No Credentials Required)
Start by testing public endpoints that don't require authentication:
1. Get Markets
2. Get Market Data
3. Get Order Book for BTCZAR

### 2. Test Account Data (Requires Credentials)
Once you've verified your VALR credentials are configured:
1. Get Balances - This is the most important test
2. Get Deposit Address for BTC

### 3. Test Trading (Use Small Amounts!)
**⚠️ WARNING: These operations involve real money. Use small test amounts!**

For testing, use the Simple Trading endpoints:
1. Get a quote first
2. Review the quote carefully
3. If satisfied, place a simple order

### 4. Common Currency Pairs
- `BTCZAR` - Bitcoin/South African Rand
- `ETHZAR` - Ethereum/South African Rand
- `USDCZAR` - USDC/South African Rand
- `XRPZAR` - Ripple/South African Rand

---

## Error Handling

All endpoints return a consistent error format:

```json
{
  "success": false,
  "message": "Failed to fetch balances",
  "error": "Detailed error message from VALR API"
}
```

Common error scenarios:
- **401 Unauthorized**: Invalid API credentials
- **422 Validation Error**: Invalid request parameters
- **500 Server Error**: VALR API error or network issue

---

## Docker URLs

Depending on your Docker setup:

**Local Development:**
```
http://localhost:8000/api/valr/...
```

**Docker Internal Network:**
```
http://app:8000/api/valr/...
```

**With Custom Port Mapping:**
```
http://localhost:{your_port}/api/valr/...
```

---

## Frontend Integration Example

### JavaScript/Fetch
```javascript
// Get Balances
fetch('http://localhost:8000/api/valr/balances')
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      console.log('Balances:', data.data);
    } else {
      console.error('Error:', data.message);
    }
  });

// Get Simple Quote
fetch('http://localhost:8000/api/valr/simple/quote', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    currencyPair: 'BTCZAR',
    payInCurrency: 'ZAR',
    payAmount: 1000,
    side: 'BUY'
  })
})
  .then(response => response.json())
  .then(data => console.log('Quote:', data));
```

### React/Axios
```javascript
import axios from 'axios';

const valrApi = axios.create({
  baseURL: 'http://localhost:8000/api/valr'
});

// Get Balances
const getBalances = async () => {
  try {
    const response = await valrApi.get('/balances');
    return response.data;
  } catch (error) {
    console.error('Error fetching balances:', error);
  }
};

// Get Quote
const getQuote = async (currencyPair, payAmount, side) => {
  try {
    const response = await valrApi.post('/simple/quote', {
      currencyPair,
      payInCurrency: 'ZAR',
      payAmount,
      side
    });
    return response.data;
  } catch (error) {
    console.error('Error getting quote:', error);
  }
};
```

---

## Security Notes

1. **Never commit API credentials** to version control
2. **Use environment variables** for sensitive data
3. **Validate all user inputs** on the frontend
4. **Test with small amounts** first
5. **Implement rate limiting** in production
6. **Add authentication/authorization** before deploying to production

---

## Next Steps

1. Test basic endpoints with Postman
2. Integrate with your frontend application
3. Add authentication middleware if needed
4. Implement proper error handling in frontend
5. Add logging and monitoring
6. Consider adding caching for market data endpoints

---

## Support

For VALR API documentation, visit: https://docs.valr.com/

For issues with this integration, check:
- Laravel logs: `storage/logs/laravel.log`
- Docker logs: `docker-compose logs -f`
