<?php
namespace Codeception\Lib\Generator;

use Codeception\Util\Shared\Namespaces;
use Codeception\Util\Template;

class Group
{
    use Namespaces;
    use Shared\Classname;

    protected $template = <<<EOF
<?php
namespace {{namespace}};

use \Codeception\Event\TestEvent;


class {{class}} extends \Codeception\Platform\Group
{
    public static \$group = '{{groupName}}';

    public function _before(TestEvent \$e)
    {
    }

    public function _after(TestEvent \$e)
    {
    }
}

EOF;

    protected $name;
    protected $namespace;
    protected $settings;

    public function __construct($settings, $name)
    {
        $this->settings = $settings;
        $this->name = $name;
        $this->namespace = $this->getNamespaceString($this->settings['namespace'] . '\\Group\\' . $name);
    }

    public function produce()
    {
        $ns = $this->getNamespaceString($this->settings['namespace'] . '\\' . $this->name);
        return (new Template($this->template))
            ->place('class', ucfirst($this->name))
            ->place('name', $this->name)
            ->place('namespace', $this->namespace)
            ->place('groupName', strtolower($this->name))
            ->produce();
    }
}
