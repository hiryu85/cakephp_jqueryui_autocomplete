# CakePHP Jquery-ui autocomplete
A easy way for use auto-complete on CakePHP


## Requirements

* jQuery
* jQuery-ui 

## Installation
* Go to your CakePHP app dir (app/)
* Clone this repository with:
    normal mode: `git clone git@github.com:hiryu85/cakephp_jqueryui_autocomplete.git app/plugins/auto_complete`
    submodule:  `git submodule add git@github.com:hiryu85/cakephp_jqueryui_autocomplete.git app/plugins/auto_complete`     
*  Include AutoComplete helper into your controller 
   `var $helpers = array('AutoComplete.AutoComplete')`  or `$this->helpers[] = 'AutoComplete.AutoComplete'
*  Use it with $this->AutoComplete('Model.field') into your view's (*Model.field sintax is required*)
*  All done :)


## AutoComplete methods:
* input($modelNameAndField, HtmlHelperOptions, JQueryUiAutoCompleteOptions)
  Render an input element with auto-complete.
  
  Example:
  `<?php 
      echo
        $this->Form->create('Event').
        $this->AutoComplete->input('Event.city').
        $this->Form->input('Event.date').
        $this->Form->end(__('Search event'))
    ?>` 

  You can found FormHelperOptions at http://api.cakephp.org/class/form-helper
  You can found JQueryUiAutoCompleteOptions at http://jqueryui.com/demos/autocomplete/

## AutoCompleteHelper configuration
    JQuery-ui auto-complete behavior:
    *Please, read about this configuration at jquery-ui homepage*
    * integer delay [default=300]
    * boolean disabled [default=false]
    * mixed   source   [default="/auto_complete/RemoteSources/get" this is an magic url]    
    * integer minLength  [default=4] 
    
    
### Change source of auto-complete 
source value can be:
*   a URL resource   [/mycontroller/myaction/] 
*   an array  (static datasource)          [array('Foo', 'Bar')] 
*   a javascript callback      [myCallback()] 

For change the source you can give to autocomplete helper the `source` key:
* `$this->AutoComplete->input('Model.field', null, array('source' => array('Foo', 'Bar') ))`   
* `$this->AutoComplete->input('Model.field', null, array('source' => '/mycontroller/myspecialaction/'))` 
* `$this->AutoComplete->input('Model.field', null, array('source' => 'myCallback()'))` 
