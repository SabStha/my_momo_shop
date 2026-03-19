# Finds & Creator Feature: Mobile Implementation TODO

This document outlines the missing components and logic required on the mobile side (Expo/React Native) to make the Finds and Creator features fully functional, leveraging the existing Laravel backend.

## 1. Finds Feature (Merchandise & Bulk)

The backend already has models for `Merchandise`, `BulkPackage`, and `FindsCategory`, along with a `/api/finds/data` endpoint.

### UI Screens Needed
- [ ] **Finds Hub**: A dedicated screen to browse Merchandise (T-shirts, Toys, Accessories) and Bulk Packages.
- [ ] **Category Filtering**: Support for the dynamic categories provided by the API (T-shirts, Accessories, etc.).
- [ ] **Item Details**: Modern, premium detail views for merchandise with "Unlockable" and "Earn points" indicators.
- [ ] **Bulk Package View**: A layout optimized for displaying multi-item packages (e.g., Momos for 10 people).

### Logic Needed
- [ ] **Finds Cart Integration**: Logic to handle adding Merchandise and BulkPackages to the cart (these are separate from standard `Product` models and may need specialized handling in `useCartSyncStore`).
- [x] Image Preloader Fix
  - [x] Search existing `/api/bulk` and `/api/finds` endpoints
  - [x] Check `ImagePreloader.ts` for expected JSON structures
  - [x] Add `/api/menu`, `/api/bulk`, and `/api/finds` endpoints to `api.php`
  - [x] Verify API returns correct JSON format

- [ ] Web and Mobile Cart Synchronization
  - [ ] Add sync logic to `CartController@index`
  - [ ] Verify `CartController` mutation methods write to DB
  - [ ] Create `SyncCartFromDatabase` middleware
  - [ ] Register middleware for web routes
  - [ ] Verify identical data format between web session and `user_carts`
- [ ] **Progress Tracking**: UI indicators showing how close a user is to unlocking specific "Finds" based on their loyalty points/tier.
- [ ] **Model Filtering**: Support for the `model` parameter (e.g., 'all', 'hero', 'limited') to filter featured items.

---

## 2. Creator Program

The backend supports most of the business logic, but many API endpoints are currently stubs or strictly web-based.

### UI Screens Needed
- [ ] **Onboarding/Application**: A "Become a Creator" screen in the user profile for eligible users to apply/register.
- [ ] **Creator Dashboard**: A mobile-optimized dashboard for active creators to view:
    - Current points and total earnings.
    - Active referral code.
    - Quick "Share" button for their referral link/code.
    - Recent successful referrals list.
- [ ] **Leaderboard**: A mobile view for the top creators (API already exists: `/api/leaderboard`).
- [ ] **Creator Profile**: View/Edit bio and avatar specifically for the creator profile.

### API Development Needed (Current Gaps)
- [ ] **POST /api/creator/register**: Mobile-specific endpoint to convert a standard user to a Creator.
- [ ] **GET /api/creator/profile**: API version of the profile management.
- [ ] **GET /api/creator/earnings**: Detailed breakdown of points earned from referrals and monthly rewards.
- [ ] **POST /api/creator/coupon**: Trigger generation of the unique creator discount code from mobile.
- [ ] **GET /api/creator/referrals**: Full list of referred users and their status (Pending, Ordered, etc.).

---

## 3. General Integration

- [ ] **Referral Link Deep Linking**: Handle `amakoshop.com/ref/{code}` links to automatically apply the creator's code during registration or in the cart.
- [ ] **Wallet Visibility**: Integration of the `Wallet` model in the mobile app so creators can see their transferable balance.
- [ ] **Push Notifications**: Specialized notifications for creators when a referral makes their first order or when rewards are assigned.
