<?php echo '<?php'?>

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class {{$className}} extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('{{$table }}', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('parent');
            $table->unsignedTinyInteger('level');
            $table->string('name', 80);
            $table->string('node', 1024);

            $table->timestamps();
            @if($softDelete)
            $table->softDeletes();
            @endif

            $table->index('parent_id');
            $table->index('name');
            $table->index('node');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('{{ $table }}');
    }

}
