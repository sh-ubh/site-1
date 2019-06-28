<?php
require __DIR__.'/../vendor/autoload.php';
require_once "email/smtp.php";

function forbidden_name($name) {
    return in_array($name, [
        '0x0',
        'abuse',
        'admin',
        'administrator',
        'auth',
        'autoconfig',
        'bbj',
        'broadcasthost',
        'cloud',
        'forum',
        'ftp',
        'git',
        'gopher',
        'hostmaster',
        'imap',
        'info',
        'irc',
        'is',
        'isatap',
        'it',
        'localdomain',
        'localhost',
        'lounge',
        'mail',
        'mailer-daemon',
        'marketing',
        'marketting',
        'mis',
        'news',
        'nobody',
        'noc',
        'noreply',
        'pop',
        'pop3',
        'postmaster',
        'retro',
        'root',
        'sales',
        'security',
        'smtp',
        'ssladmin',
        'ssladministrator',
        'sslwebmaster',
        'support',
        'sysadmin',
        'team',
        'usenet',
        'uucp',
        'webmaster',
        'wpad',
        'www',
        'znc',
    ]);
}

$message = "";
if (isset($_REQUEST["username"]) && isset($_REQUEST["email"])) {
    // Check the name.
    $name = trim($_REQUEST["username"]);
    if ($name == "")
        $message .= "<li>please fill in your desired username</li>";

    if (strlen($name) > 32)
        $message .= "<li>username too long (32 character max)</li>";

    if (!preg_match('/^[a-z][a-z0-9]{2,31}$/', $name))
        $message .= "<li>username contains invalid characters (lowercase only, must start with a letter)</li>";

    if ($_REQUEST["sshkey"] == "")
        $message .= '<li>ssh key required: please create one and submit the public key. '
            . 'see our <a href="https://tilde.team/wiki/?page=ssh">ssh wiki</a> or '
            . 'hop on <a href="https://web.tilde.chat/?join=team">irc</a> and ask for help</li>';

    if ($_REQUEST["interest"] == "")
        $message .= "<li>please explain why you're interested so we can make sure you're a real human being</li>";

    if (posix_getpwnam($name) || forbidden_name($name))
        $message .= "<li>sorry, the username $name is unavailable</li>";

    // Check the e-mail address.
    $email = trim($_REQUEST["email"]);
    if ($email == "")
        $message .= "<li>please fill in your email address</li>";
    else {
        $result = SMTP::MakeValidEmailAddress($_REQUEST["email"]);
        if (!$result["success"])
            $message .= "<li>invalid email address: " . htmlspecialchars($result["error"]) . "</li>";
        elseif ($result["email"] != $email)
            $message .= "<li>invalid email address. did you mean:  " . htmlspecialchars($result["email"]) . "</li>";
    }


    // no validation errors
    if ($message == "") { 
        $msgbody = "
username: {$_REQUEST["username"]}
email: {$_REQUEST["email"]}
reason: {$_REQUEST["interest"]}

makeuser {$_REQUEST["username"]} {$_REQUEST["email"]} \"{$_REQUEST["sshkey"]}\"
";

        if (mail('sudoers', 'new tilde.team signup', $msgbody)) {
            echo '<div class="alert alert-success" role="alert">
                    email sent! we\'ll get back to you soon (usually within a day) with login instructions! <a href="/">back to tilde.team home</a>
                  </div>';
        } else {
            echo '<div class="alert alert-danger" role="alert">
                    something went wrong... please send an email to <a href="mailto:sudoers@tilde.team">sudoers@tilde.team</a> with details of what happened
                  </div>';
        }

    } else {
        ?>
        <div class="alert alert-warning" role="alert">
            <strong>please correct the following errors: </strong>
            <?=$message?>
        </div>
        <?php
    }
}
?>

