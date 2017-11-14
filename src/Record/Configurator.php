<?php


namespace pulledbits\ActiveRecord\Record;


class Configurator
{
    private $sourceSchema;
    private $path;

    public function __construct(\pulledbits\ActiveRecord\Source\Schema $sourceSchema, string $path)
    {
        $this->sourceSchema = $sourceSchema;
        $this->path = $path;
        if (is_dir($this->path) === false) {
            mkdir($this->path);
        }
    }

    public function generate($entityTypeIdentifier)
    {
        $configuratorPath = $this->path . DIRECTORY_SEPARATOR . $entityTypeIdentifier . '.php';
        if (is_file($configuratorPath) === false) {
            $generatorGeneratorFactory = new \pulledbits\ActiveRecord\Source\GeneratorGeneratorFactory();
            $recordClassDescription = $this->sourceSchema->describeTable(new \pulledbits\ActiveRecord\SQL\Meta\Table(), $entityTypeIdentifier);
            $generator = $generatorGeneratorFactory->makeGeneratorGenerator($recordClassDescription);
            file_put_contents($configuratorPath, $generator->generate());
        }
        return require $configuratorPath;
    }
}