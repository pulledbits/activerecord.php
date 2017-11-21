<?php
namespace pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;

use Psr\Http\Message\StreamInterface;
use pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;

final class Entity implements RecordConfiguratorGenerator
{
    const NEWLINE = PHP_EOL . "    ";

    private $entityIdentifier;

    private $requiredAttributeIdentifiers;

    private $references;

    public function __construct(array $entityIdentifier)
    {
        $this->entityIdentifier = $entityIdentifier;
        $this->requiredAttributeIdentifiers = [];
        $this->references = [];
    }

    public function requires(array $requiredAttributeIdentifiers) {
        $this->requiredAttributeIdentifiers = $requiredAttributeIdentifiers;
    }

    public function references(string $referenceIdentifier, string $referencedEntityIdentifier, array $conditions)
    {
        $this->references[$referenceIdentifier] = [
            'table' => $referencedEntityIdentifier,
            'where' => $conditions
        ];
    }

    public function generateConfigurator(StreamInterface $stream) : void
    {
        $stream->write('<?php namespace pulledbits\\ActiveRecord; return new class implements RecordConfigurator { public function configure(RecordFactory $recordFactory) : Record {');
        $stream->write(self::NEWLINE . '$record = $recordFactory->createRecord();');
        $stream->write(self::NEWLINE . "\$record->identifiedBy(['" . join("', '", $this->entityIdentifier) . "']);");

        if (count($this->requiredAttributeIdentifiers) > 0) {
            $stream->write(self::NEWLINE . "\$record->requires(['" . join("', '", $this->requiredAttributeIdentifiers) . "']);");
        }

        if (count($this->references) > 0) {
            foreach ($this->references as $referenceIdentifier => $reference) {
                $where = [];
                foreach ($reference['where'] as $referencedAttributeIdentifier => $localAttributeIdentifier) {
                    $where[] = '\'' . $referencedAttributeIdentifier . '\' => \'' . $localAttributeIdentifier . '\'';
                }
                $stream->write(self::NEWLINE . "\$record->references('" . $referenceIdentifier . "', '" . $reference['table'] . "', [" . join(", ", $where) . "]);");
            }
        }
        $stream->write(self::NEWLINE . 'return $record;' . "\n" . '}};');
    }
}