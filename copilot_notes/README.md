# Copilot Notes & Documentation

This folder contains technical documentation and implementation notes created during development sessions with GitHub Copilot.

## 📚 Contents

### Architecture & Strategy
- **`ROUTING_STRATEGY.md`** - Complete routing architecture for payments and conversions
  - PaymentRouter strategy (metadata-based)
  - ConversionRouter strategy (rate-based)
  - Provider priority order
  - Future enhancements

### Implementation Guides
- **`CURRENCY_CONVERSION_IMPLEMENTATION.md`** - Currency conversion system implementation
  - Fincra integration details
  - Rate comparison logic
  - Wallet balance management

- **`StartButton.md`** - StartButton Africa integration notes
  - API endpoints
  - Configuration
  - Usage examples

### Testing
- **`POSTMAN_TEST_PAYLOADS.md`** - Comprehensive test payloads for Postman
  - Payout test cases (bank, mobile, crypto, Bridge)
  - Conversion test cases
  - Error scenarios
  - Expected responses

- **`POSTMAN_UPDATES_SUMMARY.md`** - Summary of test payload updates
  - Changes made to align with new architecture
  - Key corrections
  - Test coverage overview

### Session Notes
- **`gemini-notes.md`** - Earlier AI session notes and brainstorming

## 🎯 Purpose

These documents serve as:
1. **Reference** for understanding the system architecture
2. **Testing guide** for QA and development
3. **Onboarding material** for new developers
4. **Decision log** for architectural choices

## 📖 Recommended Reading Order

For new developers:
1. Start with `ROUTING_STRATEGY.md` to understand the core architecture
2. Review `POSTMAN_TEST_PAYLOADS.md` for practical testing examples
3. Deep dive into specific implementations as needed

## ⚠️ Note

These files are development documentation and are not required for the application to run. They can be excluded from production deployments but should be kept in version control for team reference.

---

**Last Updated:** November 13, 2024
**Maintained by:** Development team with GitHub Copilot assistance
