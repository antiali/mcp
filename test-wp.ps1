# Test WordPress REST API Access
$username = "zakharious"
$password = "tDeO iZyX kOIo Eg4K kLd2 DPxx"
$base64Auth = [Convert]::ToBase64String([Text.Encoding]::ASCII.GetBytes("${username}:${password}"))

$headers = @{
    "Authorization" = "Basic $base64Auth"
    "Content-Type" = "application/json"
}

# Test Access
Write-Host "Testing REST API Access..."
try {
    $response = Invoke-WebRequest -Uri "https://zakharioustours.de/wp-json/ytrip/v1/test-access" -Method GET -Headers $headers
    $data = $response.Content | ConvertFrom-Json
    Write-Host "Status: $($data.status)"
    Write-Host "User: $($data.user)"
    Write-Host "Time: $($data.time)"
} catch {
    Write-Host "Error: $($_.Exception.Message)"
}

# Create Content
Write-Host "`nCreating Demo Content..."
$body = @{
    "num_categories" = 4
    "num_tours" = 4
} | ConvertTo-Json -Depth 3

try {
    $response = Invoke-WebRequest -Uri "https://zakharioustours.de/wp-json/ytrip/v1/create-content" -Method POST -Headers $headers -Body $body
    $data = $response.Content | ConvertFrom-Json
    Write-Host "Created Categories: $($data.created.categories)"
    Write-Host "Created Destinations: $($data.created.destinations)"
    Write-Host "Created Tours: $($data.created.tours)"
} catch {
    Write-Host "Error creating content: $($_.Exception.Message)"
}
