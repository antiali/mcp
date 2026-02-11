# WordPress REST API - Create Content
$SiteUrl = "https://zakharioustours.de"
$Username = "zakharious"
$AppPassword = "tDeO iZyX kOIo Eg4K kLd2 DPxx"

# Create Basic Auth header
$AuthHeader = [Convert]::ToBase64String([Text.Encoding]::ASCII.GetBytes("$Username`:$AppPassword"))

# Headers
$Headers = @{
    "Authorization" = "Basic $AuthHeader"
    "Content-Type" = "application/json"
}

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "YTrip Remote Content Creator" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check WordPress REST API
Write-Host "Step 1: Checking WordPress REST API..." -ForegroundColor Yellow
try {
    $Response = Invoke-WebRequest -Uri "$SiteUrl/wp-json/" -Method GET -Headers $Headers -UseBasicParsing -ErrorAction Stop
    $Data = $Response.Content | ConvertFrom-Json
    Write-Host "WordPress REST API is working" -ForegroundColor Green
    Write-Host "Site: $($Data.name)" -ForegroundColor White
    Write-Host ""
} catch {
    Write-Host "Error accessing WordPress" -ForegroundColor Red
    Write-Host "$($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
    Write-Host "Troubleshooting:" -ForegroundColor Yellow
    Write-Host "1. Check if site URL is correct: $SiteUrl" -ForegroundColor White
    Write-Host "2. Check if REST API is enabled" -ForegroundColor White
    Write-Host "3. Verify application password" -ForegroundColor White
    exit 1
}

# Check YTrip endpoints
Write-Host "Step 2: Checking YTrip endpoints..." -ForegroundColor Yellow
try {
    $Response = Invoke-WebRequest -Uri "$SiteUrl/wp-json/ytrip/v1/test-access" -Method GET -Headers $Headers -UseBasicParsing -ErrorAction Stop
    $Data = $Response.Content | ConvertFrom-Json
    Write-Host "YTrip REST API is available" -ForegroundColor Green
    Write-Host "User: $($Data.user)" -ForegroundColor White
    Write-Host ""
} catch {
    Write-Host "YTrip endpoints not found (404)" -ForegroundColor Red
    Write-Host ""
    Write-Host "REASON: New plugin code hasn't been deployed to server yet." -ForegroundColor Yellow
    Write-Host ""
    Write-Host "SOLUTION: Upload quick-fix.php to server:" -ForegroundColor Yellow
    Write-Host "1. Download from:" -ForegroundColor White
    Write-Host "   https://github.com/antiali/zakharioustours.de/blob/main/wp-content/plugins/ytrip/quick-fix.php" -ForegroundColor Cyan
    Write-Host "2. Upload to: /wp-content/plugins/ytrip/quick-fix.php" -ForegroundColor White
    Write-Host "3. Access: $SiteUrl/wp-content/plugins/ytrip/quick-fix.php" -ForegroundColor Cyan
    Write-Host "4. Click 'Flush Permalinks' button" -ForegroundColor White
    Write-Host ""
    exit 1
}

# Create Content
Write-Host "Step 3: Creating Demo Content..." -ForegroundColor Yellow

$Body = @{
    "num_categories" = 4
    "num_tours" = 4
} | ConvertTo-Json -Depth 3

try {
    $Response = Invoke-WebRequest -Uri "$SiteUrl/wp-json/ytrip/v1/create-content" -Method POST -Headers $Headers -Body $Body -UseBasicParsing
    $Data = $Response.Content | ConvertFrom-Json

    Write-Host "Content created successfully!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Created:" -ForegroundColor Cyan
    Write-Host "   Categories: $($Data.created.categories)" -ForegroundColor White
    Write-Host "   Destinations: $($Data.created.destinations)" -ForegroundColor White
    Write-Host "   Tours: $($Data.created.tours)" -ForegroundColor White
    Write-Host ""

    Write-Host "Complete!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Access:" -ForegroundColor Cyan
    Write-Host "   Tours: $SiteUrl/tours/" -ForegroundColor White
    Write-Host "   Admin: $SiteUrl/wp-admin/admin.php?page=ytrip-settings" -ForegroundColor White

} catch {
    Write-Host "Error creating content" -ForegroundColor Red
    Write-Host "$($_.Exception.Message)" -ForegroundColor Red

    if ($_.Exception.Response) {
        $StatusCode = $_.Exception.Response.StatusCode.value__
        Write-Host "HTTP Status: $StatusCode" -ForegroundColor Red

        if ($StatusCode -eq 401) {
            Write-Host ""
            Write-Host "Authentication failed. Check:" -ForegroundColor Yellow
            Write-Host "1. Username is correct" -ForegroundColor White
            Write-Host "2. Application password is correct" -ForegroundColor White
            Write-Host "3. Application password was created correctly in WordPress Admin" -ForegroundColor White
        }
        elseif ($StatusCode -eq 403) {
            Write-Host ""
            Write-Host "Permission denied. User does not have 'edit_posts' capability." -ForegroundColor Yellow
            Write-Host "Make sure user has Administrator role." -ForegroundColor White
        }
    }
}
