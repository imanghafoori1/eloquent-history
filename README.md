# Eloquent History Tracker


It keeps track of your table rows and just like git, it only records changes for each update you make.


[![Latest Stable Version](https://poser.pugx.org/imanghafoori/eloquent-history/v/stable)](https://packagist.org/packages/imanghafoori/eloquent-history)


## Installation:

```

composer require imanghafoori/eloquent-history

php artisan vendor:publish

php artisan migrate

```



## Usage:

```php

public function boot()

{

    // here we want to monitor all the table columns except 'remember_token'

    HistoryTracker::track('App/User', $except = ['remember_token']);

}



```



** Note ** Since this works based on eloquent model events, if you update your rows without firing events the changes would not be recorded.

This includes performing an update query without fetching the row first.

So as an example:

```php

User::update([...]); // this can NOT be monitored.

```



### PUBLIC API:



```php



// Get all the history as a nice table

HistoryTracker::getHistoryOf(Model $model, array $columns, array $importantCols = [])



// It performs a query on the data changes table and gives you a raw version of changes.

HistoryTracker::getChanges(Model $model, array $cols)

// searches the history for a value in a column.

HistoryTracker::hasEverHad($modelId, string $colName, $value, string $tableName)


```



Note: all the queries are done within the transaction to guarantee that you do not end up with inconsistent data.


The most important method is the `getHistoryOf` which accepts an eloquent object, an array of columns to be fetched and an array of columns to be counted as a change.


#### $importantCols: What it means ?!



Consider a situation when you have a table with 10 columns and there are 2 forms to edit column values.

For example, a form to edit `first name`, `last name`, `bio` and etc, and another form to only change password.



Ok, now you need to show the submission history of the first form.



here you have to exclude the password column otherwise the submissions of other forms will appear in the history of the first form.



```php

HistoryTracker::getChanges($user, ['first_name', 'last_name'], ['first_name', 'last_name', 'bio']);

```

Here we don't want to show bio on the table but we want to show other metadata about that, for example, the date and the username.




--------------------



### :raising_hand: Contributing 

If you find an issue or have a better way to do something, feel free to open an issue or a pull request.

If you use laravel-widgetize in your open source project, create a pull request to provide its URL as a sample application in the README.md file. 



### :exclamation: Security

If you discover any security-related issues, please use the `security tab` instead of using the issue tracker.



### :star: Your Stars Make Us Do More :star:

As always if you found this package useful and you want to encourage us to maintain and work on it. Just press the star button to declare your willingness.



## More from the author:


### Laravel Microscope


:gem: It automatically find bugs in your laravel app


- https://github.com/imanghafoori1/laravel-microscope

-------------

### Laravel HeyMan

:gem: It allows to write expressive code to authorize, validate and authenticate.


- https://github.com/imanghafoori1/laravel-heyman

--------------


<p align="center">
 
    I speak to everyone in the same way, whether he is the garbage man or the president of the university. 

    "Albert Einstein"

</p>



