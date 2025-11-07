@isset($order)
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            padding: 20px;
            margin: 0;
        }

        .container {
            background-color: #fff;
            padding: 20px;
            margin: 0 auto;
            max-width: 600px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #007bff;
            font-size: 24px;
        }

        p {
            font-size: 16px;
            line-height: 1.6;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .signature {
            margin-top: 20px;
            font-size: 16px;
        }

        .signature p {
            margin: 5px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Уважаемый клиент,</h1>
        <p>{{ $order->getStatus->message_ru }}</p>
        <p>Заказ: <a
                href="https://optomall.uz/purchases?filter=all&orderId={{ $order->unique_id }}">{{ $order->unique_id }}</a>
        </p>
        <p>Спасибо за покупки у нас!</p>
        <div class="signature">
            <p>С наилучшими пожеланиями,</p>
            <p>Optomall.uz</p>
        </div>order
    </div>
</body>

</html>
@endisset