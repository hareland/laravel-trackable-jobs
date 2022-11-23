<?php


use Hareland\Trackable\Enums\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trackable_job_envelopes', function (Blueprint $table) {
            $table->id();
            $table->morphs('trackable');
            $table->string('handle');
            $table->string('status')->default(Status::PENDING->value);
           $table->foreignId('job_id');
            $table->json('data')->nullable();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('trackable_job_envelopes');
    }
};
