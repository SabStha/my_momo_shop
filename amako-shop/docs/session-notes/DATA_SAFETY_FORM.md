# Google Play Data Safety Form - AmaKo Shop

This document provides the exact answers for the Google Play Console Data Safety form.

## Data Collection and Use

### 1. Does your app collect or share any of the required user data types?

**Answer: YES**

### 2. Data Types Collected

#### Device or Other IDs
- **What data is collected?** Push notification tokens and device identifiers
- **How is it used?** App functionality (push notifications)
- **Is this data shared with third parties?** NO
- **Is this data sold to third parties?** NO
- **Is this data used for tracking users?** NO
- **Is this data encrypted in transit?** YES
- **Is this data encrypted at rest?** YES

#### Personal Information
- **What data is collected?** Name, phone number, email address
- **How is it used?** Account management
- **Is this data shared with third parties?** NO
- **Is this data sold to third parties?** NO
- **Is this data used for tracking users?** NO
- **Is this data encrypted in transit?** YES
- **Is this data encrypted at rest?** YES

#### Purchase History
- **What data is collected?** Order details, payment information
- **How is it used?** App functionality (order processing)
- **Is this data shared with third parties?** YES (payment processors only)
- **Is this data sold to third parties?** NO
- **Is this data used for tracking users?** NO
- **Is this data encrypted in transit?** YES
- **Is this data encrypted at rest?** YES

## Data Sharing

### 3. Does your app share data with third parties?

**Answer: YES** (Limited sharing for essential services only)

### 4. Third-Party Data Sharing

#### Payment Processors
- **What data is shared?** Order amounts, payment method information
- **Why is it shared?** To complete transactions
- **Is this data sold?** NO
- **Is this data used for tracking?** NO

#### Infrastructure Providers
- **What data is shared?** App usage data, device information
- **Why is it shared?** To provide cloud services and hosting
- **Is this data sold?** NO
- **Is this data used for tracking?** NO

## Data Security

### 5. Data Encryption

- **Is data encrypted in transit?** YES
- **Is data encrypted at rest?** YES
- **What encryption standards are used?** TLS 1.3, AES-256

### 6. Data Deletion

- **Can users request data deletion?** YES
- **How long is data retained?** Account lifetime + legal requirements
- **Is data automatically deleted?** YES (when account is deleted)

## Summary for Play Console

### Key Points to Emphasize:
1. **NO data is sold to third parties**
2. **Limited sharing only for essential services** (payments, infrastructure)
3. **All data is encrypted** in transit and at rest
4. **Push tokens are used only for app functionality**
5. **Personal info is used only for account management**
6. **Purchase data is shared only with payment processors**

### Data Safety Declaration:
- **Device or other IDs** → App functionality (push notifications)
- **Personal info** → Account management
- **Purchase info** → App functionality (order processing)
- **Data NOT sold** to third parties
- **Data encrypted** in transit and at rest
- **Limited sharing** only for essential services

## Compliance Notes

- This form should be completed in the Google Play Console
- Update if data collection practices change
- Review annually for accuracy
- Keep documentation for audit purposes
- Ensure privacy policy matches these declarations
