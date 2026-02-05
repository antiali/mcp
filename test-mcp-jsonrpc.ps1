# Test MCP JSON-RPC Format for Cursor
# This tests the exact format Cursor expects

$baseUrl = "https://projects.muhamedahmed.com/peralite2/wp-json/awbu/v1"
$apiKey = "aCtqpmSwYWjgaFXwxwRgrc38rxc838Goeb9bnDnWh8EHrRs6xDz19FwiKdBckn4Y"

Write-Host ""
Write-Host "╔══════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║        MCP JSON-RPC Format Test (Cursor Compatibility)     ║" -ForegroundColor Cyan
Write-Host "╚══════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

# Test 1: Initialize with JSON-RPC format
Write-Host "Test 1: Initialize with JSON-RPC format" -ForegroundColor Yellow
$jsonrpcRequest = @{
    jsonrpc = "2.0"
    id = 1
    method = "initialize"
    params = @{
        protocolVersion = "2024-11-05"
        capabilities = @{}
        clientInfo = @{
            name = "Cursor Test"
            version = "1.0.0"
        }
    }
} | ConvertTo-Json -Depth 10

try {
    $response = Invoke-WebRequest -Uri "$baseUrl/mcp/stream?api_key=$apiKey" `
        -Method POST `
        -Headers @{
            "Content-Type" = "application/json"
            "Accept" = "text/event-stream"
            "X-MCP-API-Key" = $apiKey
        } `
        -Body $jsonrpcRequest `
        -UseBasicParsing `
        -TimeoutSec 10
    
    Write-Host "  ✅ Status: $($response.StatusCode)" -ForegroundColor Green
    Write-Host "  Content-Type: $($response.Headers['Content-Type'])" -ForegroundColor Gray
    
    # Check if response contains JSON-RPC format
    $content = $response.Content
    if ($content -match '"jsonrpc"\s*:\s*"2.0"') {
        Write-Host "  ✅ JSON-RPC format detected" -ForegroundColor Green
    } else {
        Write-Host "  ⚠️  JSON-RPC format not found" -ForegroundColor Yellow
    }
    
    if ($content -match '"id"\s*:\s*1') {
        Write-Host "  ✅ Request ID matched" -ForegroundColor Green
    }
    
    if ($content -match '"result"') {
        Write-Host "  ✅ Result field present" -ForegroundColor Green
    }
    
    Write-Host "  Response length: $($content.Length) bytes" -ForegroundColor Gray
} catch {
    Write-Host "  ❌ Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""

# Test 2: Tools/List with JSON-RPC format
Write-Host "Test 2: Tools/List with JSON-RPC format" -ForegroundColor Yellow
$jsonrpcRequest2 = @{
    jsonrpc = "2.0"
    id = 2
    method = "tools/list"
    params = @{}
} | ConvertTo-Json -Depth 10

try {
    $response2 = Invoke-WebRequest -Uri "$baseUrl/mcp/stream?api_key=$apiKey" `
        -Method POST `
        -Headers @{
            "Content-Type" = "application/json"
            "Accept" = "text/event-stream"
            "X-MCP-API-Key" = $apiKey
        } `
        -Body $jsonrpcRequest2 `
        -UseBasicParsing `
        -TimeoutSec 10
    
    Write-Host "  ✅ Status: $($response2.StatusCode)" -ForegroundColor Green
    Write-Host "  Content-Type: $($response2.Headers['Content-Type'])" -ForegroundColor Gray
    
    $content2 = $response2.Content
    if ($content2 -match '"jsonrpc"\s*:\s*"2.0"') {
        Write-Host "  ✅ JSON-RPC format detected" -ForegroundColor Green
    }
    
    if ($content2 -match '"id"\s*:\s*2') {
        Write-Host "  ✅ Request ID matched" -ForegroundColor Green
    }
    
    if ($content2 -match '"tools"') {
        Write-Host "  ✅ Tools field present" -ForegroundColor Green
    }
} catch {
    Write-Host "  ❌ Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "╔══════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║                    Test Complete                             ║" -ForegroundColor Cyan
Write-Host "╚══════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

