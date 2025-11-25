# Quick Fix Checklist untuk Real-time Notifications

## ✅ Perbaikan yang Sudah Dilakukan

1. **Controller Update**
   - ✅ Menggunakan `broadcast()` instead of `event()`
   - ✅ Broadcast setelah `DB::commit()` untuk memastikan data tersimpan
   - ✅ Added error handling dan logging
   - ✅ Menggunakan `->toOthers()` untuk tidak broadcast ke sender

2. **Event Class**
   - ✅ Added `broadcastWith()` method untuk memastikan data terkirim
   - ✅ Event name: `document.sent`
   - ✅ Channel: `documents.ibuB`

3. **Frontend JavaScript**
   - ✅ Improved Pusher configuration (removed unnecessary options)
   - ✅ Better error handling dan logging
   - ✅ Channel subscription monitoring
   - ✅ Connection state monitoring
   - ✅ Detailed console logs untuk debugging

4. **Channel Authorization**
   - ✅ Added logging untuk debug authorization
   - ✅ Returns `true` untuk allow access

5. **Test Route**
   - ✅ Added `/test-broadcast` route untuk testing

## 🔍 Langkah Debugging

### Step 1: Verify Environment
```bash
# Check .env file
cat .env | grep PUSHER

# Should show:
# BROADCAST_DRIVER=pusher
# PUSHER_APP_ID=...
# PUSHER_APP_KEY=...
# PUSHER_APP_SECRET=...
# PUSHER_APP_CLUSTER=ap1
```

### Step 2: Clear Config Cache
```bash
php artisan config:clear
php artisan cache:clear
```

### Step 3: Test in Browser Console (IbuB)

Buka `/dashboardB` atau `/dokumensB`, buka console (F12), cek:

**Expected Output:**
```
Real-time notification setup: {module: "ibuB", isIbuB: true, ...}
✅ Laravel Echo initialized successfully
✅ Pusher connected successfully
✅ Successfully subscribed to channel: documents.ibuB
```

**If you see errors:**
- `PUSHER_APP_KEY not configured` → Fix `.env`
- `Not IbuB module` → Check controller `$module` variable
- `403 error` → Check `/broadcasting/auth` route

### Step 4: Test Broadcast

**Option A: Use Test Route**
```
http://127.0.0.1:8000/test-broadcast
```

**Option B: Send from IbuA**
1. Buka IbuA dashboard
2. Klik "Kirim ke IbuB" pada dokumen
3. Check Laravel logs: `storage/logs/laravel.log`
4. Check browser console di IbuB

### Step 5: Check Laravel Logs

```bash
tail -f storage/logs/laravel.log
```

**When sending document, should see:**
```
[INFO] DocumentSent event broadcasted {"document_id":1,"sent_to":"ibuB"}
```

**When IbuB subscribes:**
```
[INFO] Channel authorization request {"channel":"documents.ibuB"}
```

## 🐛 Common Issues

### Issue 1: "Pusher not connected"
**Fix:**
- Verify Pusher credentials di `.env`
- Check Pusher dashboard untuk verify credentials
- Try different cluster (ap1, eu, us-east-1)

### Issue 2: "Channel subscription 403"
**Fix:**
- Check `/broadcasting/auth` route exists
- Check CSRF token in request
- Check `routes/channels.php` returns `true`

### Issue 3: "Event not received"
**Fix:**
- Verify event is broadcasted (check logs)
- Check channel name: `documents.ibuB`
- Check event name: `.document.sent` (with dot!)
- If using queue: run `php artisan queue:work`

### Issue 4: "Notifications appear but no UI"
**Fix:**
- Check browser console for JS errors
- Verify `#notification-container` exists
- Check CSS is loaded

## 🧪 Testing Steps

1. **Open IbuB Dashboard**
   - URL: `http://127.0.0.1:8000/dashboardB`
   - Open browser console (F12)
   - Verify connection logs

2. **Send Test Event**
   - Open new tab: `http://127.0.0.1:8000/test-broadcast`
   - Check IbuB console for event

3. **Send Real Document**
   - Open IbuA: `http://127.0.0.1:8000/dokumens`
   - Click "Kirim ke IbuB"
   - Check IbuB for notification

## 📋 Verification Checklist

- [ ] `.env` has correct Pusher credentials
- [ ] `BROADCAST_DRIVER=pusher` in `.env`
- [ ] Config cache cleared
- [ ] Browser console shows "Pusher connected"
- [ ] Browser console shows "Subscribed to channel"
- [ ] Laravel logs show broadcast events
- [ ] Test route works
- [ ] Real document send works
- [ ] Toast notification appears
- [ ] Badge counter updates

## 🚀 Next Steps

Jika masih tidak berfungsi:

1. **Check Pusher Dashboard**
   - Login: https://dashboard.pusher.com
   - Check "Debug Console"
   - Verify events are being sent

2. **Check Network Tab**
   - Open DevTools → Network
   - Filter by "WS" (WebSocket)
   - Check WebSocket connection status

3. **Check Queue (if using)**
   ```bash
   php artisan queue:work
   ```

4. **Enable Detailed Logging**
   - Check `config/logging.php`
   - Set level to `debug`
   - Check all logs

## 📞 Still Not Working?

Check these files:
- `app/Events/DocumentSent.php` - Event class
- `app/Http/Controllers/DokumenController.php` - Broadcast trigger
- `routes/channels.php` - Channel authorization
- `routes/web.php` - Broadcasting auth route
- `resources/views/layouts/app.blade.php` - Frontend JS
- `config/broadcasting.php` - Pusher config

See `DEBUG_NOTIFICATIONS.md` for detailed debugging guide.




