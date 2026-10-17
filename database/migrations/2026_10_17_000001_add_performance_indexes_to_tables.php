<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->index(['is_published', 'category', 'published_at'], 'posts_pub_cat_pubat_idx');
            $table->index('event_date', 'posts_event_date_idx');
        });

        Schema::table('staff_members', function (Blueprint $table) {
            $table->index(['is_active', 'display_order'], 'staff_members_active_order_idx');
        });

        Schema::table('contact_messages', function (Blueprint $table) {
            $table->index('is_read', 'contact_messages_is_read_idx');
        });

        Schema::table('book_reservations', function (Blueprint $table) {
            $table->index('status', 'book_reservations_status_idx');
        });

        Schema::table('school_documents', function (Blueprint $table) {
            $table->index(['is_active', 'category'], 'school_documents_active_cat_idx');
        });

        if (Schema::hasTable('questions') && !Schema::hasColumn('questions', 'order_index')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->unsignedSmallInteger('order_index')->default(1)->nullable()->after('points');
            });
        }

        if (Schema::hasTable('question_options') && !Schema::hasColumn('question_options', 'option_label')) {
            Schema::table('question_options', function (Blueprint $table) {
                $table->string('option_label', 10)->nullable()->after('question_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('school_documents', function (Blueprint $table) {
            $table->dropIndex('school_documents_active_cat_idx');
        });

        Schema::table('book_reservations', function (Blueprint $table) {
            $table->dropIndex('book_reservations_status_idx');
        });

        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropIndex('contact_messages_is_read_idx');
        });

        Schema::table('staff_members', function (Blueprint $table) {
            $table->dropIndex('staff_members_active_order_idx');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex('posts_event_date_idx');
            $table->dropIndex('posts_pub_cat_pubat_idx');
        });

        if (Schema::hasTable('question_options') && Schema::hasColumn('question_options', 'option_label')) {
            Schema::table('question_options', function (Blueprint $table) {
                $table->dropColumn('option_label');
            });
        }

        if (Schema::hasTable('questions') && Schema::hasColumn('questions', 'order_index')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->dropColumn('order_index');
            });
        }
    }
};
