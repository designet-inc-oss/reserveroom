<?php

declare(strict_types=1);

namespace OCA\ReserveRoom\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version000001Date20220117151630 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();
		
		$table = $schema->getTable('reservation');
		foreach ($table->getForeignKeys() as $foreignKey) {
                        $column = $foreignKey->getColumns();
			if ($column[0] == 'uid') {
                            $table->removeForeignKey($foreignKey->getName());
			}
               	}
		return $schema;
	}
}
