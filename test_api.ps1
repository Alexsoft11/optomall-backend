$ErrorActionPreference = "Stop"
$baseUrl = "http://127.0.0.1:8000/api"

$headers = @{}

Write-Host "--- API Verification Test ---"

# 1. Login
Write-Host "1. Testing Login..."
$loginBody = @{
    email    = "test@test.com"
    password = "password123"
} | ConvertTo-Json

try {
    $loginResponse = Invoke-RestMethod -Uri "$baseUrl/login" -Method Post -Body $loginBody -ContentType "application/json"
    $token = $loginResponse.token
    if (-not $token) {
        throw "Token not found in login response"
    }
    Write-Host "   [PASS] Login Successful. Token received."
    $headers = @{ Authorization = "Bearer $token" }
}
catch {
    $e = $_.Exception
    if ($e.Response) {
        $reader = New-Object System.IO.StreamReader($e.Response.GetResponseStream())
        $responseBody = $reader.ReadToEnd()
        Write-Error "   [FAIL] Login Failed: $($e.Message)"
        Write-Error "          Response Body: $responseBody"
    }
    else {
        Write-Error "   [FAIL] Login Failed: $_"
    }
    exit 1
}

# 2. Get Products
Write-Host "`n2. Testing GET /products..."
try {
    $productsResponse = Invoke-RestMethod -Uri "$baseUrl/products" -Method Get -Headers $headers
    
    # Check structure
    if ($productsResponse.products -is [PSCustomObject] -and $productsResponse.products.data) {
        $products = $productsResponse.products.data
    }
    elseif ($productsResponse.products -is [Array]) {
        $products = $productsResponse.products
    }
    elseif ($productsResponse.data) {
        $products = $productsResponse.data
    }
    else {
        $products = @()
    }
    
    if ($products.Count -gt 0) {
        Write-Host "   [PASS] Products fetched. Count: $($products.Count)"
        $firstProductId = $products[0].id
        Write-Host "          First Product ID: $firstProductId"
    }
    else {
        Write-Warning "   [WARN] No products found (but request succeeded)."
    }
}
catch {
    Write-Error "   [FAIL] GET /products Failed: $_"
    exit 1
}

# 3. Get Product Detail
if ($firstProductId) {
    Write-Host "`n3. Testing GET /product-detail for ID: $firstProductId..."
    try {
        $detailResponse = Invoke-RestMethod -Uri "$baseUrl/product-detail?productId=$firstProductId" -Method Get -Headers $headers
        if ($detailResponse.item) {
            Write-Host "   [PASS] Product Detail fetched successfully."
            Write-Host "          Title: $($detailResponse.item.title)"
        }
        elseif ($detailResponse.id) {
            Write-Host "   [PASS] Product Detail fetched successfully (direct object)."
            Write-Host "          Title: $($detailResponse.title)"
        }
        else {
            Write-Warning "   [WARN] Product detail fetching succeeded but 'item' missing (might be 404 or bad structure). Response keys: $($detailResponse | Get-Member -MemberType NoteProperty | Select-Object -ExpandProperty Name)"
        }
    }
    catch {
        Write-Error "   [FAIL] GET /product-detail Failed: $_"
    }
}

# 4. Get Categories
Write-Host "`n4. Testing GET /categories-from-taobao..."
try {
    $categoriesResponse = Invoke-RestMethod -Uri "$baseUrl/categories-from-taobao" -Method Get -Headers $headers
    $catCount = if ($categoriesResponse.data) { $categoriesResponse.data.Count } else { $categoriesResponse.Count }
    Write-Host "   [PASS] Categories fetched successfully. Count: $catCount"
}
catch {
    Write-Error "   [FAIL] GET /categories-from-taobao Failed: $_"
}

Write-Host "`n--- Verification Complete ---"
