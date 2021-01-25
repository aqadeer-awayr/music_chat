<?php

use App\MessageLikeOrUnLike;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessageLikeOrUnLikesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_like_or_un_likes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('message_id')->unsigned();
            $table->foreign('message_id')->references('id')->on('chat_messages')->onDelete('cascade');
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->enum('type', [MessageLikeOrUnLike::TYPE_LIKE, MessageLikeOrUnLike::TYPE_UNLIKE])->default(MessageLikeOrUnLike::TYPE_LIKE);
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
        Schema::dropIfExists('message_like_or_un_likes');
    }
}
