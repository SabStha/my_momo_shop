# How to Fix the IP Address Conflict

## The Problem

Your computer has two network adapters:
- **VirtualBox Adapter**: `192.168.56.1` ❌ (Wrong - iOS can't reach it)
- **WiFi Adapter**: `192.168.2.145` ✅ (Correct - iOS can reach it)

Expo keeps picking the VirtualBox adapter by default, causing iOS to timeout.

---

## Permanent Solution: Disable VirtualBox Adapter When Developing

### Step 1: Open Network Connections

1. Press `Win + R` (Windows Key + R)
2. Type: `ncpa.cpl`
3. Press Enter

### Step 2: Disable VirtualBox Adapter

1. Find **"VirtualBox Host-Only Network"** (or similar name)
2. Right-click on it
3. Select **"Disable"**

### Step 3: Restart Expo

```powershell
cd amako-shop
npx expo start --clear --host lan
```

Now it will use `192.168.2.145` (your WiFi) ✅

### Step 4: When Done - Re-enable Adapter

After you finish iOS development:
1. Go back to Network Connections (`ncpa.cpl`)
2. Right-click **"VirtualBox Host-Only Network"**
3. Select **"Enable"**

---

## Alternative: Use Tunnel Mode (No Need to Disable)

Tunnel mode bypasses the IP issue completely:

```powershell
cd amako-shop
npx expo start --tunnel
```

**Pros:**
- ✅ No adapter disabling needed
- ✅ Works on any network
- ✅ No configuration needed

**Cons:**
- ⚠️ Slower initial load (5-30 seconds)
- ⚠️ Requires internet connection

---

## Visual Guide

### Finding Network Connections

```
Win + R → Type "ncpa.cpl" → Enter

You'll see:
┌─────────────────────────────────────┐
│ Network Connections                 │
├─────────────────────────────────────┤
│ 📶 Wi-Fi                            │
│    Status: Connected                │
│    192.168.2.145                    │
├─────────────────────────────────────┤
│ 🔗 VirtualBox Host-Only Network     │
│    Status: Enabled                  │
│    192.168.56.1                     │← Disable this one!
└─────────────────────────────────────┘
```

Right-click VirtualBox adapter → **Disable**

---

## Verification

After disabling VirtualBox adapter:

```powershell
# Check your IP addresses
ipconfig | findstr "IPv4"
```

Should show:
```
IPv4 Address. . . . . . . . . . . : 192.168.2.145
```

Should NOT show `192.168.56.1` anymore ✅

---

## When to Use Each Method

| Scenario | Method | Speed | Setup Required |
|----------|--------|-------|----------------|
| Quick testing | Tunnel | Slower | None |
| Daily development | LAN | Faster | Disable adapter once |
| Different networks | Tunnel | Slower | None |
| Production testing | LAN | Faster | Same WiFi needed |

---

## Troubleshooting

### Still Seeing Wrong IP After Disabling?

1. Restart Expo after disabling adapter
2. Run: `ipconfig /release` then `ipconfig /renew`
3. Restart your computer
4. Check firewall isn't blocking Node.js

### Can't Find VirtualBox Adapter?

It might be named differently:
- "Ethernet Adapter VirtualBox"
- "VirtualBox Ethernet Adapter"
- "Local Area Connection"

Look for any adapter with `192.168.56.1` IP address

### Need VirtualBox While Developing?

Unfortunately, VirtualBox and iOS development with LAN mode **conflict**. You have two options:

1. **Use tunnel mode** (keep VirtualBox enabled)
2. **Disable VirtualBox** when doing iOS development

You can't have both at the same time with LAN mode.

---

**Recommendation:** Use **tunnel mode** for iOS development to avoid this issue completely!

