<?php
/* @var $caption string */
/* @var $values array */

use aabc\helpers\Html;
use aabc\helpers\VarDumper;
?>
<h3><?= $caption ?></h3>

<?php if (empty($values)): ?>

    <p>Empty.</p>

<?php else:	?>

    <div class="table-responsive">
        <table class="table table-condensed table-bordered table-striped table-hover request-table" style="table-layout: fixed;">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($values as $name => $value): ?>
                <tr>
                    <th><?= Html::encode($name) ?></th>
                    <td><?= htmlspecialchars(VarDumper::dumpAsString($value), ENT_QUOTES|ENT_SUBSTITUTE, \Aabc::$app->charset, true) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>
