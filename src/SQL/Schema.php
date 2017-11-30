<?php

namespace pulledbits\ActiveRecord\SQL;

use pulledbits\ActiveRecord\RecordType;
use pulledbits\ActiveRecord\SQL\Meta\TableDescription;

final class Schema implements \pulledbits\ActiveRecord\Schema
{

    private $connection;
    private $queryFactory;

    public function __construct(Connection $connection, QueryFactory $queryFactory) {
        $this->connection = $connection;
        $this->queryFactory = $queryFactory;
    }

    public function makeRecordType(string $entityTypeIdentifier, TableDescription $entityDescription): RecordType
    {
        return new EntityType($this, $entityTypeIdentifier, $entityDescription);
    }

    public function read(string $entityTypeIdentifier, array $attributeIdentifiers, array $conditions) : array {
        $query = $this->queryFactory->makeSelect($entityTypeIdentifier, $attributeIdentifiers);
        $query->where($this->queryFactory->makeWhere($conditions));
        $result = $query->execute($this->connection);

        $sourceSchema = new \pulledbits\ActiveRecord\SQL\Meta\Schema($this->connection, $this);
        $recordType = $sourceSchema->describeTable($entityTypeIdentifier);

        $records = [];
        foreach ($result->fetchAll() as $row) {
            $record = $recordType->makeRecord();
            $record->contains($row);
            $records[] = $record;
        }
        return $records;
    }

    public function update(string $tableIdentifier, array $values, array $conditions) : int {
        $query = $this->queryFactory->makeUpdate($tableIdentifier, $values);
        $query->where($this->queryFactory->makeWhere($conditions));
        return count($query->execute($this->connection));
    }

    public function create(string $tableIdentifier, array $values) : int {
        $query = $this->queryFactory->makeInsert($tableIdentifier, $values);
        return count($query->execute($this->connection));
    }

    public function delete(string $tableIdentifier, array $conditions) : int {
        $query = $this->queryFactory->makeDelete($tableIdentifier);
        $query->where($this->queryFactory->makeWhere($conditions));
        return count($query->execute($this->connection));
    }

    public function executeProcedure(string $procedureIdentifier, array $arguments): void
    {
        $query = $this->queryFactory->makeProcedure($procedureIdentifier, $arguments);
        $query->execute($this->connection);
    }
}