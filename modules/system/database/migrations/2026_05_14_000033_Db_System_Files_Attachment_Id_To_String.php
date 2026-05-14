<?php

use Winter\Storm\Database\Updates\Migration;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('system_files') || !Schema::hasColumn('system_files', 'attachment_id')) {
            return;
        }

        $driver = DB::connection()->getDriverName();
        $type = $this->getAttachmentIdDataType($driver);

        if ($this->isStringType($type)) {
            return;
        }

        if ($driver === 'pgsql') {
            DB::statement(
                'ALTER TABLE system_files ALTER COLUMN attachment_id TYPE VARCHAR(255) USING attachment_id::varchar(255)'
            );

            return;
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE system_files MODIFY attachment_id VARCHAR(255) NULL');
        }
    }

    public function down()
    {
        // Keep attachment_id as a string to match WinterCMS polymorphic attachments.
    }

    protected function getAttachmentIdDataType(string $driver): ?string
    {
        if ($driver === 'pgsql') {
            $result = DB::selectOne(
                "SELECT data_type
                FROM information_schema.columns
                WHERE table_schema = current_schema()
                  AND table_name = 'system_files'
                  AND column_name = 'attachment_id'
                LIMIT 1"
            );

            return isset($result->data_type) ? strtolower((string) $result->data_type) : null;
        }

        if ($driver === 'mysql') {
            $result = DB::selectOne(
                "SELECT data_type
                FROM information_schema.columns
                WHERE table_schema = DATABASE()
                  AND table_name = 'system_files'
                  AND column_name = 'attachment_id'
                LIMIT 1"
            );

            return isset($result->data_type) ? strtolower((string) $result->data_type) : null;
        }

        return null;
    }

    protected function isStringType(?string $type): bool
    {
        return in_array($type, ['character varying', 'varchar', 'text', 'char', 'character'], true);
    }
};
