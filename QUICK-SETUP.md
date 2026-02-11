# YTrip Plugin - Quick Setup Guide

## 📋 Current Status

✅ **Plugin is installed on server**
✅ **WordPress REST API is working**
❌ **New features NOT deployed** (returns 404)

---

## 🚨 Problem

The latest plugin code (including REST API, content generator, and admin fixes) has been pushed to GitHub but **NOT deployed to your server yet**.

This is why:
- `https://zakharioustours.de/wp-admin/admin.php?page=ytrip-settings` shows "Sorry, you are not allowed to access this page"
- REST API endpoints return 404 errors
- Content cannot be created automatically

---

## 🔧 Solution: Deploy Updates

You need to update the plugin on your server using **ONE** of these methods:

### Method 1: Quick Fix Script (Recommended - Easiest)

1. **Download** this file:
   - https://github.com/antiali/zakharioustours.de/raw/main/wp-content/plugins/ytrip/quick-fix.php

2. **Upload** to your server via FTP/FileManager:
   - Location: `/wp-content/plugins/ytrip/quick-fix.php`

3. **Access** in browser:
   - `https://zakharioustours.de/wp-content/plugins/ytrip/quick-fix.php`

4. **Click buttons in order:**
   - ✅ **Step 1: Fix Admin Access** - Flushes capabilities, fixes permission error
   - 📦 **Step 2: Create Content** - Creates 4 categories, 6 destinations, 24 tours
   - 🔄 **Step 3: Flush Permalinks** - Enables REST API endpoints

5. **Done!** Access YTrip Settings:
   - `https://zakharioustours.de/wp-admin/admin.php?page=ytrip-settings`

### Method 2: Git Pull (If you have SSH/CLI access)

```bash
cd /path/to/wordpress/wp-content/plugins/ytrip
git pull origin main
```

### Method 3: Manual Upload

1. Download all updated files from GitHub
2. Upload to `/wp-content/plugins/ytrip/` replacing existing files
3. Reactivate plugin in WordPress admin

---

## ✅ What Will Be Created

After running quick-fix.php:

| Content | Count |
|----------|--------|
| Categories | 4 |
| Destinations | 6 |
| Tours | 24+ |

**Categories:**
- Adventure Tours
- Cultural Experiences
- Beach & Relaxation
- City Breaks

**Destinations:**
- Germany
- Egypt
- France
- Italy
- Spain
- Greece

**Tours per Destination:** 4 tours each

---

## 📁 Quick Fix Script Features

The `quick-fix.php` script includes:

1. **Fix Admin Access**
   - Flushes user roles and capabilities
   - Fixes "not allowed to access this page" error

2. **Create Demo Content**
   - Creates categories
   - Creates destinations
   - Creates tours with random prices (€899-€2599)
   - Assigns categories and destinations

3. **Flush Permalinks**
   - Enables REST API endpoints
   - Fixes 404 errors

4. **Show Stats**
   - Current tour count
   - Category count
   - Destination count

5. **Quick Links**
   - Direct access to YTrip Settings
   - Manage tours, categories, destinations
   - Add new tour

---

## 🔗 Quick Links After Setup

- **YTrip Settings:** https://zakharioustours.de/wp-admin/admin.php?page=ytrip-settings
- **Manage Tours:** https://zakharioustours.de/wp-admin/edit.php?post_type=ytrip_tour
- **Tours Frontend:** https://zakharioustours.de/tours/
- **Asset Debug:** https://zakharioustours.de/wp-content/plugins/ytrip/admin/debug-assets.php

---

## 💡 Important Notes

1. **Delete quick-fix.php after use** for security (or rename to `.php.bak`)
2. **Quick-fix.php runs under your WordPress admin permissions** - no extra login needed
3. **All changes made via quick-fix.php are saved immediately** to your database

---

## 🆘 If You Have Issues

If quick-fix.php doesn't work:

1. **Check file permissions:**
   ```bash
   chmod 644 /wp-content/plugins/ytrip/quick-fix.php
   ```

2. **Check PHP errors:**
   - Access the file with `?debug=1`
   - Check WordPress debug log: `/wp-content/debug.log`

3. **Alternative:** Use git pull if you have CLI access

---

**Last Updated:** 2026-02-05
**Plugin Version:** 1.0.0
**GitHub:** https://github.com/antiali/zakharioustours.de
