Install the module as usual

In any entity in the Manage fields tab:
When adding new fields a Views Reference field will now be available

The Views Reference Field works the same way as any Entity Reference field except that the entity it targets is a View
You can target a View using the Entity Reference field but you cannot nominate a particular View display
The Views Reference Field enables you to nominate a display ID and an argument

/*****  Composer *****/
Although Views Reference does not need composer, if you install using composer then use the following:

From the drupal root directory of your install:

composer config repositories.drupal composer https://packages.drupal.org/8
composer require drupal/viewsreference