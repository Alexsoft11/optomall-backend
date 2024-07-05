<!DOCTYPE html>
<html>
<head>
    <title>Google Login Successful</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        h1 {
            color: #333;
        }
        p {
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h1>Salom, {{ $userName }}</h1>
    <p>Siz muvoffaqiyatli ro'yxatdan o'tdingiz.</p>
    <p><strong>Email:</strong> {{ $userEmail }}</p>
    <p><strong>Parol:</strong> {{ $userPassword }}</p>
    <p>Siz istalgan vaqtda ushbu vaqtinchalik parolni o'zgartia olasiz</p>
    <p>Bizni tanlaganingiz uchun raxmat!</p>
    <p>Hurmat ila OnWay jamoasi</p>
</body>
</html>
