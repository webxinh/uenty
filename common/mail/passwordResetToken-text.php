<?php

/* @var $this aabc\web\View */
/* @var $user common\models\User */

$resetLink = Aabc::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->password_reset_token]);
?>
Hello <?= $user->username ?>,

Follow the link below to reset your password:

<?= $resetLink ?>
