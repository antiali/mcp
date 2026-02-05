# Test MCP Connection for Cursor and Antigravity
# This script tests all MCP endpoints to ensure global compatibility

$baseUrl = "https://projects.muhamedahmed.com/peralite2/wp-json/awbu/v1"
$apiKey = "aCtqpmSwYWjgaFXwxwRgrc38rxc838Goeb9bnDnWh8EHrRs6xDz19FwiKdBckn4Y"

Write-Host "=== MCP Global Connection Test ===" -ForegroundColor Green
Write-Host ""

# Test 1: Initialize endpoint (for Cursor)
Write-Host "Test 1: Initialize endpoint (Cursor)" -ForegroundColor Cyan
try {
    $response = Invoke-WebRequest -Uri "$baseUrl/mcp/initialize" `
        -Method POST `
        -Headers @{
            "Content-Type" = "application/json"
            "X-MCP-API-Key" = $apiKey
        } `
        -Body '{"clientInfo": {"name": "Test Client", "version": "1.0"}}' `
        -UseBasicParsing
    
    if ($response.StatusCode -eq 200) {
        $json = $response.Content | ConvertFrom-Json
        Write-Host "✅ Initialize: SUCCESS" -ForegroundColor Green
        Write-Host "   Protocol Version: $($json.protocolVersion)" -ForegroundColor White
        Write-Host "   Server Name: $($json.serverInfo.name)" -ForegroundColor White
        Write-Host "   Server Version: $($json.serverInfo.version)" -ForegroundColor White
    } else {
        Write-Host "❌ Initialize: FAILED (Status: $($response.StatusCode))" -ForegroundColor Red
    }
} catch {
    Write-Host "❌ Initialize: ERROR - $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

# Test 2: Server Info endpoint (for Antigravity)
Write-Host "Test 2: Server Info endpoint (Antigravity)" -ForegroundColor Cyan
try {
    $response = Invoke-WebRequest -Uri "$baseUrl/mcp/server-info?api_key=$apiKey" `
        -Method GET `
        -UseBasicParsing
    
    if ($response.StatusCode -eq 200) {
        $json = $response.Content | ConvertFrom-Json
        Write-Host "✅ Server Info: SUCCESS" -ForegroundColor Green
        Write-Host "   Protocol Version: $($json.protocolVersion)" -ForegroundColor White
        Write-Host "   Server Name: $($json.serverInfo.name)" -ForegroundColor White
    } else {
        Write-Host "❌ Server Info: FAILED (Status: $($response.StatusCode))" -ForegroundColor Red
    }
} catch {
    Write-Host "❌ Server Info: ERROR - $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

# Test 3: List Offerings endpoint
Write-Host "Test 3: List Offerings endpoint" -ForegroundColor Cyan
try {
    $response = Invoke-WebRequest -Uri "$baseUrl/mcp/list-offerings?api_key=$apiKey" `
        -Method GET `
        -UseBasicParsing
    
    if ($response.StatusCode -eq 200) {
        $json = $response.Content | ConvertFrom-Json
        Write-Host "✅ List Offerings: SUCCESS" -ForegroundColor Green
        Write-Host "   Tools Count: $($json.tools.Count)" -ForegroundColor White
        Write-Host "   Resources Count: $($json.resources.Count)" -ForegroundColor White
    } else {
        Write-Host "❌ List Offerings: FAILED (Status: $($response.StatusCode))" -ForegroundColor Red
    }
} catch {
    Write-Host "❌ List Offerings: ERROR - $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

# Test 4: Generic MCP endpoint (for Cursor)
Write-Host "Test 4: Generic MCP endpoint (Cursor)" -ForegroundColor Cyan
try {
    $response = Invoke-WebRequest -Uri "$baseUrl/mcp?api_key=$apiKey" `
        -Method POST `
        -Headers @{
            "Content-Type" = "application/json"
        } `
        -Body '{"method": "tools/list"}' `
        -UseBasicParsing
    
    if ($response.StatusCode -eq 200) {
        $json = $response.Content | ConvertFrom-Json
        Write-Host "✅ Generic MCP: SUCCESS" -ForegroundColor Green
        Write-Host "   Response Type: $($json.GetType().Name)" -ForegroundColor White
    } else {
        Write-Host "❌ Generic MCP: FAILED (Status: $($response.StatusCode))" -ForegroundColor Red
    }
} catch {
    Write-Host "❌ Generic MCP: ERROR - $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

# Test 5: SSE Stream endpoint (for Cursor)
Write-Host "Test 5: SSE Stream endpoint (Cursor)" -ForegroundColor Cyan
try {
    $response = Invoke-WebRequest -Uri "$baseUrl/mcp/stream?api_key=$apiKey" `
        -Method GET `
        -Headers @{
            "Accept" = "text/event-stream"
        } `
        -UseBasicParsing `
        -TimeoutSec 5
    
    if ($response.StatusCode -eq 200) {
        $contentType = $response.Headers['Content-Type']
        if ($contentType -like "*text/event-stream*") {
            Write-Host "✅ SSE Stream: SUCCESS" -ForegroundColor Green
            Write-Host "   Content-Type: $contentType" -ForegroundColor White
        } else {
            Write-Host "⚠️ SSE Stream: Content-Type mismatch" -ForegroundColor Yellow
            Write-Host "   Expected: text/event-stream" -ForegroundColor White
            Write-Host "   Got: $contentType" -ForegroundColor White
        }
    } else {
        Write-Host "❌ SSE Stream: FAILED (Status: $($response.StatusCode))" -ForegroundColor Red
    }
} catch {
    Write-Host "❌ SSE Stream: ERROR - $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

Write-Host "=== Test Complete ===" -ForegroundColor Green
Write-Host ""
Write-Host "Configuration for Cursor:" -ForegroundColor Yellow
Write-Host '{' -ForegroundColor White
Write-Host '  "mcpServers": {' -ForegroundColor White
Write-Host '    "AWBU MCP": {' -ForegroundColor White
Write-Host "      `"url`": `"$baseUrl/mcp`"," -ForegroundColor White
Write-Host "      `"apiKey`": `"$apiKey`"" -ForegroundColor White
Write-Host '    }' -ForegroundColor White
Write-Host '  }' -ForegroundColor White
Write-Host '}' -ForegroundColor White
Write-Host ""
Write-Host "Configuration for Antigravity:" -ForegroundColor Yellow
Write-Host '{' -ForegroundColor White
Write-Host '  "mcpServers": {' -ForegroundColor White
Write-Host '    "AWBU MCP": {' -ForegroundColor White
Write-Host "      `"serverUrl`": `"$baseUrl/mcp`"," -ForegroundColor White
Write-Host "      `"apiKey`": `"$apiKey`"" -ForegroundColor White
Write-Host '    }' -ForegroundColor White
Write-Host '  }' -ForegroundColor White
Write-Host '}' -ForegroundColor White

