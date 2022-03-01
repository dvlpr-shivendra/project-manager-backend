<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title')->fulltext();
            $table->text('description')->nullable()->fulltext();
            $table->foreignId('status_id')->constrained('task_statuses')->onDelete('CASCADE');
            $table->foreignId('creator_id')->constrained('users')->onDelete('CASCADE');
            $table->foreignId('assignee_id')->constrained('users')->onDelete('CASCADE');
            $table->foreignId('project_id')->constrained()->onDelete('CASCADE');
            $table->timestamp('deadline')->nullable();
            $table->boolean('is_complete')->index()->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
};
