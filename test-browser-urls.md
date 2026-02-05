# اختبار MCP Endpoints من المتصفح

## URLs للاختبار:

### 1. Server Info (GET)
```
https://projects.muhamedahmed.com/peralite2/wp-json/awbu/v1/mcp/server-info?api_key=aCtqpmSwYWjgaFXwxwRgrc38rxc838Goeb9bnDnWh8EHrRs6xDz19FwiKdBckn4Y
```

### 2. List Offerings (GET)
```
https://projects.muhamedahmed.com/peralite2/wp-json/awbu/v1/mcp/list-offerings?api_key=aCtqpmSwYWjgaFXwxwRgrc38rxc838Goeb9bnDnWh8EHrRs6xDz19FwiKdBckn4Y
```

### 3. Generic MCP Handler (GET)
```
https://projects.muhamedahmed.com/peralite2/wp-json/awbu/v1/mcp?api_key=aCtqpmSwYWjgaFXwxwRgrc38rxc838Goeb9bnDnWh8EHrRs6xDz19FwiKdBckn4Y
```

### 4. Initialize (POST - يحتاج Postman أو curl)
```
POST https://projects.muhamedahmed.com/peralite2/wp-json/awbu/v1/mcp/initialize
Headers:
  X-MCP-API-Key: aCtqpmSwYWjgaFXwxwRgrc38rxc838Goeb9bnDnWh8EHrRs6xDz19FwiKdBckn4Y
  Content-Type: application/json

Body:
{
  "protocolVersion": "2024-11-05",
  "capabilities": {},
  "clientInfo": {
    "name": "Test Client",
    "version": "1.0.0"
  }
}
```

## ملاحظات:
- ✅ يمكن استخدام `?api_key=...` في query parameter للاختبار من المتصفح
- ✅ جميع الـ endpoints تدعم GET و POST
- ✅ API key يمكن إرساله في header `X-MCP-API-Key` أو query parameter `api_key`

