<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Optomall.uz</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 24px;
            color: #333333;
            text-align: center;
            margin-bottom: 20px;
        }
        p {
            font-size: 16px;
            color: #555555;
            line-height: 1.5;
            margin: 10px 0;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .btn {
            display: inline-block;
            background-color: #ffffff;
            border: 1px solid blue;
            color: #ffffff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-size: 16px;
            margin-top: 20px;
            text-align: center;
        }
        .btn:hover {
            background-color: #cad4de;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #999999;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Новый заказ</h1>
        <p>На сайте принят новый заказ</p>
        <p>Заказ: <a href="https://api.optomall.uz/admin/order-groups">{{ $orderGroupId }}</a></p>
        <a href="https://api.optomall.uz/admin/order-groups" class="btn">Посмотреть заказ</a>
    </div>
    <div class="footer">
        <p>&copy; 2024 Optomall.uz. Все права защищены.</p>
    </div>
</body>
</html>
