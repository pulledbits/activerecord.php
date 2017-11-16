<?php


namespace pulledbits\ActiveRecord\SQL;

use pulledbits\ActiveRecord\SQL\Meta\Configurator;

class Connection
{
    private $connection;
    private $configurator;

    public function __construct(\PDO $connection, Configurator $configurator)
    {
        $this->connection = $connection;
        $this->configurator = $configurator;
    }

    static function fromDatabaseURL(string $url, Configurator $configurator) : self
    {
        $parsedUrl = parse_url($url);
        return new self(new \PDO($parsedUrl['scheme'] . ':dbname=' . substr($parsedUrl['path'], 1), $parsedUrl['user'], $parsedUrl['pass'], array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'')), $configurator);
    }

    public function schema()
    {
        return new Schema(new QueryFactory($this));
    }
    public function recordConfigurator(string $entityTypeIdentifier) : callable
    {
        return $this->configurator->generate($entityTypeIdentifier);
    }

    public function execute(string $query, array $namedParameters) : Statement
    {
        $pdostatement = $this->connection->prepare($query);
        $statement = new Statement($pdostatement);
        $statement->addParameters($namedParameters);

        if ($pdostatement->execute() === false) {
            trigger_error("Failed executing query `" . $query . "` (" . json_encode($pdostatement->debugDumpParams()) . "): " . $pdostatement->errorInfo()[2], E_USER_ERROR);
        }

        return $statement;
    }


}