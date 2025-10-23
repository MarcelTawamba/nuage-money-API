### StartButton Africa API Integration Summary

This service integrates with the StartButton Africa API to provide payment and banking functionalities. It uses a base URL and secret/public keys configured in the environment variables.

### Implemented Endpoints:

#### 1. Get List of Banks

*   **Description:** Retrieves a list of banks for a given currency and type.
*   **Method:** `getListOfBanks(string $currency="NGN", string $type="bank", string $countryCode = null)`
*   **Endpoint:** `GET /bank/list/{currency}?type={type}`
*   **Request Input:**
    *   `currency` (string, optional, default: "NGN"): The currency to filter banks for (e.g., "NGN", "GHS").
    *   `type` (string, optional, default: "bank"): The type of financial institution to retrieve.
    *   `countryCode` (string, optional): The two-letter country code to filter banks by.

#### 2. Request Payment

*   **Description:** Initializes a payment transaction.
*   **Method:** `requestPayment(float $amount, string $reference="", string $currency="NGN", string $email="mtawambamarcel@nuage.money", string $redirectUrl = null, string $webhookUrl = null, array $paymentMethods = [], array $metadata = [])`
*   **Endpoint:** `POST /transaction/initialize`
*   **Request Input:**
    *   `amount` (float, required): The amount to be paid, in the smallest currency unit (e.g., kobo for NGN).
    *   `reference` (string, optional): A unique identifier for the transaction.
    *   `currency` (string, optional, default: "NGN"): The currency of the transaction.
    *   `email` (string, optional, default: "mtawambamarcel@nuage.money"): The email address of the payer.
    *   `redirectUrl` (string, optional): A URL to redirect the user to after payment.
    *   `webhookUrl` (string, optional): A URL to send transaction status updates to.
    *   `paymentMethods` (array, optional): A list of allowed payment methods.
    *   `metadata` (array, optional): Additional data to associate with the transaction.

#### 3. Bank Account Validation

*   **Description:** Verifies the details of a bank account.
*   **Method:** `bankAccountValidation(string $bankCode, string $accountNumber)`
*   **Endpoint:** `GET /bank/verify?bankCode={bankCode}&accountNumber={accountNumber}`
*   **Request Input:**
    *   `bankCode` (string, required): The code of the bank.
    *   `accountNumber` (string, required): The account number to validate.

#### 4. Make Transfer

*   **Description:** Initiates a transfer to a bank account.
*   **Method:** `makeTransfer(array $data)`
*   **Endpoint:** `POST /transaction/transfer`
*   **Request Input:**
    *   `data` (array, required): An array containing the transfer details. Based on the code `($data['amount'] * 100)`, it's expected to contain at least an `amount` key. Other likely keys are `bankCode`, `accountNumber`, `reference`, and `currency`.

#### 5. Check Transaction Status

*   **Description:** Checks the status of a specific transaction.
*   **Method:** `checkTransaction(string $reference)`
*   **Endpoint:** `GET /transaction/status/{reference}`
*   **Request Input:**
    *   `reference` (string, required): The unique identifier of the transaction to check.
