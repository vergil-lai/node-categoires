# node-categories

Node categories model and observer for laravel 5

# Install

     composer require vergil-lai/node-categories

# Configure

在你的项目目录`config/app.php`的`providers`数组里加入:

    VergilLai\NodeCategories\NodeCategoriesProvider::class
    
# 使用说明

## 运行artisan

创建migration并运行migrate:

    $ php artisan node-categories:migration
    
默认的数据表名是`categories`，如果需要指定数据表名，需要加上参数`--table`，例如：

    $ php artisan node-categories:migration --table=mytable
    
## 创建模型
    
使用artisan创建模型，例如：

    $ php artisan make:model Cateory
    
然后，让你的模型use trait `NodeCategoryTrait`
    
    <?php
    
    namespace App;
    
    use Illuminate\Database\Eloquent\Model;
    
    class Category extends Model
    {
        use \VergilLai\NodeCategories\NodeCategoryTrait;
    }


## 添加模型观察者

在`/app/Providers/EventServiceProvider.php`的`boot`方法里添加：

    Category::observe(\VergilLai\NodeCategories\Observer::class);
    
    
# Example


## Create

    $parent1 = new Category();
    $parent1->name = 'Parent 1';
    $parent1->save();

    $parent2 = new Category();
    $parent2->name = 'Parent 2';
    $parent2->save();

    $parent3 = new Category();
    $parent3->name = 'Parent 3';
    $parent3->save();

    $parent4 = new Category();
    $parent4->name = 'Parent 4';
    $parent4->save();

    $parent5 = new Category();
    $parent5->name = 'Parent 5';
    $parent5->save();

    $child1 = new Category();
    $child1->parent_id = $parent1->id;     //把parent字段设置为上级分类的id
    $child1->name = 'Child 1';
    $child1->save();

    $child2 = new Category();
    $child2->parent_id = $parent1->id;
    $child2->name = 'Child 2';
    $child2->save();

    $child3 = new Category();
    $child3->parent_id = $parent1->id;
    $child3->name = 'Child 3';
    $child3->save();

    $grandchild1 = new Category();
    $grandchild1->parent_id = $child1->id;
    $grandchild1->name = 'Grandchild 1';
    $grandchild1->save();

    $grandchild2 = new Category();
    $grandchild2->parent_id = $child1->id;
    $grandchild2->name = 'Grandchild 2';
    $grandchild2->save();
           
           
结果：
            
    +----+--------+-------+--------------+----------+
    | id | parent | level | name         | node     |
    +----+--------+-------+--------------+----------+
    |  1 |      0 |     1 | Parent 1     | ,1,      |
    |  2 |      0 |     1 | Parent 2     | ,2,      |
    |  3 |      0 |     1 | Parent 3     | ,3,      |
    |  4 |      0 |     1 | Parent 4     | ,4,      |
    |  5 |      0 |     1 | Parent 5     | ,5,      |
    |  6 |      1 |     2 | Child 1      | ,1,6,    |
    |  7 |      1 |     2 | Child 2      | ,1,7,    |
    |  8 |      1 |     2 | Child 3      | ,1,8,    |
    |  9 |      6 |     3 | Grandchild 1 | ,1,6,9,  |
    | 10 |      6 |     3 | Grandchild 2 | ,1,6,10, |
    +----+--------+-------+--------------+----------+
        

## Update parent

    $child1 = Category::find(6);
    $child1->parent = 4;        //修改为id为4的子类
    $child1->save();

结果：

    +----+--------+-------+--------------+----------+
    | id | parent | level | name         | node     |
    +----+--------+-------+--------------+----------+
    |  1 |      0 |     1 | Parent 1     | ,1,      |
    |  2 |      0 |     1 | Parent 2     | ,2,      |
    |  3 |      0 |     1 | Parent 3     | ,3,      |
    |  4 |      0 |     1 | Parent 4     | ,4,      |
    |  5 |      0 |     1 | Parent 5     | ,5,      |
    |  6 |      4 |     2 | Child 1      | ,4,6,    |
    |  7 |      1 |     2 | Child 2      | ,1,7,    |
    |  8 |      1 |     2 | Child 3      | ,1,8,    |
    |  9 |      6 |     4 | Grandchild 1 | ,4,6,9,  |
    | 10 |      6 |     4 | Grandchild 2 | ,4,6,10, |
    +----+--------+-------+--------------+----------+
    
    
## Delete

    $parent4 = Category::find(4);
    $parent4->delete();
    
结果：
    
    +----+--------+-------+----------+-------+
    | id | parent | level | name     | node  |
    +----+--------+-------+----------+-------+
    |  1 |      0 |     1 | Parent 1 | ,1,   |
    |  2 |      0 |     1 | Parent 2 | ,2,   |
    |  3 |      0 |     1 | Parent 3 | ,3,   |
    |  5 |      0 |     1 | Parent 5 | ,5,   |
    |  7 |      1 |     2 | Child 2  | ,1,7, |
    |  8 |      1 |     2 | Child 3  | ,1,8, |
    +----+--------+-------+----------+-------+
    


# Method

public \Illuminate\Database\Eloquent\Collection NodeCategory::childrens(void)

获取所有子分类

    $parent1 = Category::find(1);
    dd($parent1->childrens());
    
public \Illuminate\Database\Eloquent\Collection NodeCategory::getParent(void)

    $child1 = Category::find(6);
    dd($child1->getParent());
    
You can use `BelongTo` Relation
    
    $child1 = Category::find(6);
    dd($child1->parent);
    
