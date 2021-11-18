<?php

declare(strict_types=1);

namespace OCA\ReserveRoom\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version000001Date20211020151527 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();
		
		
		if (!$schema->hasTable('reservation')) {
			$table = $schema->createTable('reservation');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('start_date_time', 'datetime', [
				'notnull' => true,
			]);
			$table->addColumn('end_date_time', 'datetime', [
				'notnull' => true,
			]);

			$table->addColumn('uid', 'string', [
				'notnull' => true,
				'length' => '64'
			]);

			$table->addColumn('facil_id', 'integer', [
                                'notnull' => true,
			]);

			$table->addColumn('memo', 'text', [
				'notnull' => true
			]);

			$table->setPrimaryKey(['id']);
			$table->addForeignKeyConstraint('oc_users', ['uid'], ['uid']);
			$table->addForeignKeyConstraint('oc_facility', ['facil_id'], ['id']);
		}
		return $schema;
	}
	

}
