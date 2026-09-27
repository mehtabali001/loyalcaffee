<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * All CMS content. Every human-readable field is a JSON column shaped {"en": "...", "ar": "..."}.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Key/value site settings (contact, hours, socials, SEO, rating ...)
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 40)->default('general')->index();
            $table->string('key', 80)->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        // Editable UI strings (button labels, nav, form labels) overriding the bundled defaults
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('key', 150)->unique();
            $table->json('value');
            $table->timestamps();
        });

        // Home page building blocks — reorderable, toggleable
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('page', 40)->default('home')->index();
            $table->string('type', 40);            // hero | story | day_night | featured_menu | matches | gallery | testimonials | visit | offers
            $table->json('title')->nullable();
            $table->json('subtitle')->nullable();
            $table->json('body')->nullable();
            $table->json('cta_label')->nullable();
            $table->string('cta_link')->nullable();
            $table->string('image')->nullable();
            $table->string('image_secondary')->nullable();
            $table->string('video')->nullable();
            $table->json('data')->nullable();       // type-specific extras
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('menu_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('icon', 40)->nullable();  // coffee | shisha | plate | juice | dessert | leaf
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_category_id')->constrained()->cascadeOnDelete();
            $table->json('name');
            $table->json('description')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->decimal('price_old', 8, 2)->nullable();
            $table->string('price_note', 40)->nullable(); // e.g. "per head", "from"
            $table->string('image')->nullable();
            $table->json('tags')->nullable();             // ["signature","new","popular","vegan","spicy"]
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_available')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('gallery_items', function (Blueprint $table) {
            $table->id();
            $table->string('category', 30)->default('interior')->index(); // interior | shisha | drinks | food | match-nights
            $table->json('caption')->nullable();
            $table->string('image');
            $table->string('alt_en')->nullable();
            $table->string('alt_ar')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Match nights (FIFA + any live sport) and other events
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 20)->default('match');  // match | event
            $table->json('title');
            $table->json('description')->nullable();
            $table->string('competition')->nullable();       // "FIFA World Cup 2026"
            $table->json('stage')->nullable();              // "Round of 16"
            $table->json('home_team')->nullable();
            $table->json('away_team')->nullable();
            $table->string('home_flag', 8)->nullable();      // ISO code e.g. "ae"
            $table->string('away_flag', 8)->nullable();
            $table->dateTime('starts_at')->index();          // stored in APP_TIMEZONE (Asia/Dubai)
            $table->string('image')->nullable();
            $table->json('offer')->nullable();              // "World Cup Special: shisha + 2 drinks"
            $table->boolean('is_big_match')->default(false);
            $table->boolean('reservations_open')->default(true);
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('description')->nullable();
            $table->json('badge')->nullable();              // "5–6 PM daily"
            $table->string('image')->nullable();
            $table->string('cta_link')->nullable();
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('author');
            $table->json('content');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->string('source', 30)->default('google');
            $table->date('reviewed_on')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 12)->unique();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone', 30);
            $table->string('email')->nullable();
            $table->unsignedSmallInteger('guests');
            $table->date('date')->index();
            $table->string('time', 5);
            $table->string('seating', 20)->nullable();       // indoor | outdoor | screens
            $table->text('notes')->nullable();
            $table->string('locale', 5)->default('en');
            $table->string('status', 20)->default('pending')->index(); // pending | confirmed | seated | cancelled | no_show
            $table->text('admin_notes')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('locale', 5)->default('en');
            $table->timestamp('read_at')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['contact_messages', 'reservations', 'testimonials', 'offers', 'events', 'gallery_items',
                  'menu_items', 'menu_categories', 'sections', 'translations', 'settings'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
