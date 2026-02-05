# دليل إعداد MCP لـ Cursor و Antigravity

## ✅ التكوين الصحيح لـ Cursor

### الموقع:
`C:\Users\k\.cursor\mcp.json`

### المحتوى:
```json
{
  "mcpServers": {
    "AWBU MCP": {
      "url": "https://projects.muhamedahmed.com/peralite2/wp-json/awbu/v1/mcp",
      "apiKey": "aCtqpmSwYWjgaFXwxwRgrc38rxc838Goeb9bnDnWh8EHrRs6xDz19FwiKdBckn4Y"
    }
  }
}
```

### الخطوات:
1. افتح `C:\Users\k\.cursor\mcp.json`
2. الصق المحتوى أعلاه
3. احفظ الملف
4. أعد تشغيل Cursor IDE

---

## ✅ التكوين الصحيح لـ Antigravity

### الموقع:
إعدادات Antigravity > MCP Servers

### المحتوى:
```json
{
  "mcpServers": {
    "AWBU MCP": {
      "serverUrl": "https://projects.muhamedahmed.com/peralite2/wp-json/awbu/v1/mcp",
      "apiKey": "aCtqpmSwYWjgaFXwxwRgrc38rxc838Goeb9bnDnWh8EHrRs6xDz19FwiKdBckn4Y"
    }
  }
}
```

### الخطوات:
1. افتح إعدادات Antigravity
2. اذهب إلى MCP Servers
3. الصق المحتوى أعلاه
4. احفظ الإعدادات
5. أعد تشغيل Antigravity IDE

---

## 🔍 التحقق من الاتصال

### اختبار من Terminal:
```powershell
# Test Initialize
Invoke-WebRequest -Uri "https://projects.muhamedahmed.com/peralite2/wp-json/awbu/v1/mcp/initialize" `
  -Method POST `
  -Headers @{
    "Content-Type" = "application/json"
    "X-MCP-API-Key" = "aCtqpmSwYWjgaFXwxwRgrc38rxc838Goeb9bnDnWh8EHrRs6xDz19FwiKdBckn4Y"
  } `
  -Body '{"clientInfo": {"name": "Test", "version": "1.0"}}'
```

### النتيجة المتوقعة:
- Status: 200 OK
- Response: JSON مع `protocolVersion`, `capabilities`, `serverInfo`

---

## ⚠️ الأخطاء الشائعة وحلولها

### 1. "No server info found"
**السبب:** التكوين غير صحيح أو API key خاطئ
**الحل:** 
- تحقق من أن `url` أو `serverUrl` صحيح
- تحقق من أن `apiKey` صحيح
- تأكد من أن الملف JSON صالح (لا أخطاء syntax)

### 2. "Invalid content type, expected text/event-stream"
**السبب:** Cursor يحاول الاتصال بـ SSE endpoint
**الحل:** 
- تأكد من أن URL هو `/mcp` وليس `/mcp/stream`
- النظام سيتحول تلقائياً إلى SSE عند الحاجة

### 3. "Error: serverUrl or command must be specified"
**السبب:** Antigravity يحتاج `serverUrl` وليس `url`
**الحل:** 
- استخدم `serverUrl` في Antigravity
- استخدم `url` في Cursor

---

## 📋 Endpoints المتاحة

1. **Initialize:** `/wp-json/awbu/v1/mcp/initialize`
2. **Server Info:** `/wp-json/awbu/v1/mcp/server-info`
3. **List Offerings:** `/wp-json/awbu/v1/mcp/list-offerings`
4. **Generic MCP:** `/wp-json/awbu/v1/mcp`
5. **SSE Stream:** `/wp-json/awbu/v1/mcp/stream`

---

## ✅ التحقق النهائي

بعد الإعداد، يجب أن ترى في Cursor/Antigravity:
- ✅ "AWBU MCP" في قائمة MCP Servers
- ✅ Status: Connected
- ✅ Tools: 18 tools available
- ✅ Resources: 2 resources available

---

## 🆘 الدعم

إذا استمرت المشاكل:
1. تحقق من سجلات الخادم (`error_log`)
2. تحقق من أن API key صحيح
3. تأكد من أن WordPress plugin مفعّل
4. جرب مسح الكاش من صفحة الإعدادات

