<?php

$logged_in = session('authenticated') == $_ENV['APP_AUTH'] ?? 'lotgd';

// home doesn't exist, just a test for this fake login process
$template = $logged_in
    ? 'home'
    : 'login'
;

$errors = [];

if(!$logged_in && !empty($_POST))
{
    $db = DB::getInstance();
    $db->doQuery("select `acctid`, `password` from `accounts` where `login` = ?", [
        $_POST['username'],
    ]);
    $user = $db->getNext();
    $db = null;

    if($user && password_verify($_POST['password'], $user->password))
    {
        session('authenticated', $_ENV['APP_AUTH'] ?? 'lotgd');
        session('user', $user->acctid);

        header('Location:/');
        exit();
    }
    else
    {
        $errors[] = 'The supplied credentials don’t match. Please verify the spelling.';
    }
}

echo $twig->render('pages/' . $template . '.html.twig', [
    'errors' => $errors,
    'navigation' => [
        [
            'href' => '#',
            'text' => 'Create an account',
        ],
    ],
]);
