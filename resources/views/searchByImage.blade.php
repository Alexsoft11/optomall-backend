
<?php

function searchByImage($imageData) {
    // Taobao API
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://1688-product2.p.rapidapi.com/1688/search/image",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_POSTFIELDS => http_build_query(["img_url" => $imageData, "page" => 1]),
        CURLOPT_HTTPHEADER => [
            "X-RapidAPI-Host: 1688-product2.p.rapidapi.com",
            "X-RapidAPI-Key: ad1fe6ee4amsh7622e16403e7570p12c467jsnae077066fec7"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        return "cURL Error #:" . $err;
    } else {
        return $response;
    }
}

// Check if form is submitted
if (isset($_POST['submit'])) {
    // Check if file was uploaded without errors
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $tempName = $_FILES["image"]["tmp_name"];
        $imageData = base64_encode(file_get_contents($tempName));
        $searchResult = searchByImage($_FILES['image']['tmp_name']);
        echo $searchResult;
    } else {
        echo "Error: No image uploaded or an error occurred while uploading.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Image</title>
</head>
<body>
    <h2>Upload Image</h2>
    <form action="/1688" method="post" enctype="multipart/form-data">
        @csrf
        <input type="file" name="image" id="image">
        <input type="submit" value="Search" name="submit">
    </form>
</body>
</html>


