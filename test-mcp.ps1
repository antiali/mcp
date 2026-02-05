# Test MCP Endpoints using PowerShell

# Colors for output
$GREEN = "`e[32m"
$RED = "`e[31m"
$YELLOW = "`e[33m"
$NC = "`e[0m"

Write-Host "=== MCP Connection Test ===" -ForegroundColor Cyan
Write-Host ""

# Site URL - adjust this to your WordPress site
$SITE_URL = "https://projects.muhamedahmed.com/peralite2"
$REST_BASE = "$SITE_URL/wp-json/awbu/v1"

# API Key - From mcp.json
$API_KEY = "aCtqpmSwYWjgaFXwxwRgrc38rxc838Goeb9bnDnWh8EHrRs6xDz19FwiKdBckn4Y"

Write-Host "Testing endpoints on: $REST_BASE" -ForegroundColor Yellow
Write-Host "Using API Key: $($API_KEY.Substring(0, 10))..." -ForegroundColor Yellow
Write-Host ""

# Headers with API key
$headers = @{
    "Content-Type" = "application/json"
    "X-MCP-API-Key" = $API_KEY
}

# Test 1: GET /mcp/server-info
Write-Host "Test 1: GET /mcp/server-info" -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$REST_BASE/mcp/server-info" -Method Get -Headers $headers -ErrorAction Stop
    Write-Host "✅ Status: 200" -ForegroundColor Green
    if ($response.serverInfo) {
        Write-Host "✅ Server Info Found!" -ForegroundColor Green
        Write-Host "   Name: $($response.serverInfo.name)" -ForegroundColor Green
        Write-Host "   Version: $($response.serverInfo.version)" -ForegroundColor Green
        Write-Host ($response | ConvertTo-Json -Depth 5)
    } else {
        Write-Host "❌ Server Info Missing!" -ForegroundColor Red
        Write-Host ($response | ConvertTo-Json -Depth 5)
    }
} catch {
    Write-Host "❌ Error: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.Exception.Response) {
        Write-Host "   Status Code: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Red
    }
}
Write-Host ""

# Test 2: POST /mcp/server-info
Write-Host "Test 2: POST /mcp/server-info" -ForegroundColor Yellow
try {
    $body = @{ method = "server-info" } | ConvertTo-Json
    $response = Invoke-RestMethod -Uri "$REST_BASE/mcp/server-info" -Method Post -Headers $headers -Body $body -ErrorAction Stop
    Write-Host "✅ Status: 200" -ForegroundColor Green
    if ($response.serverInfo) {
        Write-Host "✅ Server Info Found!" -ForegroundColor Green
        Write-Host ($response | ConvertTo-Json -Depth 5)
    } else {
        Write-Host "❌ Server Info Missing!" -ForegroundColor Red
        Write-Host ($response | ConvertTo-Json -Depth 5)
    }
} catch {
    Write-Host "❌ Error: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.Exception.Response) {
        Write-Host "   Status Code: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Red
    }
}
Write-Host ""

# Test 3: GET /mcp/serverInfo (camelCase)
Write-Host "Test 3: GET /mcp/serverInfo (camelCase)" -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$REST_BASE/mcp/serverInfo" -Method Get -Headers $headers -ErrorAction Stop
    Write-Host "✅ Status: 200" -ForegroundColor Green
    if ($response.serverInfo) {
        Write-Host "✅ Alternative Endpoint Works!" -ForegroundColor Green
        Write-Host ($response | ConvertTo-Json -Depth 5)
    } else {
        Write-Host "❌ Server Info Missing!" -ForegroundColor Red
        Write-Host ($response | ConvertTo-Json -Depth 5)
    }
} catch {
    Write-Host "❌ Error: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.Exception.Response) {
        Write-Host "   Status Code: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Red
    }
}
Write-Host ""

# Test 4: POST /mcp/initialize
Write-Host "Test 4: POST /mcp/initialize" -ForegroundColor Yellow
try {
    $body = @{
        protocolVersion = "2024-11-05"
        capabilities = @{}
        clientInfo = @{
            name = "Test Client"
            version = "1.0.0"
        }
    } | ConvertTo-Json -Depth 5
    $response = Invoke-RestMethod -Uri "$REST_BASE/mcp/initialize" -Method Post -Headers $headers -Body $body -ErrorAction Stop
    Write-Host "✅ Status: 200" -ForegroundColor Green
    if ($response.serverInfo) {
        Write-Host "✅ Initialize Success!" -ForegroundColor Green
        Write-Host "   Protocol Version: $($response.protocolVersion)" -ForegroundColor Green
        Write-Host "   Server Name: $($response.serverInfo.name)" -ForegroundColor Green
        Write-Host ($response | ConvertTo-Json -Depth 5)
    } else {
        Write-Host "❌ Initialize Failed - Missing serverInfo!" -ForegroundColor Red
        Write-Host ($response | ConvertTo-Json -Depth 5)
    }
} catch {
    Write-Host "❌ Error: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.Exception.Response) {
        Write-Host "   Status Code: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Red
    }
}
Write-Host ""

# Test 5: POST /mcp (generic handler)
Write-Host "Test 5: POST /mcp (with method: server-info)" -ForegroundColor Yellow
try {
    $body = @{ method = "server-info" } | ConvertTo-Json
    $response = Invoke-RestMethod -Uri "$REST_BASE/mcp" -Method Post -Headers $headers -Body $body -ErrorAction Stop
    Write-Host "✅ Status: 200" -ForegroundColor Green
    if ($response.serverInfo) {
        Write-Host "✅ Generic Handler Works!" -ForegroundColor Green
        Write-Host ($response | ConvertTo-Json -Depth 5)
    } else {
        Write-Host "❌ Generic Handler Failed!" -ForegroundColor Red
        Write-Host ($response | ConvertTo-Json -Depth 5)
    }
} catch {
    Write-Host "❌ Error: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.Exception.Response) {
        Write-Host "   Status Code: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Red
    }
}
Write-Host ""

Write-Host "=== Test Complete ===" -ForegroundColor Cyan

