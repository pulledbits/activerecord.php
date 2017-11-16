<?php


namespace pulledbits\ActiveRecord\SQL\Query;


use pulledbits\ActiveRecord\RecordFactory;

class Result implements \Countable
{

    private $statement;
    private $configurator;

    public function __construct(\pulledbits\ActiveRecord\SQL\Statement $statement, callable $configurator = null)
    {
        $this->statement = $statement;
        $this->configurator = $configurator;
    }

    public function count()
    {
        return $this->statement->rowCount();
    }

    public function fetchAllAs(RecordFactory $recordFactory) : array
    {
        $records = [];
        foreach ($this->statement->fetchAll() as $row) {
            $record = $recordFactory->createRecord($this->configurator);
            $record->contains($row);
            $records[] = $record;
        }
        return $records;
    }
}