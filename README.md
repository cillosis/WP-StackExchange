WP StackExchange
================

This plugin allows you to insert questions from a StackExchange site into your posts as a widget. 

### Installation

1) Checkout this repository.

2) Zip the code into a folder and install it through your WordPress Admin Interface under Plugins => Add New. Another option is you can get the code, place it in a folder, and upload it to your /wp-content/plugins/ directory.

3) Activate the plugin.

### Configuration

1) In the Admin area under **Settings => StackExchange Questions**, you will need to choose which StackExchange site you wish to use. It is also recommended to [register for an API key](http://stackapps.com/apps/oauth/register) which allows you to make considerably more requests. Once you register, you can set those in the admin interface as well.

2) Add the Widget where you would like the questions to be displayed by going to **Appearance => Widgets** in the Admin sidebar. The Widget is called *StackExchange Questions*. 

3) Configure the widget with a title to display as well as the number of questions you would like to show.

4) In your posts, add a new custom field named "stackexchange_search" and for the value set a keyword you would like to search for. For example, if your post is about Javascript, you can add the keyword "javascript". You can create multiple fields with the same name and it will search for all of them at once.

### Customization

The list of questions appearance can be customized by editing the **/css/theme.css** file in the plugins directory. 

### Other

I'm building this plugin for use in a personal site and wanted to share the love. I do not have the free time to provide support, although I will generally attempt to help when I can if you post a new Issue under this repository. Use of this plugin implies you are assuming full responsibility of your use of this code and I am in no way held responsible.

Enjoy ;)