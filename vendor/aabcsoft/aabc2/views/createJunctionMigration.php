<?php

/* @var $className string the new migration class name without namespace */
/* @var $namespace string the new migration class namespace */
/* @var $table string the name table */
/* @var $field_first string the name field first */
/* @var $field_second string the name field second */

echo "<?php\n";
if (!empty($namespace)) {
    echo "\nnamespace {$namespace};\n";
}
?>

use aabc\db\Migration;


class <?= $className ?> extends Migration
{
    
    public function up()
    {
        $this->createTable('<?= $table ?>', [
            '<?= $field_first ?>_id' => $this->integer(),
            '<?= $field_second ?>_id' => $this->integer(),
            'PRIMARY KEY(<?= $field_first ?>_id, <?= $field_second ?>_id)',
        ]);

        $this->createIndex(
            'idx-<?= $table . '-' . $field_first ?>_id',
            '<?= $table ?>',
            '<?= $field_first ?>_id'
        );

        $this->createIndex(
            'idx-<?= $table . '-' . $field_second ?>_id',
            '<?= $table ?>',
            '<?= $field_second ?>_id'
        );

        $this->addForeignKey(
            'fk-<?= $table . '-' . $field_first ?>_id',
            '<?= $table ?>',
            '<?= $field_first ?>_id',
            '<?= $field_first ?>',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-<?= $table . '-' . $field_second ?>_id',
            '<?= $table ?>',
            '<?= $field_second ?>_id',
            '<?= $field_second ?>',
            'id',
            'CASCADE'
        );
    }

    
    public function down()
    {
        $this->dropTable('<?= $table ?>');
    }
}
