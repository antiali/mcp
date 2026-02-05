# Final MCP Connection Test for Cursor and Antigravity
# Comprehensive test to verify all endpoints work correctly

$baseUrl = "https://projects.muhamedahmed.com/peralite2/wp-json/awbu/v1"
$apiKey = "aCtqpmSwYWjgaFXwxwRgrc38rxc838Goeb9bnDnWh8EHrRs6xDz19FwiKdBckn4Y"

Write-Host ""
Write-Host "╔══════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║     MCP Global Connection Test - Cursor & Antigravity      ║" -ForegroundColor Cyan
Write-Host "╚══════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

$testsPassed = 0
$testsFailed = 0
$totalTests = 0

function Test-Endpoint {
    param(
        [string]$Name,
        [string]$Url,
        [string]$Method = "GET",
        [hashtable]$Headers = @{},
        [string]$Body = $null,
        [string]$ExpectedContentType = $null
    )
    
    $script:totalTests++
    Write-Host "Testing: $Name" -ForegroundColor Yellow
    Write-Host "  URL: $Url" -ForegroundColor Gray
    
    try {
        $params = @{
            Uri = $Url
            Method = $Method
            Headers = $Headers
            UseBasicParsing = $true
            ErrorAction = "Stop"
        }
        
        if ($Body) {
            $params.Body = $Body
        }
        
        $response = Invoke-WebRequest @params
        
        if ($response.StatusCode -eq 200) {
            $contentType = $response.Headers['Content-Type']
            
            if ($ExpectedContentType -and $contentType -notlike "*$ExpectedContentType*") {
                Write-Host "  ⚠️  WARNING: Content-Type mismatch" -ForegroundColor Yellow
                Write-Host "     Expected: $ExpectedContentType" -ForegroundColor Gray
                Write-Host "     Got: $contentType" -ForegroundColor Gray
                $script:testsFailed++
                return $false
            }
            
            # Try to parse JSON
            try {
                $json = $response.Content | ConvertFrom-Json
                Write-Host "  ✅ SUCCESS" -ForegroundColor Green
                Write-Host "     Status: $($response.StatusCode)" -ForegroundColor Gray
                Write-Host "     Content-Type: $contentType" -ForegroundColor Gray
                
                # Show key info if available
                if ($json.protocolVersion) {
                    Write-Host "     Protocol: $($json.protocolVersion)" -ForegroundColor Gray
                }
                if ($json.serverInfo) {
                    Write-Host "     Server: $($json.serverInfo.name) v$($json.serverInfo.version)" -ForegroundColor Gray
                }
                if ($json.tools) {
                    Write-Host "     Tools: $($json.tools.Count)" -ForegroundColor Gray
                }
                if ($json.resources) {
                    Write-Host "     Resources: $($json.resources.Count)" -ForegroundColor Gray
                }
                
                $script:testsPassed++
                return $true
            } catch {
                # Not JSON, but status is 200
                Write-Host "  ✅ SUCCESS (Non-JSON response)" -ForegroundColor Green
                Write-Host "     Status: $($response.StatusCode)" -ForegroundColor Gray
                Write-Host "     Content-Type: $contentType" -ForegroundColor Gray
                Write-Host "     Content Length: $($response.Content.Length) bytes" -ForegroundColor Gray
                $script:testsPassed++
                return $true
            }
        } else {
            Write-Host "  ❌ FAILED: Status $($response.StatusCode)" -ForegroundColor Red
            $script:testsFailed++
            return $false
        }
    } catch {
        Write-Host "  ❌ ERROR: $($_.Exception.Message)" -ForegroundColor Red
        if ($_.Exception.Response) {
            $statusCode = $_.Exception.Response.StatusCode.value__
            Write-Host "     Status Code: $statusCode" -ForegroundColor Gray
        }
        $script:testsFailed++
        return $false
    }
    Write-Host ""
}

# Test 1: Initialize (Cursor)
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
Test-Endpoint `
    -Name "1. Initialize Endpoint (Cursor)" `
    -Url "$baseUrl/mcp/initialize" `
    -Method "POST" `
    -Headers @{
        "Content-Type" = "application/json"
        "X-MCP-API-Key" = $apiKey
    } `
    -Body '{"clientInfo": {"name": "Test Client", "version": "1.0"}}'

Write-Host ""

# Test 2: Server Info (Antigravity)
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
Test-Endpoint `
    -Name "2. Server Info Endpoint (Antigravity)" `
    -Url "$baseUrl/mcp/server-info?api_key=$apiKey" `
    -Method "GET"

Write-Host ""

# Test 3: Server Info POST (Alternative)
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
Test-Endpoint `
    -Name "3. Server Info POST (Alternative)" `
    -Url "$baseUrl/mcp/server-info" `
    -Method "POST" `
    -Headers @{
        "Content-Type" = "application/json"
        "X-MCP-API-Key" = $apiKey
    } `
    -Body '{}'

Write-Host ""

# Test 4: List Offerings
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
Test-Endpoint `
    -Name "4. List Offerings Endpoint" `
    -Url "$baseUrl/mcp/list-offerings?api_key=$apiKey" `
    -Method "GET"

Write-Host ""

# Test 5: Generic MCP - Tools List (Cursor)
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
Test-Endpoint `
    -Name "5. Generic MCP - Tools List (Cursor)" `
    -Url "$baseUrl/mcp?api_key=$apiKey" `
    -Method "POST" `
    -Headers @{
        "Content-Type" = "application/json"
    } `
    -Body '{"method": "tools/list"}'

Write-Host ""

# Test 6: Generic MCP - Initialize (Cursor)
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
Test-Endpoint `
    -Name "6. Generic MCP - Initialize (Cursor)" `
    -Url "$baseUrl/mcp?api_key=$apiKey" `
    -Method "POST" `
    -Headers @{
        "Content-Type" = "application/json"
    } `
    -Body '{"method": "initialize", "params": {"clientInfo": {"name": "Test", "version": "1.0"}}}'

Write-Host ""

# Test 7: SSE Stream (Cursor)
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
Test-Endpoint `
    -Name "7. SSE Stream Endpoint (Cursor)" `
    -Url "$baseUrl/mcp/stream?api_key=$apiKey" `
    -Method "GET" `
    -Headers @{
        "Accept" = "text/event-stream"
    } `
    -ExpectedContentType "text/event-stream"

Write-Host ""

# Test 8: SSE Streamable (Alternative)
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
Test-Endpoint `
    -Name "8. SSE Streamable Endpoint (Alternative)" `
    -Url "$baseUrl/mcp/streamable?api_key=$apiKey" `
    -Method "GET" `
    -Headers @{
        "Accept" = "text/event-stream"
    } `
    -ExpectedContentType "text/event-stream"

Write-Host ""

# Test 9: MCP with SSE header (Cursor compatibility)
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor DarkGray
Test-Endpoint `
    -Name "9. MCP Endpoint with SSE Accept Header" `
    -Url "$baseUrl/mcp?api_key=$apiKey" `
    -Method "GET" `
    -Headers @{
        "Accept" = "text/event-stream"
    } `
    -ExpectedContentType "text/event-stream"

Write-Host ""

# Summary
Write-Host "╔══════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║                        Test Summary                         ║" -ForegroundColor Cyan
Write-Host "╚══════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""
Write-Host "Total Tests: $totalTests" -ForegroundColor White
Write-Host "Passed: $testsPassed" -ForegroundColor Green
Write-Host "Failed: $testsFailed" -ForegroundColor $(if ($testsFailed -eq 0) { "Green" } else { "Red" })
Write-Host ""

if ($testsFailed -eq 0) {
    Write-Host "🎉 All tests passed! MCP is ready for Cursor and Antigravity!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Configuration for Cursor:" -ForegroundColor Yellow
    Write-Host @"
{
  "mcpServers": {
    "AWBU MCP": {
      "url": "$baseUrl/mcp",
      "apiKey": "$apiKey"
    }
  }
}
"@ -ForegroundColor White
    Write-Host ""
    Write-Host "Configuration for Antigravity:" -ForegroundColor Yellow
    Write-Host @"
{
  "mcpServers": {
    "AWBU MCP": {
      "serverUrl": "$baseUrl/mcp",
      "apiKey": "$apiKey"
    }
  }
}
"@ -ForegroundColor White
} else {
    Write-Host "⚠️  Some tests failed. Please check the errors above." -ForegroundColor Yellow
}

Write-Host ""

