# Debug Guide: Real-time Notifications

## Step-by-Step Debugging

### 1. Check Environment Configuration

Pastikan `.env` memiliki konfigurasi berikut:

```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=ap1
PUSHER_SCHEME=https
```

**Test:**
```bash
php artisan config:clear
php artisan config:cache
php artisan tinker
>>> config('broadcasting.default')
=> "pusher"
>>> config('broadcasting.connections.pusher.key')
=> "your_app_key"
```

### 2. Check Browser Console (IbuB Dashboard)

Buka browser console (F12) di halaman IbuB (`/dashboardB` atau `/dokumensB`).

**Expected logs:**
```
Real-time notification setup: {module: "ibuB", isIbuB: true, hasPusherKey: true, ...}
✅ Laravel Echo initialized successfully
✅ Pusher connected successfully
Connection state: "connected"
✅ Successfully subscribed to channel: documents.ibuB
✅ Real-time notifications initialized for IbuB
Listening on channel: documents.ibuB
Waiting for events...
```

**If you see errors:**
- `PUSHER_APP_KEY not configured` → Check `.env` file
- `Not IbuB module` → Check `$module` variable in controller
- `Channel subscription error: 403` → Check `/broadcasting/auth` route
- `Pusher connection error` → Check Pusher credentials and network

### 3. Test Broadcast Manually

**Option A: Use Test Route**
```
GET http://127.0.0.1:8000/test-broadcast
```

**Option B: Use Tinker**
```bash
php artisan tinker
>>> $dokumen = App\Models\Dokumen::first();
>>> broadcast(new App\Events\DocumentSent($dokumen, 'test', 'ibuB'));
```

**Expected:**
- Browser console di IbuB: `📨 Document sent event received: {...}`
- Toast notification muncul
- Badge counter update

### 4. Check Laravel Logs

```bash
tail -f storage/logs/laravel.log
```

**When IbuA sends document, you should see:**
```
[timestamp] local.INFO: DocumentSent event broadcasted {"document_id":1,"sent_to":"ibuB","status":"sent_to_ibub"}
```

**When IbuB subscribes to channel:**
```
[timestamp] local.INFO: Channel authorization request {"channel":"documents.ibuB","user_id":"guest","session_module":null}
```

### 5. Check Pusher Dashboard

1. Login ke https://dashboard.pusher.com
2. Pilih aplikasi Anda
3. Go to "Debug Console"
4. Send test event:
   - Channel: `private-documents.ibuB`
   - Event: `document.sent`
   - Data: `{"document":{"id":1,"nomor_agenda":"TEST"},"sentBy":"test","sentTo":"ibuB","message":"Test"}`
5. Check if event appears in "Events" tab

### 6. Common Issues & Solutions

#### Issue: "Pusher connection error"
**Solution:**
- Check if `PUSHER_APP_KEY` is correct
- Check if cluster matches Pusher dashboard
- Check network/firewall for WebSocket connections
- Try different cluster (ap1, eu, us-east-1)

#### Issue: "Channel subscription error: 403"
**Solution:**
- Check `/broadcasting/auth` route is accessible
- Check `routes/channels.php` authorization returns `true`
- Check CSRF token in request headers
- Check Laravel logs for authorization errors

#### Issue: "Event not received"
**Solution:**
- Verify event is broadcasted (check Laravel logs)
- Verify channel name matches: `documents.ibuB`
- Verify event name: `.document.sent` (with dot prefix)
- Check if using queue (run `php artisan queue:work`)

#### Issue: "Notifications appear but no toast"
**Solution:**
- Check browser console for JavaScript errors
- Verify `showNotification()` function exists
- Check if notification container exists: `#notification-container`
- Check CSS for notification styles

### 7. Verify Queue Configuration

If using queue for broadcasts:

```env
QUEUE_CONNECTION=database
```

Then run:
```bash
php artisan queue:work
```

**Check if events are queued:**
```sql
SELECT * FROM jobs WHERE queue = 'default';
```

### 8. Network Debugging

**Check WebSocket connection:**
1. Open browser DevTools → Network tab
2. Filter by "WS" (WebSocket)
3. Look for connection to `ws-{cluster}.pusher.com`
4. Check connection status (should be 101 Switching Protocols)

**Check Authorization Request:**
1. Filter by "broadcasting/auth"
2. Check request method: POST
3. Check response: Should return 200 with auth token
4. Check request headers: Should include CSRF token

### 9. Test Checklist

- [ ] `.env` has correct Pusher credentials
- [ ] `BROADCAST_DRIVER=pusher` in `.env`
- [ ] Browser console shows "Pusher connected"
- [ ] Browser console shows "Subscribed to channel"
- [ ] Laravel logs show "DocumentSent event broadcasted"
- [ ] Test route `/test-broadcast` works
- [ ] Toast notification appears when event received
- [ ] Badge counter updates correctly

### 10. Production Checklist

- [ ] Remove test route `/test-broadcast`
- [ ] Update channel authorization with proper auth
- [ ] Setup queue worker for broadcasts
- [ ] Enable error monitoring
- [ ] Test with multiple concurrent users
- [ ] Monitor Pusher usage/limits

## Quick Test Script

Save as `test-notification.html` and open in browser:

```html
<!DOCTYPE html>
<html>
<head>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
</head>
<body>
    <h1>Test Notification</h1>
    <div id="status"></div>
    <div id="messages"></div>

    <script>
        const key = 'YOUR_PUSHER_APP_KEY';
        const cluster = 'YOUR_CLUSTER';
        
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: key,
            cluster: cluster,
            forceTLS: true,
            encrypted: true,
            authEndpoint: 'http://127.0.0.1:8000/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': 'YOUR_CSRF_TOKEN'
                }
            }
        });

        const status = document.getElementById('status');
        const messages = document.getElementById('messages');

        window.Echo.connector.pusher.connection.bind('connected', () => {
            status.innerHTML = '<p style="color: green;">✅ Connected</p>';
        });

        window.Echo.private('documents.ibuB')
            .subscribed(() => {
                status.innerHTML += '<p style="color: green;">✅ Subscribed</p>';
            })
            .listen('.document.sent', (e) => {
                messages.innerHTML += '<p>📨 Event received: ' + JSON.stringify(e) + '</p>';
            });
    </script>
</body>
</html>
```




