<?php


namespace aabc\db;

use aabc\base\Component;
use aabc\di\Instance;


class Migration extends Component implements MigrationInterface
{
    use SchemaBuilderTrait;

    
    public $db = 'db';


    
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::className());
        $this->db->getSchema()->refresh();
        $this->db->enableSlaves = false;
    }

    
    protected function getDb()
    {
        return $this->db;
    }

    
    public function up()
    {
        $transaction = $this->db->beginTransaction();
        try {
            if ($this->safeUp() === false) {
                $transaction->rollBack();
                return false;
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $this->printException($e);
            $transaction->rollBack();
            return false;
        } catch (\Throwable $e) {
            $this->printException($e);
            $transaction->rollBack();
            return false;
        }

        return null;
    }

    
    public function down()
    {
        $transaction = $this->db->beginTransaction();
        try {
            if ($this->safeDown() === false) {
                $transaction->rollBack();
                return false;
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $this->printException($e);
            $transaction->rollBack();
            return false;
        } catch (\Throwable $e) {
            $this->printException($e);
            $transaction->rollBack();
            return false;
        }

        return null;
    }

    
    private function printException($e)
    {
        echo 'Exception: ' . $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ")\n";
        echo $e->getTraceAsString() . "\n";
    }

    
    public function safeUp()
    {
    }

    
    public function safeDown()
    {
    }

    
    public function execute($sql, $params = [])
    {
        echo "    > execute SQL: $sql ...";
        $time = microtime(true);
        $this->db->createCommand($sql)->bindValues($params)->execute();
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    
    public function insert($table, $columns)
    {
        echo "    > insert into $table ...";
        $time = microtime(true);
        $this->db->createCommand()->insert($table, $columns)->execute();
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    
    public function batchInsert($table, $columns, $rows)
    {
        echo "    > insert into $table ...";
        $time = microtime(true);
        $this->db->createCommand()->batchInsert($table, $columns, $rows)->execute();
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    
    public function update($table, $columns, $condition = '', $params = [])
    {
        echo "    > update $table ...";
        $time = microtime(true);
        $this->db->createCommand()->update($table, $columns, $condition, $params)->execute();
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    
    public function delete($table, $condition = '', $params = [])
    {
        echo "    > delete from $table ...";
        $time = microtime(true);
        $this->db->createCommand()->delete($table, $condition, $params)->execute();
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    
    public function createTable($table, $columns, $options = null)
    {
        echo "    > create table $table ...";
        $time = microtime(true);
        $this->db->createCommand()->createTable($table, $columns, $options)->execute();
        foreach ($columns as $column => $type) {
            if ($type instanceof ColumnSchemaBuilder && $type->comment !== null) {
                $this->db->createCommand()->addCommentOnColumn($table, $column, $type->comment)->execute();
            }
        }
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    
    public function renameTable($table, $newName)
    {
        echo "    > rename table $table to $newName ...";
        $time = microtime(true);
        $this->db->createCommand()->renameTable($table, $newName)->execute();
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    
    public function dropTable($table)
    {
        echo "    > drop table $table ...";
        $time = microtime(true);
        $this->db->createCommand()->dropTable($table)->execute();
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    
    public function truncateTable($table)
    {
        echo "    > truncate table $table ...";
        $time = microtime(true);
        $this->db->createCommand()->truncateTable($table)->execute();
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    
    public function addColumn($table, $column, $type)
    {
        echo "    > add column $column $type to table $table ...";
        $time = microtime(true);
        $this->db->createCommand()->addColumn($table, $column, $type)->execute();
        if ($type instanceof ColumnSchemaBuilder && $type->comment !== null) {
            $this->db->createCommand()->addCommentOnColumn($table, $column, $type->comment)->execute();
        }
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    
    public function dropColumn($table, $column)
    {
        echo "    > drop column $column from table $table ...";
        $time = microtime(true);
        $this->db->createCommand()->dropColumn($table, $column)->execute();
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    
    public function renameColumn($table, $name, $newName)
    {
        echo "    > rename column $name in table $table to $newName ...";
        $time = microtime(true);
        $this->db->createCommand()->renameColumn($table, $name, $newName)->execute();
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    
    public function alterColumn($table, $column, $type)
    {
        echo "    > alter column $column in table $table to $type ...";
        $time = microtime(true);
        $this->db->createCommand()->alterColumn($table, $column, $type)->execute();
        if ($type instanceof ColumnSchemaBuilder && $type->comment !== null) {
            $this->db->createCommand()->addCommentOnColumn($table, $column, $type->comment)->execute();
        }
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    
    public function addPrimaryKey($name, $table, $columns)
    {
        echo "    > add primary key $name on $table (" . (is_array($columns) ? implode(',', $columns) : $columns) . ') ...';
        $time = microtime(true);
        $this->db->createCommand()->addPrimaryKey($name, $table, $columns)->execute();
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    
    public function dropPrimaryKey($name, $table)
    {
        echo "    > drop primary key $name ...";
        $time = microtime(true);
        $this->db->createCommand()->dropPrimaryKey($name, $table)->execute();
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    
    public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
    {
        echo "    > add foreign key $name: $table (" . implode(',', (array) $columns) . ") references $refTable (" . implode(',', (array) $refColumns) . ') ...';
        $time = microtime(true);
        $this->db->createCommand()->addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete, $update)->execute();
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    
    public function dropForeignKey($name, $table)
    {
        echo "    > drop foreign key $name from table $table ...";
        $time = microtime(true);
        $this->db->createCommand()->dropForeignKey($name, $table)->execute();
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    
    public function createIndex($name, $table, $columns, $unique = false)
    {
        echo '    > create' . ($unique ? ' unique' : '') . " index $name on $table (" . implode(',', (array) $columns) . ') ...';
        $time = microtime(true);
        $this->db->createCommand()->createIndex($name, $table, $columns, $unique)->execute();
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    
    public function dropIndex($name, $table)
    {
        echo "    > drop index $name on $table ...";
        $time = microtime(true);
        $this->db->createCommand()->dropIndex($name, $table)->execute();
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    
    public function addCommentOnColumn($table, $column, $comment)
    {
        echo "    > add comment on column $column ...";
        $time = microtime(true);
        $this->db->createCommand()->addCommentOnColumn($table, $column, $comment)->execute();
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    
    public function addCommentOnTable($table, $comment)
    {
        echo "    > add comment on table $table ...";
        $time = microtime(true);
        $this->db->createCommand()->addCommentOnTable($table, $comment)->execute();
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    
    public function dropCommentFromColumn($table, $column)
    {
        echo "    > drop comment from column $column ...";
        $time = microtime(true);
        $this->db->createCommand()->dropCommentFromColumn($table, $column)->execute();
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    
    public function dropCommentFromTable($table)
    {
        echo "    > drop comment from table $table ...";
        $time = microtime(true);
        $this->db->createCommand()->dropCommentFromTable($table)->execute();
        echo ' done (time: ' . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }
}
