<?php

$errors = [
    'username' => false,
    'password' => false,
];
$complete = false;

if(!empty($_POST))
{
    if(!isset($_POST['username']) || empty($_POST['username']) || strlen($_POST['username']) < 3 || strlen($_POST['username']) > 25)
    {
        $errors['username'] = true;
    }

    if(!isset($_POST['password']) || empty($_POST['password']) || strlen($_POST['password']) < 8)
    {
        $errors['password'] = true;
    }

    if(!isset($_POST['password_confirm']) || empty($_POST['password_confirm']))
    {
        $errors['password'] = true;
    }

    if(!$errors['password'] && $_POST['password'] != $_POST['password_confirm'])
    {
        $errors['password'] = true;
    }

    if(!$errors['username'] && !$errors['password'])
    {
        require WEB_PATH . '/lib/constants.php';

        $su = SU_MEGAUSER | SU_EDIT_MOUNTS | SU_EDIT_CREATURES |
            SU_EDIT_PETITIONS | SU_EDIT_COMMENTS | SU_EDIT_DONATIONS |
            SU_EDIT_USERS | SU_EDIT_CONFIG | SU_INFINITE_DAYS |
            SU_EDIT_EQUIPMENT | SU_EDIT_PAYLOG | SU_DEVELOPER | SU_POST_MOTD |
            SU_MODERATE_CLANS | SU_EDIT_RIDDLES | SU_MANAGE_MODULES |
            SU_AUDIT_MODERATION | SU_RAW_SQL | SU_VIEW_SOURCE | SU_NEVER_EXPIRE;

        $password = password_hash($_POST['password'], PASSWORD_BCRYPT, [
            'cost' => $_ENV['APP_BCRYPT'] ?? 10,
        ]);

        $db = DB::getInstance();
        $db->doQuery("delete from `accounts` where `login` = ?", [
            $_POST['username'],
        ])->clear();
        $db->doQuery("insert into `accounts` (`login`, `password`, `superuser`, `name`, `ctitle`, `regdate`) values (?, ?, ?, ?, '`%Admin`0', now())", [
            $_POST['username'],
            $password,
            $su,
            '`%Admin `&' . $_POST['username'] . '`0',
        ]);

        if($db->getError())
        {
            error_page(
                $_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error',
                500,
                $db->getError()->getMessage()
            );
        }

        $db = null;

        $complete = true;
    }
}

?><!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>Installer</title>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Roboto+Mono:wght@400;700&display=swap');
            body, input, button { font-family: 'Roboto Mono', monospace; font-size: 16px; }
            body { margin: 0; text-align: center; }
            form div { margin: 0.5em 0; }
            form em { display: block; }
        </style>
    </head>

    <body>
        <main>
            <h1>
                Installer <?= $complete ? 'Complete' : ''; ?>
            </h1>

            <?php if($complete): ?>
                <p>
                    Installation is complete.
                    <a href="/">Start the game.</a>
                </p>
            <?php else: ?>
                <p>
                    <?php if(!$errors['username'] && !$errors['password']): ?>
                        The .env file is present, the values in it work, and the
                        SQL file has been imported to the database. The only
                        step remaining is to create a superuser account.
                    <?php else: ?>
                        <?php if($errors['username']): ?>
                            There is a problem with the provided username.
                        <?php endif; ?>

                        <?php if($errors['password']): ?>
                            There is a problem with the provided password.
                        <?php endif; ?>
                    <?php endif; ?>
                </p>

                <form action="/" method="post">
                    <div>
                        <label for="username">Username</label>
                        <input id="username" name="username" type="text"
                            autocomplete="username"
                            value="<?= $_POST['username'] ?? ''; ?>"
                            required <?= !$errors['password'] ? 'autofocus' : ''; ?>
                            />
                        <em>
                            Username must be between 3 and 25 characters.
                        </em>
                    </div>

                    <div>
                        <label for="password">Password</label>
                        <input id="password" name="password" type="password"
                            autocomplete="new-password" required
                            <?= $errors['password'] ? 'autofocus' : ''; ?> />
                    </div>

                    <div>
                        <label for="password-confirm">Confirm Password</label>
                        <input id="password-confirm" name="password_confirm"
                            type="password" autocomplete="new-password" required />
                        <em>
                            Password must be at least 8 characters. It is
                            recommended to use a mix of letters, numbers, case,
                            and symbols.
                        </em>
                    </div>

                    <button type="submit">Create Superuser</button>
                </form>
            <?php endif; ?>
        </main>
    </body>
</html>
