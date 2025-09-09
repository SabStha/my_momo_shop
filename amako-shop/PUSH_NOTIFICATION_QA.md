# Push Notification QA Testing Guide - AmaKo Shop

This document outlines the testing procedures for the push notification system to ensure it works correctly before Play Store submission.

## Prerequisites

- Android device with Android 13+ (for POST_NOTIFICATIONS permission)
- iOS device (for iOS testing)
- Development build of the app
- Backend server running with notification endpoints
- Test user account

## Test Environment Setup

### 1. App Configuration
- Ensure `app.json` has correct notification permissions
- Verify EAS project ID is set correctly
- Check that `expo-notifications` plugin is enabled

### 2. Backend Setup
- Confirm `/devices` endpoint is working
- Verify `/notify/test` endpoint is implemented
- Ensure order status update notifications are configured

## Test Cases

### Test Case 1: Initial Login and Device Registration

#### Steps:
1. Install fresh app build
2. Login with test account
3. Grant notification permissions when prompted
4. Check server logs for device registration

#### Expected Results:
- ✅ Notification permission dialog appears
- ✅ Permission is granted
- ✅ Device token is generated
- ✅ Token is sent to `/devices` endpoint
- ✅ Server devices table shows new device entry

#### Verification:
```bash
# Check server logs for:
"Device registered successfully"
# Check database devices table for new entry
```

### Test Case 2: Test Push Button

#### Steps:
1. Navigate to Profile screen
2. Tap "Test Push" button
3. Wait for API response
4. Check for local notification
5. Check for in-app toast

#### Expected Results:
- ✅ "Test Push Sent" alert appears
- ✅ Local notification is received
- ✅ In-app toast shows notification message
- ✅ No errors in console

#### Verification:
```bash
# Check API logs for:
POST /notify/test
# Check app console for:
"Test push notification sent successfully"
```

### Test Case 3: Order Status Update Notifications

#### Steps:
1. Place a test order
2. Update order status via API
3. Check if push notification is received
4. Verify notification content
5. Test notification tap navigation

#### Expected Results:
- ✅ Push notification received with order status
- ✅ Notification shows correct order ID
- ✅ Tapping notification opens app
- ✅ App navigates to order detail screen
- ✅ In-app toast shows order update

#### Verification:
```bash
# Check notification payload:
{
  "orderId": "123",
  "status": "ready",
  "title": "Order Update",
  "body": "Your order is ready"
}
```

### Test Case 4: Background/Foreground Notifications

#### Steps:
1. Send notification while app is in background
2. Send notification while app is in foreground
3. Test notification tap from background
4. Test notification tap from foreground

#### Expected Results:
- ✅ Background notifications work correctly
- ✅ Foreground notifications show in-app toast
- ✅ Navigation works from both states
- ✅ No duplicate notifications

### Test Case 5: Multiple Device Testing

#### Steps:
1. Login on multiple devices
2. Send test notifications
3. Verify each device receives notifications
4. Test logout/device cleanup

#### Expected Results:
- ✅ Each device gets unique token
- ✅ All devices receive notifications
- ✅ Logout removes device from server
- ✅ No orphaned device tokens

## Error Scenarios

### Test Case 6: Network Failures

#### Steps:
1. Disconnect internet
2. Try to register device
3. Try to send test notification
4. Reconnect and retry

#### Expected Results:
- ✅ Graceful error handling
- ✅ Retry mechanism works
- ✅ User gets appropriate error messages
- ✅ App doesn't crash

### Test Case 7: Permission Denied

#### Steps:
1. Deny notification permissions
2. Try to use notification features
3. Check app behavior

#### Expected Results:
- ✅ App handles permission denial gracefully
- ✅ User can still use app (without notifications)
- ✅ Clear messaging about permission requirements

## Performance Testing

### Test Case 8: Notification Delivery Speed

#### Steps:
1. Send multiple notifications rapidly
2. Measure delivery time
3. Check for notification queuing

#### Expected Results:
- ✅ Notifications delivered within 5 seconds
- ✅ No notification loss
- ✅ Proper queuing if needed

### Test Case 9: Battery Impact

#### Steps:
1. Monitor battery usage with notifications enabled
2. Compare with notifications disabled
3. Check for background processes

#### Expected Results:
- ✅ Minimal battery impact
- ✅ No unnecessary background processes
- ✅ Efficient token management

## Security Testing

### Test Case 10: Authentication Requirements

#### Steps:
1. Try to register device without auth token
2. Try to send test notification without auth
3. Verify proper authentication checks

#### Expected Results:
- ✅ Unauthorized requests are rejected
- ✅ Proper HTTP status codes returned
- ✅ No sensitive data exposed

## Cross-Platform Testing

### Android Specific
- ✅ POST_NOTIFICATIONS permission handling
- ✅ Notification channel creation
- ✅ Adaptive icon support
- ✅ Edge-to-edge display support

### iOS Specific
- ✅ APNS token handling
- ✅ Notification permissions
- ✅ Background app refresh
- ✅ Silent notifications

## Reporting and Documentation

### Test Results Template
```
Test Case: [Number]
Date: [Date]
Tester: [Name]
Device: [Model/OS]
Result: PASS/FAIL
Notes: [Any issues or observations]
```

### Bug Reporting
- Screenshots of issues
- Console logs
- Steps to reproduce
- Expected vs actual behavior
- Device information

## Sign-off Checklist

- [ ] All test cases pass
- [ ] No critical bugs found
- [ ] Performance meets requirements
- [ ] Security requirements satisfied
- [ ] Cross-platform compatibility verified
- [ ] Documentation completed
- [ ] Ready for Play Store submission

## Post-Launch Monitoring

- Monitor notification delivery rates
- Track user engagement with notifications
- Monitor crash reports related to notifications
- Gather user feedback on notification experience
- Plan improvements based on usage data
