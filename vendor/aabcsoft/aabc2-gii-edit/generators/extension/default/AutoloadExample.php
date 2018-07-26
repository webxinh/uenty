<?= "<?php\n" ?>

namespace <?= substr($generator->namespace, 0, -1) ?>;


class AutoloadExample extends \aabc\base\Widget
{
    public function run()
    {
        return "Hello!";
    }
}
