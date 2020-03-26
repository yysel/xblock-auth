<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOauthTokenTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('oauth_token', function(Blueprint $table)
		{
			$table->string('id', 100)->primary();
			$table->string('user_id', 100)->index();
			$table->text('scopes')->nullable();
			$table->dateTime('expires_at')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('oauth_token');
	}

}
