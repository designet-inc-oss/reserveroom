<?php

declare(strict_types=1);

namespace OCA\ReserveRoom\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version000001Date20211019155941 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();
		
		if (!$schema->hasTable('facility')) {
                        $table = $schema->createTable('facility');
                        $table->addColumn('id', 'integer', [
                              'autoincrement' => true,
                              'notnull' => true,
                        ]);

                        $table->addColumn('sort_num', 'integer', [
                              'notnull' => true
                        ]);

                        $table->addColumn('facil_name', 'string', [
                              'notnull' => true,
                              'length' => '256'
                        ]);

                        $table->setPrimaryKey(['id']);
		}
		return $schema;
	}
	

}
