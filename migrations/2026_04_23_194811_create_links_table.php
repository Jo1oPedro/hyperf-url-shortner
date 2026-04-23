<?php

use App\Domain\Link\LinkStatus;
use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug', 16)->unique();
            $table->text('original_url');
            $table->string('status', 16)->default(LinkStatus::ACTIVE->value)->index();
            $table->timestamp('expires_at')->nullable();
            $table->uuid('owner_user_id')->nullable()->index();
            $table->datetimes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};
