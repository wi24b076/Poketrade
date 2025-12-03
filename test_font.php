<?php // Poketrade/test_font.php ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Font Test</title>
    <style>
        @font-face {
            font-family: 'Pokemon Pixel Font';
            src: url('assets/fonts/pokemon.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        body {
            font-family: 'Pokemon Pixel Font', sans-serif;
            font-size: 32px;
        }
    </style>
</head>
<body>
    ABCDEFGHIJKLMNOPQRSTUVWXYZ<br>
    abcdefghijklmnopqrstuvwxyz<br>
    0123456789<br>
    Poketrade Font Test
</body>
</html>
