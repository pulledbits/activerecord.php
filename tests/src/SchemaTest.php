<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 20-12-16
 * Time: 16:06
 */

namespace ActiveRecord;


class SchemaTest extends \PHPUnit_Framework_TestCase
{
    public function testTransformColumnToProperty_When_ColumnIdentifierSupplied_Expect_ColumnIdPrefixedWithUnderscore()
    {

        $schema = new Schema('\Test\Record', new class extends \PDO
        {
            public function __construct()
            {
            }
        });

        $this->assertEquals("_id", $schema->transformColumnToProperty('id'));
    }

    public function testTransformTableIdentifierToRecordClassIdentifier_When_TableIdentifierSupplied_Expect_TableIdPrefixedWithTargetNamespace()
    {

        $schema = new Schema('\Test\Record', new class extends \PDO
        {
            public function __construct()
            {
            }
        });

        $this->assertEquals('\Test\Record\activiteit', $schema->transformTableIdentifierToRecordClassIdentifier('activiteit'));
    }

    public function testPrepareParameters_When_ColumnIdentifiersSupplied_Expect_ColumnIdPrefixedWithUnderscore()
    {

        $schema = new Schema('\Test\Record', new class extends \PDO
        {
            public function __construct()
            {
            }
        });

        $namedParameter = ':' . sha1('where_id');

        $this->assertEquals([["id" => "id = " . $namedParameter],[$namedParameter => '1']], $schema->prepareParameters('where', ['id' => '1']));
    }

    public function testPrepareFields_When_ColumnIdentifiersSupplied_Expect_ColumnsAliassedAsColumnIdsPrefixedWithUnderscore()
    {

        $schema = new Schema('\Test\Record', new class extends \PDO
        {
            public function __construct()
            {
            }
        });

        $this->assertEquals(["id AS _id"], $schema->prepareFields(['id']));
    }

    public function testExecute_When_WhenProperQueryWithNamedParametersSupplied_Expect_PDOStatementWithFiveRecords()
    {
        $schema = new Schema('\Test\Record', new class extends \PDO
        {
            public function __construct()
            {
            }

            public function prepare($query, $options = null)
            {
                if ($query === 'SELECT id AS _id, name AS _name FROM activiteit WHERE id = :param1') {
                    return new class extends \PDOStatement
                    {
                        public function __construct()
                        {
                        }

                        public function fetchAll($how = NULL, $class_name = NULL, $ctor_args = NULL)
                        {
                            if ($how === \PDO::FETCH_CLASS && $class_name === '\Test\Record\activiteit') {
                                return [
                                    new class
                                    {
                                    },
                                    new class
                                    {
                                    },
                                    new class
                                    {
                                    },
                                    new class
                                    {
                                    },
                                    new class
                                    {
                                    },
                                ];
                            }
                        }
                    };
                }
            }
        });

        $statement = $schema->execute('SELECT id AS _id, name AS _name FROM activiteit WHERE id = :param1', [':param1' => '1']);

        $this->assertCount(5, $statement->fetchAll(\PDO::FETCH_CLASS, '\Test\Record\activiteit'));
    }
}