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

    node-categories:migration
    
默认的数据表名是`categories`，如果需要指定数据表名，需要加上参数`--table`，例如：

    node-categories:migration --table=mytable
    
## 创建模型
    
使用artisan创建模型，例如：

    php artisan make:model Cateory
    
然后，让你的模型继承`NodeCategory`
    
    <?php
    
    namespace App;
    
    use VergilLai\NodeCategories\NodeCategory;
    
    class Cateory extends NodeCategory
    {
    }

## 添加事件

在`/app/Providers/EventServiceProvider.php`的`boot`方法里添加：

    Category::observe(\VergilLai\NodeCategories\Observer::class);
    
