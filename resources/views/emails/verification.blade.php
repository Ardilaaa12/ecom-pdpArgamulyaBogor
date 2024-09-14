<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .code {
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
        }
        .footer a {
            color: #777;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Yuk masuk ke akun kamu!</h2>
        <p>selamat datang {{ $username}}, untuk menyelesaikan sesi registrasi kamu, silahkan masukan code di website kami untuk verifikasi email yaa</p>

        <div class="code">
            {{ $token }}
        </div>

        <p>Kamu menerima email ini karena Kamu telah membuat akun di situs web kami. Email ini bukan email pemasaran atau promosi. Kami harap Kamu memiliki pengalaman berbelanja yang menyenangkan. Terima kasih telah bergabung dengan kami!</p>

        <div class="footer">
            <p>PDP Argamulya Bogor</p>
            <p>Made with ❤️ from Us</p>
            <p>pdpArgamulyaBogor®, Bogor, Jawa Barat</p>
        </div>
    </div>
</body>
</html>
