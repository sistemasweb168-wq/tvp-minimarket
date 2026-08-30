<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table("users")->where("username", "admin")->update(["email" => "admin@mikitos.com"]);
        DB::table("users")->where("username", "gerente")->update(["email" => "gerente@mikitos.com"]);
        DB::table("users")->where("username", "cajero")->update(["email" => "cajero@mikitos.com"]);
        DB::statement("UPDATE users SET email = REPLACE(email, '@tpvminimarket.com', '@mikitos.com') WHERE email LIKE '%@tpvminimarket.com'");
        DB::statement("UPDATE users SET email = REPLACE(email, '@bodegavalezka.com', '@mikitos.com') WHERE email LIKE '%@bodegavalezka.com'");
    }

    public function down(): void
    {
    }
};
