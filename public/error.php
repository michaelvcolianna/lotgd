<?php

$generic_header = $_SERVER['SERVER_PROTOCOL'] . ' 404 Page Not Found';
header($header ?? $generic_header, true, $code ?? 404);

?><!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Error: <?= $code ?? 404; ?></title>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&display=swap');
            body { font-family: 'Roboto Mono', monospace; font-size: 16px; margin: 0; text-align: center; }
        </style>
    </head>

    <body>
        <main>
            <h1>
                <?= $code ?? 404; ?>
            </h1>

            <p>
                <?= $message ?? 'Page Not Found'; ?>
            </p>
        </main>
    </body>
</html>
