<?php echo '<?php'?>

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoriesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('{{$table }}', function(Blueprint $table) {
        $table->increments('id');
        $table->integer('parent')->unsigned();
        $table->tinyInteger('level')->unsigned();
        $table->string('name', 60);
        $table->string('node', 1024)->default('');

        $table->timestamps();
        $table->index('parent');
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
